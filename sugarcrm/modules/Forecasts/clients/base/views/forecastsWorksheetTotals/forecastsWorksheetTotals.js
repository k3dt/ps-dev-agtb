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
 * View that displays totals model for the forecastsWorksheetManager view
 * @extends View.View
 */
({
    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this.model.set({
            amount: 0,
            best_case: 0,
            worst_case: 0,
            overall_amount: 0,
            overall_best: 0,
            overall_worst: 0,
            show_worksheet_likely: app.util.getConfigValue('show_worksheet_likely'),
            show_worksheet_best: app.util.getConfigValue('show_worksheet_best'),
            show_worksheet_worst: app.util.getConfigValue('show_worksheet_worst')
        });
    },

    bindDataChange: function() {
        var self = this;
        this.context.on('change:updatedTotals', function(context, totals){
            self.model.set( totals );
            self._render();
        });

        this.context.on('forecasts:worksheet:rendered forecasts:worksheet:filtered', function() {
            self._render();
        })
    },

    /**
     * Special _render override that injects this model directly into the
     * forecastsWorksheetManager table/template
     * @private
     */
    _render: function() {
        // make sure forecastsWorksheet component is rendered first before rendering this
        if(this.context.get('currentWorksheet') == 'worksheet') {
            // if this template's items are already in the #summary table, remove them
            if(!_.isEmpty($('#forecastsWorksheetTotalsIncludedTotals').html())) {
                $('#forecastsWorksheetTotalsIncludedTotals').remove();
                $('#forecastsWorksheetTotalsOverallTotals').remove();
            }
            // Add forecastsWorksheetTotals.hbt to the forecastsWorksheet table footer
            $('#summary').html(this.template(this.model.toJSON()));
        }
    }
})

