/**
 * 
 * Find more about the scrolling function at
 * http://cubiq.org/iscroll
 *
 * Copyright (c) 2010 Matteo Spinelli, http://cubiq.org/
 * Released under MIT license
 * http://cubiq.org/dropbox/mit-license.txt
 * 
 * Version 3.7.1 - Last updated: 2010.10.08
 * 
 */
SUGAR.ajaxUI = {
    callback : function(o)
    {
        var cont;
        if (typeof window.onbeforeunload == "function")
            window.onbeforeunload = null;
        try{
            var r = YAHOO.lang.JSON.parse(o.responseText);
            cont = r.content;
            if (r.moduleList)
            {
                SUGAR.themes.setModuleTabs(r.moduleList);
            }
            if (r.title)
            {
                document.title = r.title.replace(/&raquo;/g, '>').replace(/&nbsp;/g, ' ');
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
            if(YAHOO.lang.trim(o.responseText) == "" && o.responseText.charAt(0) != '{') {
                document.body.innerHTML = "An error has occured:<br/>" + o.responseText;
                SUGAR.util.evalScript(document.body.innerHTML);
            } else if (typeof(console) != "undefined" && typeof(console.log) == "function")
            {
                console.log("invalid JSON response:");
                console.log(o.responseText);
            }
        }
    },

    canAjaxLoadModule : function(module)
    {
        // Return false if ajax ui is completely disabled
        if(typeof(SUGAR.config.disableAjaxUI) != 'undefined' && SUGAR.config.disableAjaxUI == true){
            return false;
        }
        
        var bannedModules = SUGAR.config.stockAjaxBannedModules;
        // Mechanism to allow for overriding or adding to this list
        if(typeof(SUGAR.config.addAjaxBannedModules) != 'undefined'){
            bannedModules.concat(SUGAR.config.addAjaxBannedModules);
        }
        if(typeof(SUGAR.config.overrideAjaxBannedModules) != 'undefined'){
            bannedModules = SUGAR.config.overrideAjaxBannedModules;
        }
        return bannedModules.indexOf(module) == -1;
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

    go : function(url, params)
    {
        
        if(YAHOO.lang.trim(url) != "")
        {
            var con = YAHOO.util.Connect, ui = SUGAR.ajaxUI;
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
            ui.cleanGlobals();
            var loadLanguageJS = '';
            if(module && typeof(SUGAR.language.languages[module]) == 'undefined'){
                loadLanguageJS = '&loadLanguageJS=1';
            }

            if (!/action=ajaxui/.exec(window.location))
                //If we aren't in the ajaxUI yet, we need to reload the page to get setup properly
                window.location = "index.php?action=ajaxui#ajaxUILoc=" + encodeURIComponent(url);
            else {
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
            //Use POST for long forms and GET for short forms (GET allow resubmit via reload)
            if(string.length > 200)
            {
                con.asyncRequest('POST', 'index.php?ajax_load=1', {
                    success: SA.callback
                });
                window.location="index.php?action=ajaxui#ajaxUILoc=";
            } else {
                con.resetFormState();
                window.location = "index.php?action=ajaxui#ajaxUILoc=" + encodeURIComponent("index.php?" + string);
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
        url = url ? url : 'index.php?module=Home&action=index';

        YAHOO.util.History.register('ajaxUILoc', url, SUGAR.ajaxUI.go);
        YAHOO.util.History.initialize("ajaxUI-history-field", "ajaxUI-history-iframe");
        SUGAR.ajaxUI.hist_loaded = true;
        SUGAR.ajaxUI.go(url);
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
