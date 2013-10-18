/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    /**
     * {@inheritDoc}
     *
     * Unformats the integer based on userPreferences (grouping separator).
     * If we weren't able to parse the value, `undefined` is returned.
     *
     * @param {String} value the formatted value based on user preferences.
     * @return {Number|undefined} the unformatted value.
     */
    unformat: function(value) {
        return app.utils.unformatNumberStringLocale(value, true);
    },

    /**
     * {@inheritDoc}
     *
     * Formats the integer based on user preferences (grouping separator).
     * If the field definition has `disabled_num_format` as `true` the value
     * won't be formatted. Also, if the value isn't a finite integer it will
     * return `undefined`.
     *
     * @param {Number} value the integer value to format as per user
     *   preferences.
     * @return {String|undefined} the formatted value based as per user
     *   preferences.
     */
    format: function(value) {
        var numberGroupSeparator = '', decimalSeparator = '';

        if (!this.def.disable_num_format) {
            numberGroupSeparator = app.user.getPreference('number_grouping_separator') || ',';
            decimalSeparator = app.user.getPreference('decimal_separator') || '.';
        }

        return app.utils.formatNumber(
            value, 0, 0,
            numberGroupSeparator,
            decimalSeparator
        );
    }
})
