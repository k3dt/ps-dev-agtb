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

    'events': {
        'click': 'updateCss',
        'focus input.input-large': 'handleInputFocus',
        'blur input.input-large': 'handleInputBlur'
    },
    transactionValue: '',
    _currencyField: null,

    /**
     * {@inheritdoc}
     *
     * Setup transactional amount if flag is present and transaction currency
     * is not base.
     * On edit view render the currency enum field associated with this field on
     * the correct placeholder
     *
     * @return {Object} this
     * @private
     */
    _render: function() {

        app.view.Field.prototype._render.call(this);

        if (this.action === 'edit' || this.action === 'disabled') {

            this.getCurrencyField().setElement(this.$('span[sfuuid="' + this.currencySfId + '"]'));
            this.$el.find('div.select2-container').css('min-width','8px');
            this.getCurrencyField().setDisabled(false);
            this.getCurrencyField().render();
            return this;
        }
        return this;
    },

    /**
     * When currency changes, we need to make appropriate silent changes to the base rate.
     */
    bindDataChange : function() {
        app.view.Field.prototype.bindDataChange.call(this);
        var currencyField = this.def.currency_field || 'currency_id';
        this.model.on('change:' + currencyField, function(model, value, options) {
            //When model is reset, it should not be called
            if(!value) {
                return;
            }
            var baseRateField = this.def.base_rate_field || 'base_rate';
            // manually force a change on the base rate in the model when the currency changes
            this.model.set(baseRateField, app.metadata.getCurrency(value).conversion_rate);
        }, this);
    },

    /**
     * disable currency dropdown when input is focused
     *
     * @param {Object} e element.
     */
    handleInputFocus: function(e) {
        this.getCurrencyField().setDisabled(true);
    },

    /**
     * enable currency dropdown when input is blurred
     *
     * @param {Object} e element.
     */
    handleInputBlur: function(e) {
        this.getCurrencyField().setDisabled(false);
    },

    /**
     * {@inheritdoc}
     *
     * Convert to base currency if flag is present.
     *
     * @param {Array/Object/String/Number/Boolean} value The value to format.
     * @return {String} the formatted value based on view name.
     */
    format: function(value) {
        if (_.isNull(value) || _.isUndefined(value) || _.isNaN(value)){
            value = "";
        }

        if (this.tplName === 'edit' || (this.tplName == 'disabled' && this.action == 'disabled')) {
            this.currencySfId = this.getCurrencyField().sfId;

            return app.utils.formatNumberLocale(value);
        }

        // TODO review this forecasts requirement and make it work with css defined on metadata
        // force this to recalculate the transaction value if needed
        // and more importantly, clear out previous transaction value
        this.transactionValue = '';
        if (this.def.convertToBase &&
            this.def.showTransactionalAmount &&
            this.model.get(this.def.currency_field || 'currency_id') !== app.currency.getBaseCurrencyId()
        ) {

            this.transactionValue = app.currency.formatAmountLocale(
                this.model.get(this.name),
                this.model.get(this.def.currency_field || 'currency_id')
            );
        }

        var baseRate = this.model.get(this.def.base_rate_field || 'base_rate');
        var currencyId = this.model.get(this.def.currency_field || 'currency_id');

        if (this.def.convertToBase) {
            value = app.currency.convertWithRate(value, baseRate) || 0;
            currencyId = app.currency.getBaseCurrencyId();
        }
        return app.currency.formatAmountLocale(value, currencyId);
    },

    /**
     * {@inheritdoc}
     *
     * @param {String} value The value to unformat.
     * @return {Number} Unformatted value.
     */
    unformat: function(value) {

        if (this.tplName === 'edit') {
            return app.utils.unformatNumberStringLocale(value);
        }

        return app.currency.unformatAmountLocale(value);
    },
    updateCss: function(){
      $('div.select2-drop.select2-drop-active').width('auto');
    },
    /**
     * Get the currency field related to this currency amount.
     *
     * @return {View.Field} the currency field associated.
     */
    getCurrencyField: function() {

        if (!_.isNull(this._currencyField)) {
            return this._currencyField;
        }

        var currencyDef = this.model.fields[this.def.currency_field || 'currency_id'];
        currencyDef.type = 'enum';
        currencyDef.options = app.currency.getCurrenciesSelector(Handlebars.compile('{{symbol}} ({{iso4217}})'));
        currencyDef.enum_width = '100%';
        currencyDef.searchBarThreshold = this.def.searchBarThreshold || 7;

        this._currencyField = app.view.createField({
            def: currencyDef,
            view: this.view,
            viewName: this.action,
            model: this.model
        });

        return this._currencyField;
    },

    setDisabled: function(disable) {
        disable = _.isUndefined(disable) ? true : disable;
        app.view.Field.prototype.setDisabled.call(this, disable);

        this.getCurrencyField().setDisabled(disable);
    },

    setMode: function(name) {
        app.view.Field.prototype.setMode.call(this, name);
        this.getCurrencyField().setMode(name);
    }
})
