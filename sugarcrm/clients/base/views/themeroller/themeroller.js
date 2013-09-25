//FILE SUGARCRM flav=ent ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * View that displays edit view on a model
 * @class View.Views.EditView
 * @alias SUGAR.App.layout.EditView
 * @extends View.View
 */
({
    events: {
        "click [name=save_button]": "saveTheme",
        "click [name=refresh_button]": "loadTheme",
        "click [name=reset_button]": "resetTheme",
        "blur input": "previewTheme"
    },
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.customTheme = "default";
        this.loadTheme();
    },
    parseLessVars: function() {
        if (this.lessVars && this.lessVars.rel && this.lessVars.rel.length > 0) {
            _.each(this.lessVars.rel, function(obj, key) {
                this.lessVars.rel[key].relname = this.lessVars.rel[key].value;
                this.lessVars.rel[key].relname = this.lessVars.rel[key].relname.replace('@', '');
            }, this);
        }
    },
    _renderHtml: function() {
        if (!app.acl.hasAccess('admin', 'Administration')) {
            return;
        }
        this.parseLessVars();
        app.view.View.prototype._renderHtml.call(this);
        _.each(this.$('.hexvar[rel=colorpicker]'), function(obj, key) {
            $(obj).blur(function() {
                $(this).parent().parent().find('.swatch-col').css('backgroundColor', $(this).val());
            });
        }, this);
        this.$('.hexvar[rel=colorpicker]').colorpicker();
        this.$('.rgbavar[rel=colorpicker]').colorpicker({format: 'rgba'});
    },
    loadTheme: function() {
        this.themeApi('read', {}, _.bind(function(data) {
            this.lessVars = data;
            if (this.disposed) {
                return;
            }
            this.render();
            this.previewTheme();
        }, this));
    },
    saveTheme: function() {
        var self = this;
        // get the value from each input
        var colors = this.getInputValues();

        this.showMessage('LBL_SAVE_THEME_PROCESS');
        this.themeApi('create', colors, function() {
            app.alert.dismissAll();
        });
    },
    resetTheme: function() {
        var self = this;
        app.alert.show('reset_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('LBL_RESET_THEME_MODAL_INFO'),
            onConfirm: function () {
                self.showMessage('LBL_RESET_THEME_PROCESS');
                self.themeApi('create', {"reset": true}, function(data) {
                    app.alert.dismissAll();
                    self.loadTheme();
                });
            }
        });
    },
    previewTheme: function() {
        var colors = this.getInputValues();
        this.context.set("colors", colors);
    },
    themeApi: function(method, params, successCallback) {
        var self = this;
        _.extend(params, {
            platform: 'portal',
            themeName: self.customTheme
        });
        var paramsGET   = (method==='read') ? params : {},
            paramsPOST  = (method==='read') ? {} : params;
        var url = app.api.buildURL('theme', '', {}, paramsGET);
        app.api.call(method, url, paramsPOST,
            {
                success: successCallback ,
                error: function(error) {
                    app.error.handleHttpError(error);
                }
            },
            { context: self }
        );
    },
    getInputValues: function() {
        var colors = {};
        this.$('input').each(function() {
            var $this = $(this);
            colors[$this.attr("name")] = $this.hasClass('bgvar') ? '"' + $this.val() + '"' : $this.val();
        });
        return colors;
    },
    showMessage: function(messageKey) {
        app.alert.show('themeProcessing', {level: 'process', title: app.lang.getAppString(messageKey), closeable: true, autoclose: true});
    }
})
