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
({
    events: {
        'click #languageList .dropdown-menu a' : 'setLanguage'
    },
    tagName: "span",
    /**
     * @override
     * @param {Object} options
     */
    initialize: function(options) {
        app.events.on("app:sync:complete", this.render, this);
        app.events.on("app:login:success", this.render, this);
        app.events.on("app:logout", this.render, this);
        app.view.View.prototype.initialize.call(this, options);
        $(window).on('resize', _.bind(this.adjustMenuHeight,this));
    },
    /**
     * @override
     * @private
     */
    _renderHtml: function() {
        this.isAuthenticated = app.api.isAuthenticated();
        this.currentLang = app.lang.getLanguage() || "en_us";
        this.languageList = this.formatLanguageList();
        app.view.View.prototype._renderHtml.call(this);
        this.$('[data-toggle="dropdown"]').dropdown();
        this.adjustMenuHeight();
    },
    /**
     * When a user selects a language in the dropdown, set this language.
     * Note that on login, user's preferred language will be updated to this language
     *
     * @param {Event} e
     */
    setLanguage: function(e) {
        var $li = this.$(e.currentTarget),
            langKey = $li.data("lang-key");
        app.alert.show('language', {level: 'warning', title: app.lang.getAppString('LBL_LOADING_LANGUAGE'), autoclose: false});
        app.lang.setLanguage(langKey, function() {
            app.alert.dismiss('language');
        });
    },
    adjustMenuHeight: function(){
        var footerHeight = $("footer").height(),
            viewportHeight = $(window).height(),
            currentMenuHeight = this.$('.dropdown-menu').height(),
            menuHeight = viewportHeight - footerHeight;
        this.$('.dropdown-menu').css('max-height',menuHeight);
    },
    /**
     * Formats the language list for the template
     *
     * @returns {Array} of languages
     */
    formatLanguageList: function() {
        // Format the list of languages for the template
        var list = [],
            languages = app.lang.getAppListStrings('available_language_dom');

        _.each(languages, function(label, key) {
            if (key !== '') {
                list.push({ key: key, value: label });
            }
        });
        return list;
    },
    /**
     * @inheritdoc
     */
    _dispose: function() {
        $(window).off('resize');
        app.view.View.prototype._dispose.call(this);
    }
})
