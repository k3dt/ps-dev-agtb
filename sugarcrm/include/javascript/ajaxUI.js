/**
 * Javascript file for Sugar
 *
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: ajaxUI.js 57264 2010-07-02 18:45:27Z kjing $

SUGAR.ajaxUI = {
    loadingWindow : false,
    callback : function(o)
    {
        var cont;
        if (typeof window.onbeforeunload == "function")
            window.onbeforeunload = null;
        scroll(0,0);
        ajaxStatus.hideStatus();
        try{
            var r = YAHOO.lang.JSON.parse(o.responseText);
            cont = r.content;
            if (r.moduleList)
            {
                SUGAR.themes.setModuleTabs(r.moduleList);
            }
            if (r.title)
            {
                document.title = html_entity_decode(r.title);
            }
            if (r.action)
            {
                action_sugar_grp1 = r.action;
            }
            //SUGAR.themes.setCurrentTab(r.menu);
            var c = document.getElementById("content");
            c.innerHTML = cont;
            SUGAR.util.evalScript(cont);
            // set response time from ajax response
            if(typeof(r.responseTime) != 'undefined'){
                var rt = document.getElementById('responseTime');
                if(rt != null){
                    rt.innerHTML = r.responseTime;
                }
            }
        } catch (e){
            if (!SUGAR.ajaxUI.errorPanel) {
                SUGAR.ajaxUI.errorPanel = new YAHOO.widget.Panel("ajaxUIErrorPanel", {
                    modal: false,
                    visible: true,
                    constraintoviewport: true,
                    width	: "800px",
                    height : "600px",
                    close: true
                });
            }
            var panel = SUGAR.ajaxUI.errorPanel;
            panel.setHeader( SUGAR.language.get('app_strings','ERR_AJAX_LOAD')) ;
            panel.setBody('<iframe id="ajaxErrorFrame" style="width:780px;height:550px;border:none;marginheight="0" marginwidth="0" frameborder="0""></iframe>');
            panel.render(document.body);
            SUGAR.util.doWhen(
				function(){
					var f = document.getElementById("ajaxErrorFrame");
					return f != null && f.contentWindow != null && f.contentWindow.document != null;
				}, function(){
					document.getElementById("ajaxErrorFrame").contentWindow.document.body.innerHTML = o.responseText;
					window.setTimeout('throw "AjaxUI error parsing response"', 300);
			});
            panel.show();
            panel.center();

            throw "AjaxUI error parsing response";
        }
    },

    canAjaxLoadModule : function(module)
    {
        // Return false if ajax ui is completely disabled
        if(typeof(SUGAR.config.disableAjaxUI) != 'undefined' && SUGAR.config.disableAjaxUI == true){
            return false;
        }
        
        var bannedModules = SUGAR.config.stockAjaxBannedModules;
        //If banned modules isn't there, we are probably on a page that isn't ajaxUI compatible
        if (typeof(bannedModules) == 'undefined')
            return false;
        // Mechanism to allow for overriding or adding to this list
        if(typeof(SUGAR.config.addAjaxBannedModules) != 'undefined'){
            bannedModules.concat(SUGAR.config.addAjaxBannedModules);
        }
        if(typeof(SUGAR.config.overrideAjaxBannedModules) != 'undefined'){
            bannedModules = SUGAR.config.overrideAjaxBannedModules;
        }
        
        return SUGAR.util.arrayIndexOf(bannedModules, module) == -1;
    },

    loadContent : function(url, params)
    {
        if(YAHOO.lang.trim(url) != "")
        {
            //Don't ajax load certain modules
            var mRegex = /module=([^&]*)/.exec(url);
            var module = mRegex ? mRegex[1] : false;
            if (module && SUGAR.ajaxUI.canAjaxLoadModule(module))
            {
                YAHOO.util.History.navigate('ajaxUILoc',  url);
            } else {
                window.location = url;
            }
        }
    },

    go : function(url)
    {
        if(YAHOO.lang.trim(url) != "")
        {
            var con = YAHOO.util.Connect, ui = SUGAR.ajaxUI;
            if (ui.lastURL == url)
                return;
            var inAjaxUI = /action=ajaxui/.exec(window.location);
            if (inAjaxUI && typeof (window.onbeforeunload) == "function"
                    && window.onbeforeunload() && !confirm(window.onbeforeunload()))
            {
                YAHOO.util.History.navigate('ajaxUILoc',  ui.lastURL);
                return;
            }
            if (ui.lastCall && con.isCallInProgress(ui.lastCall)) {
                con.abort(ui.lastCall);
            }
            var mRegex = /module=([^&]*)/.exec(url);
            var module = mRegex ? mRegex[1] : false;
            //If we can't ajax load the module (blacklisted), set the URL directly.
            if (!ui.canAjaxLoadModule(module)) {
                window.location = url;
                return;
            }
            ui.lastURL = url;
            ui.cleanGlobals();
            var loadLanguageJS = '';
            if(module && typeof(SUGAR.language.languages[module]) == 'undefined'){
                loadLanguageJS = '&loadLanguageJS=1';
            }

            if (!inAjaxUI) {
                //If we aren't in the ajaxUI yet, we need to reload the page to get setup properly
                if (!SUGAR.isIE)
                    window.location.replace("index.php?action=ajaxui#ajaxUILoc=" + encodeURIComponent(url));
                else {
                    //if we use replace under IE, it will cache the page as the replaced version and thus no longer load the previous page.
                    window.location.hash = "#";
                    window.location.assign("index.php?action=ajaxui#ajaxUILoc=" + encodeURIComponent(url));
                }
            }
            else {
                ajaxStatus.showStatus( SUGAR.language.get('app_strings','LBL_LOADING')) ;
                ui.lastCall = YAHOO.util.Connect.asyncRequest('GET', url + '&ajax_load=1' + loadLanguageJS, {
                    success: SUGAR.ajaxUI.callback
                });
            }
        }
    },

    submitForm : function(formname, params)
    {
        var con = YAHOO.util.Connect, SA = SUGAR.ajaxUI;
        if (SA.lastCall && con.isCallInProgress(SA.lastCall)) {
            con.abort(SA.lastCall);
        }
        //Reset the EmailAddressWidget before loading a new page
        SA.cleanGlobals();
        //Don't ajax load certain modules
        var form = YAHOO.util.Dom.get(formname) || document.forms[formname];
        if (SA.canAjaxLoadModule(form.module.value)
            //Do not try to submit a form that contains a file input via ajax.
            && typeof(YAHOO.util.Selector.query("input[type=file]", form)[0]) == "undefined"
            //Do not try to ajax submit a form if the ajaxUI is not initialized
            && /action=ajaxui/.exec(window.location))
        {
            var string = con.setForm(form);
            var baseUrl = "index.php?action=ajaxui#ajaxUILoc=";
            SA.lastURL = "";
            //Use POST for long forms and GET for short forms (GET allow resubmit via reload)
            ajaxStatus.showStatus( SUGAR.language.get('app_strings','LBL_LOADING')) ;
            if(string.length > 200)
            {
                con.asyncRequest('POST', 'index.php?ajax_load=1', {
                    success: SA.callback
                });
                window.location=baseUrl;
            } else {
                con.resetFormState();
                window.location = baseUrl + encodeURIComponent("index.php?" + string);
            }
            return true;
        } else {
            form.submit();
            return false;
        }
    },

    cleanGlobals : function()
    {
        sqs_objects = {};
        QSProcessedFieldsArray = {};
        collection = {};
        //Reset the EmailAddressWidget before loading a new page
        if (SUGAR.EmailAddressWidget){
            SUGAR.EmailAddressWidget.instances = {};
            SUGAR.EmailAddressWidget.count = {};
        }
        YAHOO.util.Event.removeListener(window, 'resize');

    },
    firstLoad : function()
    {
        //Setup Browser History
        var url = YAHOO.util.History.getBookmarkedState('ajaxUILoc');
        var aRegex = /action=([^&#]*)/.exec(window.location);
        var action = aRegex ? aRegex[1] : false;
        var mRegex = /module=([^&#]*)/.exec(window.location);
        var module = mRegex ? mRegex[1] : false;
        if (module != "ModuleBuilder")
        {
            var go = url != null || action == "ajaxui";
            url = url ? url : 'index.php?module=Home&action=index';
            YAHOO.util.History.register('ajaxUILoc', url, SUGAR.ajaxUI.go);
            YAHOO.util.History.initialize("ajaxUI-history-field", "ajaxUI-history-iframe");
            SUGAR.ajaxUI.hist_loaded = true;
            if (go)
                SUGAR.ajaxUI.go(url);
        }
        SUGAR_callsInProgress--;
    },
    print: function()
    {
        var url = YAHOO.util.History.getBookmarkedState('ajaxUILoc');
        SUGAR.util.openWindow(
            url + '&print=true',
            'printwin',
            'menubar=1,status=0,resizable=1,scrollbars=1,toolbar=0,location=1'
        );
    }
};
