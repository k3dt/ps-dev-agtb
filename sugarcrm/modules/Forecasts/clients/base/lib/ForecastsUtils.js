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
(function(app) {
    var forecastsUtils = {

        /**
         * Takes two Forecasts models and returns HTML for the history log
         *
         * @param oldestModel {Backbone.Model} the oldest model by date_entered
         * @param newestModel {Backbone.Model} the most recent model by date_entered
         * @return {Object}
         */
        createHistoryLog: function(oldestModel, newestModel) {
            var is_first_commit = false;

            if(_.isEmpty(oldestModel)) {
                oldestModel = new Backbone.Model({
                    best_case: 0,
                    likely_case: 0,
                    worst_case: 0,
                    date_entered: ''
                });
                is_first_commit = true;
            }
            var best_difference = this.getDifference(oldestModel, newestModel, 'best_case'),
                best_direction = this.getDirection(best_difference),
                likely_difference = this.getDifference(oldestModel, newestModel, 'likely_case'),
                likely_direction = this.getDirection(likely_difference),
                worst_difference = this.getDifference(oldestModel, newestModel, 'worst_case'),
                worst_direction = this.getDirection(worst_difference),
                args = [],
                text = 'LBL_COMMITTED_HISTORY_NONE_CHANGED',
                best_arrow = this.getArrowDirectionSpan(best_direction),
                likely_arrow = this.getArrowDirectionSpan(likely_direction),
                worst_arrow = this.getArrowDirectionSpan(worst_direction),
                num_shown = 0,
                hb = Handlebars.compile("{{str_format key module args}}"),
                lang_string_key = '',
                final_args = [],
                labels = [],
                setup_or_updated_lang_key = (is_first_commit) ? '_SETUP' : '_UPDATED',
                likely_args = {
                    changed: likely_difference != 0,
                    show: app.metadata.getModule('Forecasts', 'config').show_worksheet_likely
                },
                best_args = {
                    changed: best_difference != 0,
                    show: app.metadata.getModule('Forecasts', 'config').show_worksheet_best
                },
                worst_args = {
                    changed: worst_difference != 0,
                    show: app.metadata.getModule('Forecasts', 'config').show_worksheet_worst
                };

            // increment num_shown for each variable that is true
            likely_args.show ? num_shown++ : '';
            best_args.show ? num_shown++ : '';
            worst_args.show ? num_shown++ : '';

            // set the key for the lang string
            lang_string_key = 'LBL_COMMITTED_HISTORY_' + num_shown + '_SHOWN';

            //determine what changed and add parts to the array for displaying the changes
            if(likely_args.changed && likely_args.show) {
                final_args.push(
                    this.gatherLangArgsByParams(likely_direction, likely_arrow, likely_difference, newestModel, 'likely_case')
                );
            } else if(likely_args.show) {
                // push an empty array for args
                final_args.push([]);
            }

            if(best_args.changed && best_args.show) {
                final_args.push(
                    this.gatherLangArgsByParams(best_direction, best_arrow, best_difference, newestModel, 'best_case')
                );
            } else if(best_args.show) {
                // push an empty array for args
                final_args.push([]);
            }

            if(worst_args.changed && worst_args.show) {
                final_args.push(
                    this.gatherLangArgsByParams(worst_direction, worst_arrow, worst_difference, newestModel, 'worst_case')
                );
            } else if(worst_args.show) {
                // push an empty array for args
                final_args.push([]);
            }

            // get the final args to go into the main text
            labels = this.getCommittedHistoryLabel(best_args, likely_args, worst_args, is_first_commit);

            final_args = this.parseArgsAndLabels(final_args, labels);

            //Compile the language string for the log
            var text = hb({'key': lang_string_key, 'module': 'Forecasts', 'args': final_args});

            // Check for first time run -- no date_entered for oldestModel
            var oldestDateEntered = oldestModel.get('date_entered');

            // This will always have a value and Format the date according to the user date and time preferences
            var newestModelDate = new Date(Date.parse(newestModel.get('date_entered'))),
                text2 = '',
                newestModelDisplayDate = app.date.format(newestModelDate, app.user.getPreference('datepref') + ' ' + app.user.getPreference('timepref'));

            if(!_.isEmpty(oldestDateEntered)) {
                var oldestModelDate = new Date(Date.parse(oldestDateEntered)),
                    yearDiff = oldestModelDate.getYear() - newestModelDate.getYear(),
                    monthsDiff = oldestModelDate.getMonth() - newestModelDate.getMonth();

                if(yearDiff == 0 && monthsDiff < 2) {
                    args = [newestModelDisplayDate];
                    text2 = hb({'key': 'LBL_COMMITTED_THIS_MONTH', 'module': 'Forecasts', 'args': args});
                } else {
                    args = [monthsDiff, newestModelDisplayDate];
                    text2 = hb({'key': 'LBL_COMMITTED_MONTHS_AGO', 'module': 'Forecasts', 'args': args});
                }
            } else {
                args = [newestModelDisplayDate];
                text2 = hb({'key': 'LBL_COMMITTED_THIS_MONTH', 'module': 'Forecasts', 'args': args});
            }

            // need to tell Handelbars not to escape the string when it renders it, since there might be
            // html in the string, args returned for testing purposes
            return {'text': new Handlebars.SafeString(text), 'text2': new Handlebars.SafeString(text2)};
        },

        /**
         * Returns an array of three args for the html for the arrow, the difference (amount changed), and the new value
         *
         * @param dir {String} direction of the arrow, LBL_UP/LBL_DOWN
         * @param arrow {String} HTML for the arrow string
         * @param diff {Number} difference between the new model and old model
         * @param model {Backbone.Model} the newestModel being used so we can get the current caseStr
         * @param attrStr {String} the attr string to get from the newest model
         */
        gatherLangArgsByParams: function(dir, arrow, diff, model, attrStr) {
            var args = [];
            args.push(app.lang.get(dir, 'Forecasts') + arrow);
            args.push(app.currency.formatAmountLocale(Math.abs(diff)));
            args.push(app.currency.formatAmountLocale(model.get(attrStr)));
            return args;
        },

        /**
         * checks the direction class passed in to determine what span to create to show the appropriate arrow
         * or lack of arrow to display on the
         * @param directionClass class being used for the label ('LBL_UP' or 'LBL_DOWN')
         * @return {String}
         */
        getArrowDirectionSpan: function(directionClass) {
            return directionClass == "LBL_UP" ? '&nbsp;<span class="icon-arrow-up font-green"></span>' :
                directionClass == "LBL_DOWN" ? '&nbsp;<span class="icon-arrow-down font-red"></span>' : '';
        },

        /**
         * builds the args to look up for the history label based on what has changed in the model
         * @param best {Object}
         * @param likely {Object}
         * @param worst {Object}
         * @param is_first_commit {bool}
         * @return {Array}
         */
        getCommittedHistoryLabel: function(best, likely, worst, is_first_commit) {
            var args = [];

            // Handle if this is the first commit
            if(is_first_commit) {
                args.push('LBL_COMMITTED_HISTORY_SETUP_FORECAST');
            } else {
                args.push('LBL_COMMITTED_HISTORY_UPDATED_FORECAST');
            }

            // Handle Likely
            if(likely.show) {
                if(likely.changed) {
                    args.push('LBL_COMMITTED_HISTORY_LIKELY_CHANGED');
                } else {
                    args.push('LBL_COMMITTED_HISTORY_LIKELY_SAME');
                }
            }

            // Handle Best
            if(best.show) {
                if(best.changed) {
                    args.push('LBL_COMMITTED_HISTORY_BEST_CHANGED');
                } else {
                    args.push('LBL_COMMITTED_HISTORY_BEST_SAME');
                }
            }

            // Handle Worst
            if(worst.show) {
                if(worst.changed) {
                    args.push('LBL_COMMITTED_HISTORY_WORST_CHANGED');
                } else {
                    args.push('LBL_COMMITTED_HISTORY_WORST_SAME');
                }
            }

            return args;
        },

        /**
         * Parses through labels array and adds the proper args in to the string
         *
         * @param argsArray {Array} of args (direction arrow html, amount difference and the new amount)
         * @param labels {Array} of lang key labels to use
         * @return {Array}
         */
        parseArgsAndLabels: function(argsArray, labels) {
            var retArgs = [],
                hb = Handlebars.compile("{{str_format key module args}}");

            // labels should have one more item in its array than argsArray
            // because of the SETUP or UPDATED label which has no args
            if((argsArray.length + 1) != labels.length) {
                // SOMETHING CRAAAAZY HAPPENED!
                app.logger.error('ForecastsUtils.parseArgsAndLabels() :: argsArray and labels params are not the same length ');
                return null;
            }

            // get the first argument off the label array
            retArgs.push(hb({'key': _.first(labels), 'module': 'Forecasts', 'args': []}));

            // get the other values, with out the first value
            labels = _.last(labels, labels.length - 1);

            // loop though all the other values
            _.each(labels, function(label, index) {
                retArgs.push(hb({'key': label, 'module': 'Forecasts', 'args': argsArray[index]}))
            });

            return retArgs;
        },

        /**
         * Returns the difference between the newest model and the oldest
         *
         * @param oldModel {Backbone.Model}
         * @param newModel {Backbone.Model}
         * @param attr {String} the attribute key to get from the models
         * @return {*}
         */
        getDifference: function(oldModel, newModel, attr) {
            return newModel.get(attr) - oldModel.get(attr);
        },

        /**
         * Returns the proper direction label to use
         *
         * @param difference the amount of difference between newest and oldest models
         * @return {String} LBL_UP, LBL_DOWN, or ''
         */
        getDirection: function(difference) {
            return difference > 0 ? 'LBL_UP' : (difference < 0 ? 'LBL_DOWN' : '');
        },

        /**
         * Contains a list of column names from metadata and maps them to correct config param
         * e.g. 'likely_case' column is controlled by the Forecast config.show_worksheet_likely param
         * Used by forecastsWorksheetManager, forecastsWorksheetManagerTotals
         *
         * @property tableColumnsConfigKeyMapManager
         * @private
         */
        _tableColumnsConfigKeyMapManager: {
            'likely_case': 'show_worksheet_likely',
            'likely_case_adjusted': 'show_worksheet_likely',
            'best_case': 'show_worksheet_best',
            'best_case_adjusted': 'show_worksheet_best',
            'worst_case': 'show_worksheet_worst',
            'worst_case_adjusted': 'show_worksheet_worst'
        },

        /**
         * Contains a list of column names from metadata and maps them to correct config param
         * e.g. 'likely_case' column is controlled by the Forecast config.show_worksheet_likely param
         * Used by forecastsWorksheet, forecastsWorksheetTotals
         *
         * @property tableColumnsConfigKeyMapRep
         * @private
         */
        _tableColumnsConfigKeyMapRep: {
            'likely_case': 'show_worksheet_likely',
            'best_case': 'show_worksheet_best',
            'worst_case': 'show_worksheet_worst'
        },

        /**
         * Function checks the proper _tableColumnsConfigKeyMap___ for the key and returns the config setting
         *
         * @param key {String} table key name (eg: 'likely_case')
         * @param viewName {String} the name of the view calling the function (eg: 'forecastsWorksheet')
         * @return {*}
         */
        getColumnVisFromKeyMap: function(key, viewName) {
            var moduleMap = {
                'forecastsWorksheet': 'rep',
                'forecastsWorksheetTotals': 'rep',
                'forecastsWorksheetManager': 'mgr',
                'forecastsWorksheetManagerTotals': 'mgr'
            };

            // which key map to use from the moduleMap
            var whichKeyMap = moduleMap[viewName];

            // get the proper keymap
            var keyMap = (whichKeyMap === 'rep') ? this._tableColumnsConfigKeyMapRep : this._tableColumnsConfigKeyMapManager;

            var returnValue = app.metadata.getModule('Forecasts', 'config')[keyMap[key]];
            // If we've been passed a value that doesn't exist in the keymaps
            if(!_.isUndefined(returnValue)) {
                // convert it to boolean
                returnValue = returnValue == 1
            } else {
                // if return value was null (not found) then set to true
                returnValue = true;
            }
            return returnValue;
        },

        /**
         * Get the Datasets for the specified app list string that are only present via the specified config key list string combination
         *
         * @param app_list_dataset_name {String} variable to pull from app list strings for the datasets needed
         * @param cfg_key_prefix {String} config key part to prepend to the values of the app list string dataset, and will create a key to match within the config vars
         * @return {Object}
         */
        getAppConfigDatasets: function(app_list_dataset_name, cfg_key_prefix) {
            var ds = app.metadata.getStrings('app_list_strings')[app_list_dataset_name] || [];

            var returnDs = {};
            _.each(ds, function(value, key) {
                if(app.metadata.getModule('Forecasts', 'config')[cfg_key_prefix + key] == 1) {
                    returnDs[key] = value
                }
            }, this);
            return returnDs;
        },

        /**
         * If the passed in User is a Manager, then get his direct reportee's, and then set the user
         * on the context, if they are not a Manager, just set user to the context
         * @param selectedUser
         * @param context
         */
        getSelectedUsersReportees: function(selectedUser, context) {

            if(selectedUser.isManager) {
                var url = app.api.buildURL('Users', 'filter'),
                    post_args = {
                        "filter": [
                            {"reports_to_id": selectedUser.id}
                        ],
                        "fields": "full_name"
                    },
                    options = {};
                options.success = _.bind(function(resp, status, xhr) {
                    _.each(resp.records, function(user) {
                        selectedUser.reportees.push({id: user.id, name: user.full_name});
                    });
                    this.set("selectedUser", selectedUser)
                }, context);
                app.api.call("create", url, post_args, options);
            } else {
                // update context with selected user which will trigger checkRender
                context.set("selectedUser", selectedUser);
            }

        }
    };

    // play nice with other modules, check if utils already exists
    if(!_.has(app, 'utils')) {
        // if not, forecastsUtils should be the utils
        app.utils = forecastsUtils
    } else {
        // if utils already exists, add forecastsUtils to it
        app.utils = _.extend(app.utils, forecastsUtils);
    }
})(SUGAR.App);
