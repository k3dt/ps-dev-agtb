/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * Reply or reply all action.
 *
 * This allows an user to "reply" or "reply all" to an existing email.
 *
 * @class View.Fields.Base.ReplyActionField
 * @alias SUGAR.App.view.fields.BaseReplyActionField
 * @extends View.Fields.Base.EmailactionField
 */
({
    extendsFrom: 'EmailactionField',

    plugins: ['EmailClientLaunch'],

    /**
     * ID to add to the div wrapper around reply content for later identifying
     * the portion of the email body which is the reply content. ie., when
     * inserting templates into an email but maintaining reply content.
     *
     * @private
     */
    REPLY_CONTENT_ID: 'replycontent',

    /**
     * Template for reply header.
     *
     * @private
     */
    _tplHeaderHtml: null,

    /**
     * @inheritdoc
     *
     * Sets up the reply content to be used when the user clicks on the Reply or
     * Reply All button. Also listens for changes to the model to update the
     * reply content. The reply content is built ahead of the button click
     * to support the option of doing a mailto link which needs to be built and
     * set in the DOM at render time.
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this._tplHeaderHtml = app.template.getField(this.type, 'reply-header-html', this.module);

        //Use field template from emailaction
        this.type = 'emailaction';

        this._setReplyContent();
        this.model.on('change', this._setReplyContent, this);
    },

    /**
     * Sets up the email options for the EmailClientLaunch plugin to use -
     * passing to the email compose drawer or building up the mailto link.
     *
     * @protected
     */
    _setReplyContent: function() {
        var replyRecipients = this._getReplyRecipients(this.def.reply_all);
        var subject = this._getReplySubject(this.model.get('name'));
        var replyHeader = this._tplHeaderHtml(this._getReplyHeaderParams());
        var replyBody = this._getReplyBody();
        var descriptionHtml = '<div></div><div id="' + this.REPLY_CONTENT_ID + '">' +
            replyHeader + replyBody + '</div>';

        this.addEmailOptions({
            to: replyRecipients.to,
            cc: replyRecipients.cc,
            name: subject,
            description_html: descriptionHtml,
            parent_type: this.model.get('parent_type'),
            parent_id: this.model.get('parent_id'),
            parent_name: this.model.get('parent_name'),
            _signatureLocation: 'above',
            _isReply: true
        });
    },

    /**
     * Build the reply recipients based on the original email's from, to, and cc
     *
     * @param {boolean} all Whether this is reply to all (true) or just a standard
     *   reply (false).
     * @return {Object} To and Cc values for the reply email.
     * @return {Array} return.to The to values for the reply email.
     * @return {Array} return.cc The cc values for the reply email.
     * @protected
     */
    _getReplyRecipients: function(all) {
        var replyTo = [];
        var replyCc = [];
        var originalSender = this.model.get('from');
        var originalTo = this.model.get('to');
        var originalCc = this.model.get('cc');

        var mapRecipients = function(recipients) {
            return _.map(recipients, function(recipient) {
                if (recipient.module === 'EmailAddresses') {
                    return {email: recipient.get('email_address_used')};
                } else {
                    return {bean: recipient};
                }
            });
        };

        if (originalSender && originalSender.models) {
            replyTo = _.union(replyTo, mapRecipients(originalSender.models));
        }

        if (all && originalTo && originalTo.models) {
            replyTo = _.union(replyTo, mapRecipients(originalTo.models));
        }

        if (all && originalCc && originalCc.models) {
            replyCc = _.union(replyCc, mapRecipients(originalCc.models));
        }

        return {
            to: replyTo,
            cc: replyCc
        };
    },

    /**
     * Given the original subject, generate a reply subject.
     *
     * @param {string} subject
     * @protected
     */
    _getReplySubject: function(subject) {
        var pattern = /^((?:re|fwd): *)*/i;
        subject = subject || '';
        return 'Re: ' + subject.replace(pattern, '');
    },

    /**
     * Get the params required to run the reply header template.
     *
     * @return {Object}
     * @protected
     */
    _getReplyHeaderParams: function() {
        return {
            from: this._formatEmailList(this.model.get('from')),
            date: this.model.get('date_sent'),
            to: this._formatEmailList(this.model.get('to')),
            cc: this._formatEmailList(this.model.get('cc')),
            name: this.model.get('name')
        };
    },

    /**
     * Given a list of people, format a text only list for use in a reply header
     *
     * @param {Collection} collection A list of models
     * @protected
     */
    _formatEmailList: function(collection) {
        var result = '';
        var models = (collection instanceof Backbone.Collection) ?
            collection.models :
            [];

        _.each(models, function(model) {
            var name = model.get('name');
            var email = model.get('email_address_used');

            if (result) {
                result += ', ';
            }

            if (name) {
                result += name + ' <' + email + '>';
            } else {
                result += email;
            }
        }, this);

        return result;
    },

    /**
     * Retrieve the reply body.
     *
     * Ensure the result is a defined string and strip any signature wrapper
     * tags to ensure it doesn't get stripped if we insert a signature above
     * the reply content. Also strip any reply content class if this is a
     * reply to a previous reply.
     *
     * @return {string} The reply body
     * @private
     */
    _getReplyBody: function() {
        var body = (this.model.get('description_html') || '');
        body = body.replace('<div class="signature">', '<div>');
        return body.replace('<div id="' + this.REPLY_CONTENT_ID + '">', '<div>');
    }
})