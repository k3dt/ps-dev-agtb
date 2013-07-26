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
     * {@inheritDoc}
     *
     * This field doesn't support `showNoData`.
     */
    showNoData: false,

    events: {
        "click .btn": "_showAddressBook"
    },

    fieldTag: 'input.select2',

    tooltips: [], //initialized tooltips

    /**
     * Sets up event handlers for syncing between the model and the recipients field.
     *
     * @see RecipientsField::format() For the acceptable formats for recipients.
     */
    bindDataChange: function() {
        this.model.on("change:" + this.name, function(model, recipients) {
            this._replaceRecipients(recipients);
        }, this);
    },

    /**
     * Remove events from the field value if it is a collection
     */
    unbindData: function() {
        var value = this.model.get(this.name);
        if (value instanceof Backbone.Collection) {
            value.off(null, null, this);
        }

        app.view.Field.prototype.unbindData.call(this);
    },

    /**
     * Render field with select2 widget
     *
     * @private
     */
    _render: function() {
        app.view.Field.prototype._render.call(this);

        var $recipientsField = this.getFieldElement();

        if ($recipientsField.length > 0) {
            $recipientsField.select2({
                allowClear:          true,
                multiple:            true,
                width:               'off',
                containerCssClass:   'select2-choices-pills-close',
                containerCss:        {'width':'100%'},
                minimumInputLength:  1,
                query:               _.bind(function(query) {this.loadOptions(query);}, this),
                createSearchChoice:  _.bind(this.createOption, this),
                formatSelection:     _.bind(this.formatSelection, this),
                formatResult:        _.bind(this.formatResult, this),
                formatSearching:     _.bind(this.formatSearching, this),
                formatInputTooShort: _.bind(this.formatInputTooShort, this)
            });

            if (!!this.def.disabled) {
                $recipientsField.select2('disable');
            }
        }
    },

    /**
     * Fetch additional recipients from the server.
     *
     * @see http://ivaynberg.github.io/select2/#doc-query
     * @param {Object} query Possible attributes can be found in select2's documentation.
     */
    loadOptions: _.debounce(function(query) {
        var data = {
                results: [],
                // only show one page of results
                // if more results are needed, then the address book should be used
                more: false
            },
            options = {},
            callbacks = {};

        // add the search term to the URL params
        options.q = query.term;
        // add the allowed modules to the URL params
        options.module_list = 'Accounts,Contacts,Leads,Prospects,Users';
        // add the necessary fields to the URL params
        options.fields = 'name,email';
        // the first 10 results should be enough
        // if more results are needed, then the address book should be used
        options.max_num = 10;
        // create the callbacks
        callbacks.success = function(result) {
            // add the recipients that were found via the select2 callback
            data.results = result.records;
        };
        callbacks.error = function() {
            // don't add any recipients via the select2 callback
            data.results = [];
        };
        callbacks.complete = function() {
            // execute the select2 callback to add any new recipients
            query.callback(data);
        };
        app.api.search(options, callbacks);
    }, 300),

    /**
     * Create additional select2 options when loadOptions returns no matches for the search term.
     *
     * @see http://ivaynberg.github.io/select2/#documentation
     * @param {String} term
     * @param {Array} data The options in the select2 drop-down after the query callback has been executed.
     * @returns {Object}
     */
    createOption: function(term, data) {
        if (data.length === 0) {
            return {id: term, email: term};
        }
    },

    /**
     * Formats a recipient object for displaying selected recipients.
     *
     * @see http://ivaynberg.github.io/select2/#documentation
     * @param {Object} recipient
     * @return {String}
     */
    formatSelection: function(recipient) {
        return recipient.name ? recipient.name : recipient.email;
    },

    /**
     * Formats a recipient object for displaying items in the recipient options list.
     *
     * @see http://ivaynberg.github.io/select2/#documentation
     * @param {Object} recipient
     * @return {String}
     */
    formatResult: function(recipient) {
        return this.formatSelection(recipient); // do the same as formatSelection by default
    },

    /**
     * Returns the localized message indicating that a search is in progress
     *
     * @see http://ivaynberg.github.io/select2/#documentation
     * @returns {String}
     */
    formatSearching: function() {
        return app.lang.get("LBL_LOADING", this.module);
    },

    /**
     * Suppresses the message indicating the number of characters remaining before a search will trigger
     *
     * @see http://ivaynberg.github.io/select2/#documentation
     * @param term
     * @param min
     * @returns {String}
     */
    formatInputTooShort: function(term, min) {
        return "";
    },

    /**
     * Translates a set of recipients into an array of objects that select2 understands.
     *
     * @param {*} data A Backbone collection, a single Backbone model or standard JavaScript object, or an array of
     *                 Backbone models or standard JavaScript objects.
     * @returns {Array}
     * @see RecipientsField::_translateRecipient() For the acceptable/expected attributes to be found on each recipient.
     */
    format: function(data) {
        var translatedRecipients = [];

        // the lowest common denominator of potential inputs is an array of objects
        // force the parameter to be an array of either objects or Backbone models so that we're always dealing with
        // one data-structure type
        if (data instanceof Backbone.Collection) {
            // get the raw array of models
            data = data.models;
        } else if (data instanceof Backbone.Model || (_.isObject(data) && !_.isArray(data))) {
            // wrap the single model in an array so the code below behaves the same whether it's a model or a collection
            data = [data];
        } else {
            // it's most likely, and hopefully, an array of objects like:
            // [
            //     {email:"foo@bar.com", name:"Foo Bar"},
            //     {email:"foo@bar.com", name:""},
            //     {email:"foo@bar.com", name:""}
            // ]
            // nothing to do but let the rest of the method iterate over the recipients
        }

        if (_.isArray(data)) {
            _.each(data, function(recipient) {
                var translatedRecipient = this._translateRecipient(recipient);

                // only add the recipient if there is an email address
                if (!_.isEmpty(translatedRecipient.email)) {
                    translatedRecipients.push(translatedRecipient);
                }
            }, this);
        }

        return translatedRecipients;
    },

    /**
     * Translates an array of objects into a collection.
     *
     * @param data {Array}
     * @returns {Collection}
     */
    unformat: function(data) {
        return new Backbone.Collection(data);
    },

    /**
     * Synchronize recipient field value with the model and setup tooltips for email pills
     *
     * NOTE: In Select2 v3.4.0, the event names are namespaced (prefixed with "select2-"). So it is expected that the
     * event handlers defined in this method for the Select2 field will break upon upgrading.
     */
    bindDomChange: function() {
        var self = this;
        this.getFieldElement()
            .on("change", function() {
                var value = $(this).select2('data');
                self.model.set(self.name, self.unformat(value), {silent: true});
            })
            .on("change", function(event) {
                self._destroyTooltips();
                self._initializeTooltips();
            }).
            on("selected", _.bind(this._handleEventOnSelected, this));
    },

    /**
     * Event handler for the Select2 "selected" event.
     *
     * NOTE: In Select2 v3.4.0, the event names are namespaced (prefixed with "select2-"). So "selected" event will no
     * longer exist; it will become the "select2-selecting" event.
     *
     * @param event
     * @returns {boolean}
     * @private
     */
    _handleEventOnSelected: function (event) {
        // only allow the user to select an option if it is determined to be a valid email address
        // returning true will select the option; false will prevent the option from being selected
        var isValidChoice = false;

        // since this event is fired twice, we only want to perform validation on the first event
        // event.object is not available on the second event
        if (event.object) {
            // the id and email address will not match when the email address came from the database and
            // we are assuming that email addresses stored in the database have already been validated
            if (event.object.id == event.object.email) {
                // this option must be a new email address that the application does not recognize
                // so validate it
                isValidChoice = this._validateEmailAddress(event.object.email);
            } else {
                // the application should recognize the email address, so no need to validate it again
                // just assume it's a valid choice and we'll deal with the consequences later (server-side)
                isValidChoice = true;
            }
        }

        return isValidChoice;
    },

    /**
     * Destroy all select2 and tooltip plugins
     */
    unbindDom: function() {
        this._destroyTooltips();
        this.getFieldElement().select2('destroy');
        app.view.Field.prototype.unbindDom.call(this);
    },

    /**
     * Adds the new recipients to the existing recipients.
     *
     * @param recipients
     * @see RecipientsField::format() For the acceptable formats for recipients.
     * @private
     */
    _addRecipients: function(recipients) {
        var existingRecipients = this.format(this.model.get(this.name)), // get the existing recipients in array format
            newRecipients      = this.format(recipients), // force the new recipients to array format
            filteredRecipients = [];

        _.each(newRecipients, function(recipient) {
            // only add recipients whose id's are not found among the existing recipients
            if (_.where(existingRecipients, {id: recipient.id}).length === 0) {
                filteredRecipients.push(recipient);
            }
        }, this);

        this.getFieldElement()
            .select2('data', _.union(existingRecipients, filteredRecipients))
            .trigger('change');
    },

    /**
     * Replaces the current recipients with the new recipients
     *
     * @param recipients
     * @see RecipientsField::format() For the acceptable formats for recipients.
     * @private
     */
    _replaceRecipients: function(recipients) {
        var newRecipients = this.format(recipients);

        this.getFieldElement()
            .select2('data', newRecipients)
            .trigger('change');
    },

    /**
     * When in edit mode, the field includes an icon button for opening an address book. Clicking the button will
     * trigger an event to open the address book, which calls this method to do the dirty work. The selected recipients
     * are added to this field upon closing the address book.
     *
     * @private
     */
    _showAddressBook: function() {
        app.drawer.open({
                layout:  "compose-addressbook",
                context: {
                    module: "Emails",
                    mixed:  true
                }
            }, _.bind(this._addRecipients, this));
    },

    /**
     * update ul.select2-choices data attribute which prevents underrun of pills by
     * using a css definition for :before {content:''} set to float right
     *
     * @param content {String}
     */
    setContentBefore: function(content) {
        this.$('.select2-choices').attr('data-content-before', content);
    },

    /**
     * Gets the recipients DOM field
     *
     * @returns {Object} DOM Element
     */
    getFieldElement: function() {
        return this.$(this.fieldTag);
    },

    /**
     * Tooltip should show when hovering over the recipient pill
     * @private
     */
    _initializeTooltips: function() {
        var self = this;
        this.$('.select2-search-choice').each(function() {
            $(this).tooltip({
                container: 'body',
                title: $(this).data('select2Data').email
            });
            self.tooltips.push($(this).data('tooltip'));
        });
    },

    /**
     * Destroy all tooltips
     * @private
     */
    _destroyTooltips: function() {
        _.each(this.tooltips, function(tooltip) {
            tooltip.destroy();
        });
        this.tooltips = [];
    },

    /**
     * Transpose data from a Backbone model into a standard JavaScript object with the data required by the field.
     *
     * @param {Backbone.Model} bean
     * @returns {Object}
     * @private
     */
    _getDataFromBean: function(bean) {
        var model = {
            id:     bean.get("id"),
            module: bean.module || bean.get("module"),
            name:   bean.get("name") || bean.get("full_name"),
            email:  bean.get("email1") || bean.get("email")
        };

        if (_.isArray(model.email)) {
            // grab the primary email address
            var primaryAddress = _.find(model.email, function (emailAddress) {
                return (emailAddress.primary_address == "1");
            });

            if (!_.isUndefined(primaryAddress) && !_.isEmpty(primaryAddress.email_address)) {
                model.email = primaryAddress.email_address;
            }
        }

        if (_.isEmpty(model.email) || !_.isString(model.email)) {
            delete model.email;
        }

        if (_.isEmpty(model.name) || !_.isString(model.name)) {
            delete model.name;
        }

        return model;
    },

    /**
     * Translate a recipient to an object that the field can understand.
     *
     * @param {*} recipient A Backbone model or standard JavaScript object. If it's a standard object, it may be
     *                      structured like:
     *
     *                          {
     *                              id: "abcd",
     *                              module: "Contacts",
     *                              email: "foo@bar.com",
     *                              name: "Foo Bar",
     *                              bean: Backbone.Model
     *                          }
     *
     *                      All attributes are optional. However, if the email attribute is not present, then primary
     *                      email address should exist on the bean. Without an email address that can be resolved, the
     *                      recipient is considered to be invalid. The bean attribute must be a Backbone model and it
     *                      likely will be a Bean. Data found in the bean is considered to be secondary to the first-
     *                      class attributes found on the object. The bean is a mechanism for collecting additional
     *                      information about the recipient that may not have been explicitly set when the recipient
     *                      was passed in.
     * @returns {Object}
     * @private
     */
    _translateRecipient: function(recipient) {
        var translatedRecipient = {},
            bean,
            id,
            module,
            name,
            email;

        if (recipient instanceof Backbone.Model) {
            bean   = this._getDataFromBean(recipient);
            id     = bean.id || bean.email;
            module = bean.module;
            name   = bean.name;
            email  = bean.email;
        } else {
            bean = {};

            // grab values off the bean
            if (recipient.hasOwnProperty("bean") && recipient.bean instanceof Backbone.Model) {
                bean = this._getDataFromBean(recipient.bean);
            }

            // try to grab values directly first, otherwise use the bean
            id     = recipient.id || bean.id || recipient.email || bean.email;
            module = recipient.module || bean.module;
            name   = recipient.name || bean.name;
            email  = recipient.email || bean.email;
        }

        // don't bother with the recipient unless an id is present
        if (!_.isEmpty(id)) {
            translatedRecipient.id = id;

            if (!_.isEmpty(email)) {
                // only set the email if it's actually available
                translatedRecipient.email = email;
            }

            if (!_.isEmpty(module)) {
                // only set the module if it's actually available
                translatedRecipient.module = module;
            }

            if (!_.isEmpty(name)) {
                // only set the name if it's actually available
                translatedRecipient.name = name;
            }
        }

        return translatedRecipient;
    },

    /**
     * Validates an email address on the server.
     *
     * @param {String} emailAddress
     * @returns {boolean}
     * @private
     */
    _validateEmailAddress: function(emailAddress) {
        var isValid   = false,
            callbacks = {},
            options   = {
                // execute the api call synchronously so that the method doesn't return before the response is known
                async: false
            },
            url       = app.api.buildURL("Mail", "address/validate");

        callbacks.success = function(result) {
            isValid = result[emailAddress];
        };
        callbacks.error = function() {
            isValid = false;
        };
        app.api.call("create", url, [emailAddress], callbacks, options);

        return isValid;
    }
})
