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
/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.ListViewBottom
 * @alias SUGAR.App.layout.ListViewBottom
 * @extends View.View
 */
    // We listen to event and keep track if search filter is toggled open/close
    filterOpened: false,
    events: {
        'click [name=show_more_button]': 'showMoreRecords'
    },
    _renderHtml: function() {

        // Dashboard layout injects shared context with limit: 5. 
        // Otherwise, we don't set so fetches will use max query in config.
        this.limit = this.context.get('limit') ? this.context.get('limit') : null;

        app.view.View.prototype._renderHtml.call(this);

        // We listen for if the search filters are opened or not. If so, when 
        // user clicks show more button, we treat this as a search, otherwise,
        // normal show more for list view.
        this.layout.off("list:filter:toggled", null, this);
        this.layout.on("list:filter:toggled", this.filterToggled, this);
    },
    filterToggled: function(isOpened) {
        this.context.set('filterOpened', isOpened);
    },
    showMoreRecords: function(evt) {
        var self = this, options;
        // Mark current models as old, in order to animate the new one
        _.each(this.collection.models, function(model) {
            model.old = true;
        });
        
        // Display loading message
        app.alert.show('show_more_records_' + self.cid, {level:'process', title:app.lang.getAppString('LBL_PORTAL_LOADING')});
        
        // save current screen position
        var screenPosition = $('html').offset().top;

        // If in "search mode" (the search filter is toggled open) set q:term param
        options = this.context.get('filterOpened') ? self.getSearchOptions() : {};

        // Indicates records will be added to those already loaded in to view
        options.add = true;
            
        options.success = function() {
            // Hide loading message
            app.alert.dismiss('show_more_records_' + self.cid);
            self.layout.trigger("list:paginate:success");
            self.render();
            // retrieve old screen position
            window.scrollTo(0, -1*screenPosition);

            // Animation for new records
            self.layout.$('tr.new').animate({
                opacity:1
            }, 500, function () {
                $(this).removeAttr('style class');
            });
        };
        options.limit = this.limit;
        this.collection.paginate(options);
    },
    getSearchOptions: function() {
        var collection, options, previousTerms, term = '';
        collection = this.context.get('collection');

        // If we've made a previous search for this module grab from cache
        if(app.cache.has('previousTerms')) {
            previousTerms = app.cache.get('previousTerms');
            if(previousTerms) {
                term = previousTerms[this.module];
            } 
        }
        // build search-specific options and return
        options = {
            params: { 
                q: term
            },
            fields: collection.fields ? collection.fields : this.collection
        };
        return options;
    },
    bindDataChange: function() {
        if(this.collection) {
            this.collection.on("reset", this.render, this);
        }
    }

})
