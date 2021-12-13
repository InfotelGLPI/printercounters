/*
 -------------------------------------------------------------------------
 Printercounters plugin for GLPI
 Copyright (C) 2014 by the Printercounters Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Printercounters.

 Printercounters is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Printercounters is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Printercounters. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------  */

/**
 *  Printercounter search
 */
(function ($) {
    $.fn.printercountersSearch = function (options) {

        var object = this;
        init();

        // Start the plugin
      function init() {
          object.params = new Array();
          object.params['start'] = 0;
          object.params['limit'] = 0;
          object.params['order'] = '';
          object.params['sort'] = 0;

         if (options != undefined) {
             $.each(options, function (index, val) {
               if (val != undefined && val != null) {
                   object.params[index] = val;
               }
             });
         }
      }

        /**
         * Init search
         *
         * @param string root_doc
         * @param string formName
         * @param string toupdate
         * @param params : - int start
         *                 - int limit
         *                 - string order
         *                 - int sort
         */
        this.initSearch = function (root_doc, formName, toupdate, params) {
            var formInput = getFormData(formName);

            var item_bloc = $('#' + toupdate);

         if (params != undefined) {
             $.each(params, function (index, val) {
               if (val != undefined && val != null) {
                   object.params[index] = val;
               }
             });
         }

         if (object.params['limit'] == '__VALUE__') {
             object.params['limit'] = $("select[name='glpilist_limit']").val();
         }

            // Loading
            item_bloc.html('<div style="width:100%;text-align:center"><img src="' + root_doc + '/pics/large-loading.gif"></div>');

            // Send data
            $.ajax({
               url: root_doc + '/ajax/search.php',
               type: "POST",
               dataType: "html",
               data: 'action=initSearch&' + formInput +
                        '&start=' + object.params['start'] +
                        '&limit=' + object.params['limit'] +
                        '&order=' + object.params['order'] +
                        '&sort=' + object.params['sort'],
               success: function (response, opts) {
                   item_bloc.html(response);

                   var scripts, scriptsFinder = /<script[^>]*>([\s\S]+?)<\/script>/gi;
                  while (scripts = scriptsFinder.exec(response)) {
                      eval(scripts[1]);
                  }
               }
            });
        };

         /**
         *  Add search field
         *
         * @param string root_doc
         * @param string toupdate
         * @param string fieldcounter
         * @param string formName
         */
         this.addSearchField = function (root_doc, toupdate, fieldcounter, formName) {
            var search_count = $('#' + fieldcounter).val();

            $('#' + fieldcounter).val((parseInt(search_count) + 1));

            var formInput = getFormData(formName);

            $.ajax({
               url: root_doc + '/ajax/search.php',
               type: "POST",
               dataType: "html",
               data: 'action=addSearchField&' + formInput + '&search_count=' + $('#' + fieldcounter).val(),
               success: function (response, opts) {
                   var item_bloc = $('#' + toupdate + search_count);

                   $(response).insertAfter(item_bloc);

                   var scripts, scriptsFinder = /<script[^>]*>([\s\S]+?)<\/script>/gi;
                  while (scripts = scriptsFinder.exec(response)) {
                      eval(scripts[1]);
                  }
               }
             });
         };

         /**
         *  Delete search field
         */
         this.deleteSearchField = function (toupdate, fieldcounter) {
            var search_count = $('#' + fieldcounter).val();
            if (search_count > 0) {
                $('#' + toupdate + search_count).remove();
                $('#' + fieldcounter).val(parseInt(search_count) - 1);
            }
         };

         /**
         *  Reset search field
         *
         * @param string root_doc
         * @param string toupdate
         * @param string formName
         * @param string historyFormName
         */
         this.resetSearchField = function (root_doc, toupdate, formName, historyFormName) {
            var formInput = getFormData(formName);

            $.ajax({
               url: root_doc + '/ajax/search.php',
               type: "POST",
               dataType: "html",
               data: 'action=resetSearchField&' + formInput,
               success: function (response, opts) {
                   var item_bloc = $('#' + toupdate);
                   item_bloc.html(response);

                   var scripts, scriptsFinder = /<script[^>]*>([\s\S]+?)<\/script>/gi;
                  while (scripts = scriptsFinder.exec(response)) {
                      eval(scripts[1]);
                  }

                     object.initSearch(root_doc, formName, historyFormName);
               }
             });
         };

         return this;
    }
}(jQuery));


/**
 *  Printercounter actions for records
 */
(function ($) {
    $.fn.printercountersAction = function (options) {

        var object = this;
        init();

        // Start the plugin
      function init() {
      }

        /**
         * init additional data
         *
         * @param string root_doc
         * @param string toupdate
         * @param string itemtype
         * @param int items_id
         */
        this.initAdditionalData = function (root_doc, toupdate, itemtype, items_id) {
            var item_bloc = $('#' + toupdate);

            // Loading
            item_bloc.html('<div style="width:100%;text-align:center"><img src="' + root_doc + '/pics/large-loading.gif"></div>');

            $.ajax({
               url: root_doc + '/ajax/additional_data.php',
               type: "POST",
               dataType: "html",
               data: 'action=showAdditionalData&itemtype=' + itemtype + '&items_id=' + items_id,
               success: function (response, opts) {
                   item_bloc.html(response);

                   var scripts, scriptsFinder = /<script[^>]*>([\s\S]+?)<\/script>/gi;
                  while (scripts = scriptsFinder.exec(response)) {
                      eval(scripts[1]);
                  }
               }
            });
        };

         /**
         * Init error item
         *
         * @param string root_doc
         * @param string toupdate
         * @param string itemtype
         * @param int items_id
         */
         this.initErrorItem = function (root_doc, toupdate, itemtype, items_id) {

            var item_bloc = $('#' + toupdate);

            // Loading
            item_bloc.html('<div style="width:100%;text-align:center"><img src="' + root_doc + '/pics/large-loading.gif"></div>');

            $.ajax({
               url: root_doc + '/ajax/record.php',
               type: "POST",
               dataType: "html",
               data: 'action=showErrorItem&itemtype=' + itemtype + '&items_id=' + items_id,
               success: function (response, opts) {
                   item_bloc.html(response);

                   var scripts, scriptsFinder = /<script[^>]*>([\s\S]+?)<\/script>/gi;
                  while (scripts = scriptsFinder.exec(response)) {
                      eval(scripts[1]);
                  }
               }
             });
         };

         /**
         *  Init printercounters actions
         *
         * @param string root_doc
         * @param string action
         * @param string toobserve
         * @param string toupdate
         * @param json params :
         *                       - int items_id
         *                       - string itemtype
         *                       - int records_id
         *                       - bool addLowerRecord
         *                       - string rand
         *                       - json updates
         */
         this.printercountersActions = function (root_doc, action, toobserve, toupdate, params) {
            if (params.records_id == undefined) {
                params.records_id = 0;
            }

            if (toupdate != '') {
                var item_bloc = $('#' + toupdate);
                // Loading
                item_bloc.html('<div style="width:100%;text-align:center"><img src="' + root_doc + '/pics/large-loading.gif"></div>');
            }

            // If manual record is set : get form input
            var formInput = '';
            if (action == 'setManualRecord' && toobserve != null) {
                formInput = getFormData(toobserve);
            }

            $.ajax({
               url: root_doc + '/ajax/record.php',
               type: "POST",
               dataType: "html",
               data: 'action=' + action +
                        '&rand=' + params.rand +
                        '&items_id=' + params.items_id +
                        '&itemtype=' + params.itemtype +
                        '&records_id=' + params.records_id +
                        '&addLowerRecord=' + params.addLowerRecord +
                        '&' + formInput,
               success: function (response, opts) {
                   var result = {};

                   // Test if response is in JSON format
                  try {
                      result = jQuery.parseJSON(response);
                  } catch (err) {
                     result.message = response;
                     result.error = false;
                  }

                  switch (action) {
                     case 'immediateRecord':
                         printercountersSearch.initSearch(root_doc, params.formName, params.updates.record);
                         object.initAdditionalData(root_doc, params.updates.additionalData, params.itemtype, params.items_id);
                         object.initErrorItem(root_doc, params.updates.errorItem, params.itemtype, params.items_id);
                         break;

                     case 'SNMPSet':
                         object.initAdditionalData(root_doc, params.updates.additionalData, params.itemtype, params.items_id);
                         break;

                     case 'showManualRecord':
                         // var xy = printecountersGetScrollXY();
                         // manual_record_window.y = xy[1] + 50;
                         // manual_record_window.html(response).dialog("open");
                        glpi_html_dialog({
                           title: __('Add a manual record', 'printercounters'),
                           body: response,
                           id: 'showManualRecord',
                           buttons: [{
                              label: 'OK',
                              click: function(event) {
                                 window.location.reload();
                              }
                           }],
                        })
                         break;

                     case 'setManualRecord':
                        if (!result.error) {
                            printercountersSearch.initSearch(root_doc, params.formName, params.updates.record);
                            // manual_record_window.dialog("close");
                        }
                         break;

                     case 'updatePrinterData':
                        if (!result.error) {
                            $("input[name='last_pages_counter']", true).val(result.message);
                            result.message = '';
                        }
                         break;

                     case 'killProcess':
                         object.printercountersActions(root_doc + '/ajax/process.php', root_doc, 'getProcesses', '', 'process_display', '', '');
                         break;

                     case 'updateGlobalTco':
                        if ($('#' + params.updates.globalTco) != undefined) {
                            $('#' + params.updates.globalTco).html(result.result);
                        }
                         break;
                  }

                  if (toupdate != '') {
                     if (result.error) {
                         result.message = '<b class="red">' + result.message + '</b>';
                     }

                     item_bloc.html(result.message);
                  }
               }
             });
         };

         /**
         *  Get scroll X Y position for modal window
         */
         function printecountersGetScrollXY() {
            var x = 0, y = 0;
            if (typeof (window.pageYOffset) == 'number') {
                // Netscape
                x = window.pageXOffset;
                y = window.pageYOffset;
            } else if (document.body && (document.body.scrollLeft || document.body.scrollTop)) {
               // DOM
               x = document.body.scrollLeft;
               y = document.body.scrollTop;
            } else if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
                // IE6 standards compliant mode
                x = document.documentElement.scrollLeft;
                y = document.documentElement.scrollTop;
            }
             return [x, y];
         }

         return this;
    }
}(jQuery));


/**
 *  Get the form values and construct data url
 *
 * @param object form
 */
function getFormData(form) {

   if (typeof (form) !== 'object') {
       var form = $('#' + form);
   }

    return encodeParameters(form[0]);
}

/**
 * Encode form parameters for URL
 *
 * @param array elements
 */
function encodeParameters(elements) {
    var kvpairs = [];

    $.each(elements, function (index, e) {
      if (e.name != '') {
         switch (e.type) {
            case 'radio':
            case 'checkbox':
               if (e.checked) {
                   kvpairs.push(encodeURIComponent(e.name) + "=" + encodeURIComponent(e.value));
               }
                  break;
            case 'select-multiple':
               var name = e.name.replace("[", "").replace("]", "");
               $.each(e.selectedOptions, function (index, option) {
                   kvpairs.push(encodeURIComponent(name + '[' + option.index + ']') + '=' + encodeURIComponent(option.value));
               });
                  break;
            default:
               kvpairs.push(encodeURIComponent(e.name) + "=" + encodeURIComponent(e.value));
                  break;
         }
      }
    });

    return kvpairs.join("&");
}

/**
 *  Add elements in item forms
 */
function printercounters_addelements(params) {

    var root_doc = params.root_doc;
    var itemtype = params.itemtype;
    var itemToShow = params.itemToShow;
    var glpi_tab = params.glpi_tab;

    $(document).ready(function () {
        $.urlParam = function (name) {
            var results = new RegExp('[\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.href);
         if (results != null) {
             return results[1] || 0;
         }
            return undefined;
        };
         // get item id
         var items_id = parseInt($.urlParam('id'));
         //only in edit form
         if (items_id == undefined) {
            return;
         }

         // Launched on each complete Ajax load
         $(document).ajaxComplete(function (event, xhr, option) {
            setTimeout(function () {
                // We execute the code only if the ticket form display request is done
               if (option.url != undefined) {
                   var ajaxTab_param;
                   var paramFinder = /[?&]?_glpi_tab=([^&]+)(&|$)/;

                   // We find the name of the current tab
                   ajaxTab_param = paramFinder.exec(option.url);

                   // Get the right tab
                  if (ajaxTab_param != undefined
                           && (ajaxTab_param[1] == glpi_tab)) {

                      $.ajax({
                           url: root_doc + '/ajax/infocom.php',
                           type: "POST",
                           dataType: "json",
                           data: {
                              'items_id': items_id,
                              'itemtype': itemtype,
                              'action': 'getTco'
                           },
                           success: function (response, opts) {
                               // Get element where insert html
                               var item_bloc = $("form[name='form_ic'] table tr:nth-child(13)");
                               $("<tr class='tab_bg_1'><td>" + params.lang.global_tco + "</td><td><span id='update_global_tco'>" + response.global_tco + "</span></td><td colspan='2'></td></tr>").insertAfter(item_bloc);
                           }
                        });
                  }
               }

            }, 100);
         }, this);
    });
}

/**
 *  Set confirmation window on input click
 */
function printercounters_setConfirmation(message, oldValue, newValue, formID, buttonName) {
   if (formID == undefined || formID == null) {
       var item_bloc = $("form div table input[name=update]");
   } else {
       var item_bloc = $("form[id=" + formID + "] div table input[name=" + buttonName + "]");
   }

   if (oldValue != newValue) {
       item_bloc.set({'onclick': "if (window.confirm('" + message + "')){return true;} else {return false;}"});
   } else {
       item_bloc.set({'onclick': ""});
   }
}

/**
 *  Reload csrf input
 */
function printecounters_reloadCsrf(root_doc, formName) {

    $.ajax({
         url: root_doc + '/ajax/search.php',
         type: "POST",
         dataType: "html",
         data: {
            'action': 'reloadCsrf'
         },
         success: function (response, opts) {
            $("form[name=" + formName + "] input[name=_glpi_csrf_token]").val(response);
         }
      });
}

/**
 *  Ajax massive action
 */
function printecounters_ajaxMassiveAction(root_doc, action, phpTimeout) {

    $.ajax({
         url: root_doc + '/ajax/record.php',
         type: "POST",
         dataType: "html",
         data: {
            'action': action
         },
         success: function (response, opts) {
            var scripts, scriptsFinder = /<script[^>]*>([\s\S]+?)<\/script>/gi;
            while (scripts = scriptsFinder.exec(response)) {
                eval(scripts[1]);
            }
         },
         error: function (xhr, ajaxOptions, thrownError) {
            printecounters_ajaxMassiveAction(root_doc, 'ajaxMassiveActionTimeOut', phpTimeout);
         },
         timeout: phpTimeout * 1000
      });

}
