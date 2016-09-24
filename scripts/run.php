<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 printercounters plugin for GLPI
 Copyright (C) 2014-2016 by the printercounters Development Team.

 https://github.com/InfotelGLPI/printercounters
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of printercounters.

 printercounters is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 printercounters is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with printercounters. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function usage() {

   echo "Usage:\n";
   echo "\t" . $_SERVER["argv"][0]. " [--args]\n";
   echo "\n\tArguments:\n";
   echo "\t\t--sonprocess_nbr=num: number of son processes to launch\n";
   echo "\t\t--itemtype=string: type of item to search\n";
   echo "\t\t--record_type=string: type of record ('error' or 'normal')\n";
   echo "\t\t--nolog: use standard output rather than log file\n";
}


function readargs () {
   global $itemtype, $sonprocess_nbr, $record_type,  $log;

   for ($i=1 ; $i<$_SERVER["argc"] ; $i++) {
      $it = explode("=",$_SERVER["argv"][$i]);
      switch ($it[0]) {
         case '--itemtype' :
            $itemtype=$it[1];
            break;

         case '--sonprocess_nbr' :
            $sonprocess_nbr=$it[1];
            break;

         case '--record_type' :
            $record_type=$it[1];
            break;

         case '--nolog' :
            fclose($log);
            $log=STDOUT;
            break;

         default :
            usage();
            exit(1);
      }
   }
}

function exit_if_soft_lock() {

   if (file_exists(GLPI_LOCK_DIR."/printercounters.lock")) {
      echo "Software lock : script can't run !\n";
      exit (1);
   }

}


function exit_if_already_running($pidfile) {

   # No pidfile, probably no daemon present
   if (!file_exists($pidfile)) {
      return 1;
   }
   $pid=intval(file_get_contents($pidfile));

   # No pid, probably no daemon present
   if (!$pid || @pcntl_getpriority($pid)===false) {
      return 1;
   }
   exit (1);
}


function cleanup ($pidfile) {

   @unlink($pidfile);

   $dir=opendir(GLPI_LOCK_DIR);
   if ($dir) while ($name=readdir($dir)) {
      if (strpos($name, "lock_entity")===0) {
         unlink(GLPI_LOCK_DIR."/".$name);
      }
   }
}


if (!isset($_SERVER["argv"][0])) {
   header("HTTP/1.0 403 Forbidden");
   die("403 Forbidden");
}
ini_set("memory_limit","-1");
ini_set("max_execution_time", "0");

chdir(dirname($_SERVER["argv"][0]));
define ("GLPI_ROOT", realpath(dirname($_SERVER["argv"][0])."/../../.."));
require GLPI_ROOT."/config/based_config.php";

$processid      = date("zHi");
$itemtype       = "Printer";
$record_type    = 'normal';
$sonprocess_nbr = 2;

if (function_exists("sys_get_temp_dir")) {
   # PHP > 5.2.x
   $pidfile = sys_get_temp_dir()."/printercounters_fullsync.pid";
} else if (DIRECTORY_SEPARATOR=='/') {
   # Unix/Linux
   $pidfile = "/tmp/printercounters_fullsync.pid";
} else {
   # Windows
   $pidfile = GLPI_LOG_DIR . "/printercounters_fullsync.pid";
}
$logfilename = GLPI_LOG_DIR."/printercounters_fullsync.log";

if (!is_writable(GLPI_LOCK_DIR)) {
   echo "\tERROR : " .GLPI_LOCK_DIR. " not writable\n";
   echo "\trun script as 'apache' user\n";
   exit (1);
}
$log=fopen($logfilename, "at");
readargs();

exit_if_soft_lock();
exit_if_already_running($pidfile);
cleanup($pidfile);

//Only available with PHP5 or later
file_put_contents($pidfile, getmypid());
fwrite($log, date("r") . " " . $_SERVER["argv"][0] . " started\n");

if (function_exists("pcntl_fork")) {
   # Unix/Linux
   $pids=array();
   for ($i=0 ; $i<$sonprocess_nbr ; ) {
      $i++;
      $pid=pcntl_fork();
      if ($pid == -1) {
         fwrite ($log, "Could not fork\n");

      } else if ($pid) {
         fwrite ($log, "$pid Started\n");
         file_put_contents($pidfile, ";".$i.'$$$'.$pid, FILE_APPEND);
         $pids[$pid]=1;

      } else  {
         $cmd="php -q -d -f printercounters_fullsync.php --record_type=$record_type --sonprocess_nbr=$sonprocess_nbr ".
              " --sonprocess_id=$i --itemtype=$itemtype --process_id=$processid";

         $out=array();
         exec($cmd, $out, $ret);
         foreach ($out as $line) {
            fwrite ($log, $line."\n");
         }
         exit($ret);
      }
   }
   $status=0;
   while (count($pids)) {
      $pid=pcntl_wait($status);
      if ($pid<0) {
         fwrite ($log, "Cound not wait\n");
         exit (1);
      } else {
         unset($pids[$pid]);
         fwrite ($log, "$pid ended, waiting for " . count($pids) . " running son process\n");
      }
   }
} else {
   # Windows - No fork, so Only one process :(
   $cmd="php -q -d -f printercounters_fullsync.php --record_type=$record_type --sonprocess_nbr=1"
         . " --sonprocess_id=1 --itemtype=$itemtype --process_id=$processid";

   $out=array();
   exec($cmd, $out, $ret);
   foreach ($out as $line) {
      fwrite ($log, $line."\n");
   }
}

cleanup($pidfile);
fwrite ($log, date("r") . " " . $_SERVER["argv"][0] . " ended\n\n");

?>
