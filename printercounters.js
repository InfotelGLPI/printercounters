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
*  Get the form values and construct data url
* 
* @param object form
*/
function getFormInput(form){

   if (typeof(form) !== 'object'){
      var form = document.getElementById(form);
   } 
   
   return encodeParameters(form.elements);
}

/** 
* Encode form parameters for URL
* 
* @param array elements
*/
function encodeParameters(elements){
   var kvpairs = [];

   for (var i = 0; i < elements.length; i++) {
      var e = elements[i];
      switch(e.type){
         case 'radio': case 'checkbox':
            if(e.checked){
               kvpairs.push(encodeURIComponent(e.name) + "=" + encodeURIComponent(e.value));
            }
            break;
         case 'select-multiple':
            var name = e.name.replace("[", "").replace("]", "");
            Ext.each(e.selectedOptions, function(option) {
               kvpairs.push(encodeURIComponent(name+'['+option.index+']')+'='+encodeURIComponent(option.value));
            });
            break;
         default:
            kvpairs.push(encodeURIComponent(e.name) + "=" + encodeURIComponent(e.value));
            break;
      }
   }
   
   return kvpairs.join("&");
}

/** 
* Init search
* 
* @param string root_doc
* @param string formName
* @param string toupdate
* @param int start
* @param int limit
*/
function initSearch(root_doc, formName, toupdate, start, limit, order, sort){

   var queryString = getFormInput(formName);

   var item_bloc = Ext.get(toupdate);
   
   // Loading
   item_bloc.update('<div style="width:100%;text-align:center"><img src="'+root_doc+'/lib/extjs/resources/images/default/shared/large-loading.gif"></div>');

   // Send data
   Ext.Ajax.request({
      url: root_doc+'/plugins/printercounters/ajax/search.php',
      params: 'action=initSearch&'+queryString+'&start='+start+'&limit='+limit+'&order='+order+'&sort='+sort,
      success: function(response, opts) {
         var result = response.responseText;
         
         item_bloc.update(result);

         var scripts, scriptsFinder=/<script[^>]*>([\s\S]+?)<\/script>/gi;
         while(scripts=scriptsFinder.exec(result)) {
            eval(scripts[1]);
         }
      }
   });
}

/** 
* Init search
* 
* @param string root_doc
* @param string formName
* @param string toupdate
* @param int start
* @param int limit
*/
function initAdditionalData(root_doc, toupdate, itemtype, items_id){

   var item_bloc = Ext.get(toupdate);
   
   // Loading
   item_bloc.update('<div style="width:100%;text-align:center"><img src="'+root_doc+'/lib/extjs/resources/images/default/shared/large-loading.gif"></div>');

   // Send data
   Ext.Ajax.request({
      url: root_doc+'/plugins/printercounters/ajax/additional_data.php',
      params: 'action=showAdditionalData&itemtype='+itemtype+'&items_id='+items_id,
      success: function(response, opts) {
         var result = response.responseText;
         
         item_bloc.update(result);

         var scripts, scriptsFinder=/<script[^>]*>([\s\S]+?)<\/script>/gi;
         while(scripts=scriptsFinder.exec(result)) {
            eval(scripts[1]);
         }
      }
   });
}


/** 
*  Add search field
* 
* @param string root_doc
* @param string toupdate
* @param string fieldcounter
* @param string formName
*/
function addSearchField(root_doc, toupdate, fieldcounter, formName){
   var search_count = Ext.get(fieldcounter).getValue();
   Ext.get(fieldcounter).dom.value = (parseInt(search_count)+1);

   var queryString = getFormInput(formName);

   Ext.Ajax.request({
      url: root_doc+'/plugins/printercounters/ajax/search.php',
      params: 'action=addSearchField&'+queryString+'&search_count='+Ext.get(fieldcounter).dom.value,
      success: function(response, opts) {
         var result    = response.responseText;
         var item_bloc = Ext.get(toupdate+search_count);
         item_bloc.insertHtml('afterEnd',  result);

         var scripts, scriptsFinder=/<script[^>]*>([\s\S]+?)<\/script>/gi;
         while(scripts=scriptsFinder.exec(result)) {
            eval(scripts[1]);
         }
      }
   });
}

/** 
*  Delete search field
*/
function deleteSearchField(toupdate, fieldcounter){
   var search_count = Ext.get(fieldcounter).getValue();
   if(search_count > 0){
      Ext.get(toupdate+search_count).remove();
      Ext.get(fieldcounter).dom.value = (parseInt(search_count)-1);
   }
}

/** 
*  Reset search field
*  
* @param string root_doc
* @param string formName
*/
function resetSearchField(root_doc, toupdate, formName, historyFormName, start, limit, order, sort){

   var queryString = getFormInput(formName);
   
   Ext.Ajax.request({
      url: root_doc+'/plugins/printercounters/ajax/search.php',
      params: 'action=resetSearchField&'+queryString,
      success: function(response, opts) {
         var result    = response.responseText;
         var item_bloc = Ext.get(toupdate);
         item_bloc.update(result);

         var scripts, scriptsFinder=/<script[^>]*>([\s\S]+?)<\/script>/gi;
         while(scripts=scriptsFinder.exec(result)) {
            eval(scripts[1]);
         }
         
         initSearch(root_doc, formName, historyFormName, start, limit, order, sort);
      }
   });
}

/** 
*  Init printercounters actions
*  
* @param string url
* @param string root_doc
* @param string action
* @param string toobserve
* @param string toupdate
* 
* @param int items_id
* @param string itemtype
* @param int records_id
*/
function printercountersActions(root_doc, action, toobserve, toupdate, params){
   
   if(params.records_id == undefined){
      params.records_id = 0;
   }

   if(toupdate != ''){
      var item_bloc = Ext.get(toupdate);
      // Loading
      item_bloc.update('<div style="width:100%;text-align:center"><img src="'+root_doc+'/lib/extjs/resources/images/default/shared/large-loading.gif"></div>');
   }

   // If manual record is set : get form input
   var queryString = '';
   if((action == 'setManualRecord') && toobserve != ''){
      queryString = getFormInput(toobserve);
   }

   // Send data
   Ext.Ajax.request({
      url: root_doc+'/plugins/printercounters/ajax/record.php',
      params: 'action='+action+
              '&rand='+params.rand+
              '&items_id='+params.items_id+
              '&itemtype='+params.itemtype+
              '&records_id='+params.records_id+
              '&addLowerRecord='+params.addLowerRecord+
              '&'+queryString,
           
      success: function(response, opts) {
         var result = {};
         
         // Test if response is in JSON format
         try {
            result = Ext.util.JSON.decode(response.responseText);
         }
         catch (err) {
            result.message = response.responseText;
            result.error   = false;
         }

         switch(action){
            case 'immediateRecord':
               initSearch(root_doc, params.formName, params.updates.record);
               initAdditionalData(root_doc, params.updates.additionalData, params.itemtype, params.items_id);
               break;
               
            case 'SNMPSet':
               initAdditionalData(root_doc, params.updates.additionalData, params.itemtype, params.items_id);
               break;
               
            case 'showManualRecord':
               var xy = printecountersGetScrollXY();
               manual_record_window.y = xy[1]+50;
               manual_record_window.show();
               manual_record_window.load({
                  url: root_doc+'/plugins/printercounters/ajax/record.php',
                  scripts: true,
                  params:{'action':'showManualRecord', 'items_id':params.items_id, 'itemtype':params.itemtype, 'records_id':params.records_id, 'rand':params.rand, 'addLowerRecord':params.addLowerRecord}
               });
               break;
                             
            case 'setManualRecord':
               if(!result.error){
                  initSearch(root_doc, params.formName, params.updates.record);
                  manual_record_window.hide();
               }
               break;
            
            case 'updateCounterPosition':
               if(!result.error){
                  Ext.select("input[name='last_pages_counter']", true).elements[0].dom.value = result.message;
                  result.message = '';
               }
               break;
               
            case 'killProcess':
               printercountersActions(root_doc+'/plugins/printercounters/ajax/process.php', root_doc, 'getProcesses', '', 'process_display', '', '');
               break;
               
            case 'updateGlobalTco':
               if(Ext.get(params.updates.globalTco) != undefined){
                  Ext.get(params.updates.globalTco).update(result.result);
               }
               break;
         }
         
         if(toupdate != ''){
            if (result.error){
               result.message = '<b class="red">'+result.message+'</b>';
            }
            
            item_bloc.update(result.message);
         }
         
      }
   });
}

/** 
*  Get scroll X Y position for modal window
*/
function printecountersGetScrollXY() {
   var x = 0, y = 0;
   if( typeof( window.pageYOffset ) == 'number' ) {
      // Netscape
      x = window.pageXOffset;
      y = window.pageYOffset;
   } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
      // DOM
      x = document.body.scrollLeft;
      y = document.body.scrollTop;
   } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
      // IE6 standards compliant mode
      x = document.documentElement.scrollLeft;
      y = document.documentElement.scrollTop;
   }
   return [x, y];
}

/** 
*  Add elements in item forms
*/
function printercounters_addelements(params){

   var root_doc   = params.root_doc;
   var itemtype   = params.itemtype;
   var itemToShow = params.itemToShow;
   var glpi_tab   = params.glpi_tab;

   Ext.onReady(function() {
      // separating the GET parameters from the current URL
      var getParams = document.URL.split("?");
      // transforming the GET parameters into a dictionnary
      var url_params = Ext.urlDecode(getParams[getParams.length - 1]);
      // get items_id
      var items_id = url_params['id'];
      //only in edit form
      if(items_id == undefined) items_id = 0;
      //remove #
      items_id = parseInt(items_id);
      
      var ajaxTab_param;
      var paramFinder = /[?&]?glpi_tab=([^&]+)(&|$)/;
      var loaded      = 0;
      var checkParam  = null;
 
      // Launched on each complete Ajax load 
      Ext.Ajax.on('requestcomplete', function(conn, response, option) {
         checkParam = option.url;
         
         if(option.url.indexOf("updatecurrenttab.php") == -1){
            checkParam = option.params;
         }
         // We execute the code only if the item form display request is done 
         if(checkParam != null){
            // We find the name of the current tab
            ajaxTab_param = paramFinder.exec(checkParam);

            // Delay the execution (ajax requestcomplete event fired before dom loading)
            setTimeout( function () {
               // If the item tab name is found AND the request couter at 1 AND my ajax is not yet launched AND item Ajax is loaded AND the toolip html elements are found
               if(ajaxTab_param != undefined  && ajaxTab_param != null
                  && ajaxTab_param[1].indexOf(itemToShow) != -1 && loaded == 0){

                  loaded++;
                  
                  Ext.Ajax.request({
                     url: root_doc+'/plugins/printercounters/ajax/infocom.php',
                     params: {
                        'items_id' : items_id, 
                        'itemtype' : itemtype, 
                        'action'   : 'getTco'
                     },
                     success: function(response, opts) {
                        // Get element where insert html
                        var item_bloc = Ext.select("div[id="+glpi_tab+"] form div table tr:nth-child(13)", true).elements[0];
                        var result = Ext.util.JSON.decode(response.responseText);

                        item_bloc.insertHtml('afterEnd', "<tr class='tab_bg_1'><td>"+params.lang.global_tco+"</td><td><span id='update_global_tco'>"+result.global_tco+"</span></td><td colspan='2'></td></tr>");
                     }
                  });
                  
               }
            }, 100); 
         }
      }, this);
   });
}

/** 
*  Set confirmation window on input click
*/
function printercounters_setConfirmation(message, oldValue, newValue, formID, buttonName){
   if (formID == undefined || formID == null) {
      var item_bloc = Ext.select("form div table input[name=update]", true).elements[0];
   } else {
      var item_bloc = Ext.select("form[id="+formID+"] div table input[name="+buttonName+"]", true).elements[0];
   }

   if (oldValue != newValue) {
      item_bloc.set({'onclick' : "if (window.confirm('"+message+"')){return true;} else {return false;}"});
   } else {
      item_bloc.set({'onclick' : ""});
   }
}

/** 
*  Reload csrf input
*/
function printecounters_reloadCsrf(root_doc, formName){
   Ext.Ajax.request({
      url: root_doc+'/plugins/printercounters/ajax/search.php',
      params: {
         'action'   : 'reloadCsrf'
      },
      success: function(response, opts) {
         Ext.select("form[name="+formName+"] input[name=_glpi_csrf_token]", true).elements[0].dom.value = response.responseText;
      }
   });
}

/** 
*  Ajax massive action
*/
function printecounters_ajaxMassiveAction(root_doc, action, phpTimeout){
   Ext.Ajax.request({
      url: root_doc+'/plugins/printercounters/ajax/record.php',
      params: {
         'action'   : action
      },
      success: function(response, opts) {
         var scripts, scriptsFinder=/<script[^>]*>([\s\S]+?)<\/script>/gi;
         while(scripts=scriptsFinder.exec(response.responseText)) {
            eval(scripts[1]);
         }
      },
      failure: function(response, opts) {
         printecounters_ajaxMassiveAction(root_doc, 'ajaxMassiveActionTimeOut', phpTimeout);
      }
   });
   
   Ext.Ajax.timeout = phpTimeout*1000;
}