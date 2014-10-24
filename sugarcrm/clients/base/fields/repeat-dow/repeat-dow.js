/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * Repeat Day of Week is a custom field for Meetings & Calls modules used to set
 * days of the week for a Weekly recurring record.
 *
 * @class View.Fields.Base.RepeatDowField
 * @alias SUGAR.App.view.fields.BaseRepeatDowField
 * @extends View.Fields.Base.EnumField
 */
({
    extendsFrom: 'EnumField',

    defaultOnUndefined: false, //custom default behavior defined below

    /**
     * @inheritdoc
     *
     * Set default value for this field and
     * add validation (required if `repeat_type` is weekly)
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.type = 'enum';

        this.def['default'] = this.getDefaultDayOfWeek();

        this.model.addValidationTask(
            'repeat_dow_validator_' + this.cid,
            _.bind(this._doValidateRepeatDow, this)
        );
    },

    /**
     * Get the default day of week (current day of the week)
     */
    getDefaultDayOfWeek: function() {
        var isoDayOfWeek = app.date().isoWeekday(),
            sugarDayOfWeek = (isoDayOfWeek === 7) ? 0 : isoDayOfWeek;
        return sugarDayOfWeek.toString();
    },

    /**
     * @inheritdoc
     *
     * Remove blank element from the days of the week list -
     * Day of week list starts Sunday with 1, but needs to start with 0 for API -
     * Transform the list of items to conform to the API spec
     */
    loadEnumOptions: function(fetch, callback) {
        var enumOptions = this.def.options;
        if (enumOptions === 'dom_cal_day_short' || enumOptions === 'dom_cal_day_long') {
            this.items = this._transformDowItems(app.lang.getAppListStrings(enumOptions));
        } else {
            this._super('loadEnumOptions', [fetch, callback]);
        }
    },

    /**
     * Remove blank element from the days of the week list -
     * Day of week list starts Sunday with 1, but needs to start with 0 for API -
     * Transform the list of items to conform to API spec
     *
     * @param {Object} items Days of the week list needing to be transformed
     * @returns {Object}
     * @private
     */
    _transformDowItems: function(items) {
        return _.reduce(items, function(newItems, value, key) {
            // remove empty key 0 and shift keys down one
            if (key !== '0') {
                newItems[key-1] = value;
            }
            return newItems;
        }, {});
    },

    /**
     * @inheritdoc
     *
     * Model day of week format is a string of numeric characters ('1'-'7')
     * Select2 needs an array of these numeric strings
     */
    format: function(value) {
        return (_.isString(value)) ? value.split('').sort() : value;
    },

    /**
     * @inheritdoc
     *
     * Select2 array of numeric strings to Model numeric string format
     */
    unformat: function(value) {
        return (_.isArray(value)) ? value.sort().join('') : value;
    },

    /**
     * Custom required validator for the `repeat_dow` field.
     *
     * This validates `repeat_dow` based on the value of `repeat_type` -
     * if Weekly repeat type, repeat day of week must be specified
     *
     * @param {Object} fields The list of field names and their definitions.
     * @param {Object} errors The list of field names and their errors.
     * @param {Function} callback Async.js waterfall callback.
     * @private
     */
    _doValidateRepeatDow: function(fields, errors, callback) {
        var repeatType = this.model.get('repeat_type'),
            repeatDow = this.model.get(this.name);

        if (repeatType === 'Weekly' && (!_.isString(repeatDow) || repeatDow.length < 1)) {
            errors[this.name] = {'required': true};
        }
        callback(null, fields, errors);
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        this.model._validationTasks = _.omit(
            this.model._validationTasks,
            'repeat_dow_validator_' + this.cid
        );
        this._super('_dispose');
    }
})
