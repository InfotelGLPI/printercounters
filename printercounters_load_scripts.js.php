<?php
use Glpi\Event;
include('../../inc/includes.php');
header('Content-Type: text/javascript');

?>

var root_printercounters_doc = "<?php echo PLUGIN_PRINTERCOUNTERS_WEBDIR; ?>";
(function ($) {
   $.fn.printercounters_load_scripts = function () {

      init();

      // Start the plugin
      function init() {
         //            $(document).ready(function () {
         // Send data
         $.ajax({
            url: root_printercounters_doc + '/ajax/loadscripts.php',
            type: "POST",
            dataType: "html",
            data: 'action=load',
            success: function (response, opts) {
               var scripts, scriptsFinder = /<script[^>]*>([\s\S]+?)<\/script>/gi;
               while (scripts = scriptsFinder.exec(response)) {
                  eval(scripts[1]);
               }
            }
         });
         //            });
      }

      return this;
   };
}(jQuery));

$(document).printercounters_load_scripts();
