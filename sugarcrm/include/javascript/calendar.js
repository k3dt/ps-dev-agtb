/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

Calendar = function() {};

Calendar.setup = function (params) {

    YAHOO.util.Event.onDOMReady(function(){
    	
        var Event = YAHOO.util.Event;
        var Dom = YAHOO.util.Dom;
        var dialog;
        var calendar;
        var showButton = params.button ? params.button : params.buttonObj;
        var userDateFormat = params.ifFormat ? params.ifFormat : (params.daFormat ? params.daFormat : "m/d/Y");
        var inputField = params.inputField ? params.inputField : params.inputFieldObj;
        var dateFormat = userDateFormat.substr(0,10);
        var date_field_delimiter = /([-.\\/])/.exec(dateFormat)[0];
        dateFormat = dateFormat.replace(/[^a-zA-Z]/g,'');
        
        var monthPos = dateFormat.search(/m/);
        var dayPos = dateFormat.search(/d/);
        var yearPos = dateFormat.search(/Y/);         
        
        Event.on(Dom.get(showButton), "click", function() {

            if (!dialog) {
                             
                dialog = new YAHOO.widget.SimpleDialog("container_" + showButton, {
                    visible:false,
                    context:[showButton, "tl", "bl"],
                    buttons:[],
                    draggable:false,
                    close:true
                });
                
                dialog.setHeader(SUGAR.language.get('app_strings', 'LBL_MASSUPDATE_DATE'));
                dialog.setBody('<div id="' + showButton + '_div"></div>');
                dialog.render(document.body);

                dialog.showEvent.subscribe(function() {
                    if (YAHOO.env.ua.ie) {
                        // Since we're hiding the table using yui-overlay-hidden, we 
                        // want to let the dialog know that the content size has changed, when
                        // shown
                        dialog.fireEvent("changeContent");
                    }
                });
                
                // Hide Calendar if we click anywhere in the document other than the calendar
                Event.on(document, "click", function(e) {
                	
                    if(!dialog)
                    {
                       return;	
                    }                	
                	
                    var el = Event.getTarget(e);                   
                    var dialogEl = dialog.element;
                    if (el != dialogEl && !Dom.isAncestor(dialogEl, el) && el != Dom.get(showButton) && !Dom.isAncestor(Dom.get(showButton), el)) {
                        dialog.hide();
                        calendar = null;
                        dialog = null;
                    }
                });                
            }

            // Lazy Calendar Creation - Wait to create the Calendar until the first time the button is clicked.
            if (!calendar) {
            
                var navConfig = {
                    strings : {
                        month: SUGAR.language.get('app_strings', 'LBL_CHOOSE_MONTH'),
                        year: SUGAR.language.get('app_strings', 'LBL_ENTER_YEAR'),
                        submit: SUGAR.language.get('app_strings', 'LBL_EMAIL_OK'),
                        cancel: SUGAR.language.get('app_strings', 'LBL_CANCEL_BUTTON_LABEL'),
                        invalidYear: SUGAR.language.get('app_strings', 'LBL_ENTER_VALID_YEAR')
                    },
                    monthFormat: YAHOO.widget.Calendar.SHORT,
                    initialFocus: "year"
                };               	
            	
                calendar = new YAHOO.widget.Calendar(showButton + '_div', {
                    iframe:false,
                    hide_blank_weeks:true,
                    navigator:navConfig
                });
                
                calendar.cfg.setProperty('DATE_FIELD_DELIMITER', date_field_delimiter);
                calendar.cfg.setProperty('MDY_DAY_POSITION', dayPos+1);
                calendar.cfg.setProperty('MDY_MONTH_POSITION', monthPos+1);
                calendar.cfg.setProperty('MDY_YEAR_POSITION', yearPos+1);
                
                //Configure the month and days label with localization support where defined
                if(typeof SUGAR.language.languages['app_list_strings'] != 'undefined' && SUGAR.language.languages['app_list_strings']['dom_cal_month_long'] != 'undefined')
                {
                	if(SUGAR.language.languages['app_list_strings']['dom_cal_month_long'].length == 13)
                	{
                	   SUGAR.language.languages['app_list_strings']['dom_cal_month_long'].shift();
                	}
                	calendar.cfg.setProperty('MONTHS_LONG', SUGAR.language.languages['app_list_strings']['dom_cal_month_long']);
                }
                
                if(typeof SUGAR.language.languages['app_list_strings'] != 'undefined'  && typeof SUGAR.language.languages['app_list_strings']['dom_cal_day_short'] != 'undefined')
                {
                	if(SUGAR.language.languages['app_list_strings']['dom_cal_day_short'].length == 8)
                	{
                	   SUGAR.language.languages['app_list_strings']['dom_cal_day_short'].shift();
                	}                	
                	calendar.cfg.setProperty('WEEKDAYS_SHORT', SUGAR.language.languages['app_list_strings']['dom_cal_day_short']);
                }
                
                calendar.selectEvent.subscribe(function() {
                    var input = Dom.get(inputField);
					if (calendar.getSelectedDates().length > 0) {

                        var selDate = calendar.getSelectedDates()[0];
                        var monthVal = selDate.getMonth() + 1; //Add one for month value
                        if(monthVal < 10)
                        {
                           monthVal = '0' + monthVal;	
                        }
                        
                        var dateVal = selDate.getDate();
                        
                        if(dateVal < 10)
                        {
                           dateVal = '0' + dateVal;	
                        }
                        
                        var yearVal = selDate.getFullYear();
                        
                        selDate = '';
                        if(monthPos == 0) {
                          selDate = monthVal;
                        } else if(dayPos == 0) {
                          selDate = dateVal;
                        } else {
                          selDate = yearVal;
                        }
                        
                        if(monthPos == 1) {
                          selDate += date_field_delimiter + monthVal;
                        } else if(dayPos == 1) {
                          selDate += date_field_delimiter + dateVal;
                        } else {
                          selDate += date_field_delimiter + yearVal;
                        }
                        
                        if(monthPos == 2) {
                          selDate += date_field_delimiter + monthVal;
                        } else if(dayPos == 2) {
                          selDate += date_field_delimiter + dateVal;                       	
                        } else {
                          selDate += date_field_delimiter + yearVal;
                        }

                        input.value = selDate;
                        
                        if(params.comboObject)
                        {
                           params.comboObject.update();
                        }
                    } else {
                        input.value = "";
                    }

                    dialog.hide();
					//Fire any on-change events for this input field
					var listeners = YAHOO.util.Event.getListeners(input, 'change');
					if (listeners != null) {
						for (var i = 0; i < listeners.length; i++) {
							var l = listeners[i];
							l.fn.call(l.scope ? l.scope : this, l.obj);
						}
					}
                });

                calendar.renderEvent.subscribe(function() {
                    // Tell Dialog it's contents have changed, which allows 
                    // container to redraw the underlay (for IE6/Safari2)
                    dialog.fireEvent("changeContent");
                });
               
            }
            
            var seldate = calendar.getSelectedDates();
            if (Dom.get(inputField).value.length > 0) {
            	val = new Date(Dom.get(inputField).value);
            	if(!isNaN(val.getTime()))
            	{
	            	calendar.cfg.setProperty("selected", Dom.get(inputField).value);
	                seldate = Dom.get(inputField).value.split(date_field_delimiter);       	
	            	calendar.cfg.setProperty("pagedate", seldate[monthPos] + calendar.cfg.getProperty("DATE_FIELD_DELIMITER") + seldate[yearPos]);
	            }
            } else if (seldate.length > 0) {
                // Set the pagedate to show the selected date if it exists
                calendar.cfg.setProperty("selected", seldate[0]);
                var month = seldate[0].getMonth() + 1;
                var year = seldate[0].getFullYear();
                calendar.cfg.setProperty("pagedate", month + calendar.cfg.getProperty("DATE_FIELD_DELIMITER") + year);         	
            }      

            calendar.render();
            dialog.show();
        });
    });	
};