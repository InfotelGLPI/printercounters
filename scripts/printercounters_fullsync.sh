#!/bin/sh
cd $(dirname $0)

eval $(php -f ./glpienv.php)

PROCESS_ID=$(date +%j%H%M%S)

pid_dir="/tmp"
pidfile="$pid_dir/printercounters_fullsync.pid"
runningpid=""
scriptname="printercounters_fullsync.sh"
logfilename="printercounters_fullsync.log"


# Predefined settings
sonprocess_nbr=2
itemtype=Printer

trap cleanup 1 2 3 6

usage()
{
   echo "Usage:"
   echo "  $0 [--arg]"
   echo
   echo "Arguments:"
   echo "  --sonprocess_nbr=num: number of sonprocesss to launch"
   echo "  --itemtype=string: Type of item"
}

exit_if_soft_lock()
{
   if [ -f $GLPI_LOCK_DIR/printercounters.lock ]
   then
      echo "Software lock : script can't run !"
      exit 1
   fi
}

read_argv()
{
   for i in $@; do
      valname=`echo $i| sed 's/--\(.*\)=.*/\1/'`
      valcontent=`echo $i| sed 's/--.*=\(.*\)/\1/'`

      [ -z $valname ] && usage
      case "$valname" in
         sonprocess_nbr)
         sonprocess_nbr=$valcontent
         ;;
         itemtype)
         itemtype=$valcontent
         ;;
         --nolog)
         logfilename=
         ;;
         *)
         usage
         exit 1
         ;;
      esac
   done
}

cleanup()
{
   echo "cleaning up."
   #  echo "kill pids: $runningpid"
   for pid in $runningpid; do 
      kill $pid 2>/dev/null; 
   done
   rm -f $pidfile
   rm -f "$GLPI_LOCK_DIR/lock_entity*"

   echo $(date) ended
   exit 0
}

exit_if_already_running()
{
   # No pidfile, probably no daemon present
   #
   if [ ! -f $pidfile ]
   then
      return 1
   fi

   pid=`cat $pidfile`

   # No pid, probably no daemon present
   #
   if [ -z "$pid" ]
   then
      return 1
   fi

   if [ ! -d /proc/$pid ]
   then
      return 1
   fi

   cmd=`cat /proc/$pid/cmdline | grep $scriptname`

   if [ "$cmd" != "" ]
   then
      exit 1
   fi
}

if [ ! -w $GLPI_LOCK_DIR ]
then
   echo -e "\tERROR : $GLPI_LOCK_DIR not writable"
   echo -e "\trun script as 'apache' user"
   exit 1
fi

read_argv "$@"
if [ -n "$logfilename" ]; then 
   exec >>$GLPI_LOG_DIR/$logfilename 2>&1
fi

exit_if_soft_lock
exit_if_already_running

echo $$ > $pidfile 

rm -f "$GLPI_LOCK_DIR/lock_entity*"
cpt=0

echo $(date) $0 started
  
while [ $cpt -lt $sonprocess_nbr ]; do 
   cpt=$(($cpt+1))
   cmd="php printercounters_fullsync.php --itemtype=$itemtype --sonprocess_nbr=$sonprocess_nbr --sonprocess_id=$cpt --process_id=$PROCESS_ID"
   sh -c "$cmd"&
   runningpid="$runningpid $!"
   sleep 1
done

wait

cleanup
