/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (''License'') which can be viewed at
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
 *  (i) the ''Powered by SugarCRM'' logo and
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
    plugins: ['GridBuilder', 'error-decoration'],
    /**
     * An abstract WizardPageView.  Wizard pages should extend this and provide
     * field metadata, custom logic, etc.  This view is detached from Wizard
     * layout when it is not the current page.  When it becomes the current
     * page it is appended to Wizard layout and render is called.
     *
     * If you want to use the default Wizard template, you'll need to load it in initialize.
     * For example,
     *
     * <pre>
     * initialize: function(options){
     *   //Load the default wizard page template, if you want to.
     *   options.template = app.template.getView("wizard-page");
     *   app.view.invokeParent(this, {type: 'view', name: 'wizard-page', method: 'initialize', args:[options]});
     * },
     * </pre>
     *
     * @class View.Views.WizardPageView
     * @alias SUGAR.App.view.views.WizardPageView
     */
    events: {
        'click [name=previous_button]:not(.disabled)': 'previous',
        'click [name=next_button]:not(.disabled)': 'next'
    },
    /**
     * Current progress through wizard, updated automatically on each render.
     */
    progress: null,
    /**
     * Flags if all required fields have at least one character or not. This is
     * used to determine whether we enable or disable the wizard's next button.
     * @type {Boolean}
     */
    areAllRequiredFieldsNonEmpty: false,
    /**
     * Initialize the wizard controller and load header and footer partials
     * @param  {options} options the options
     */
    initialize: function(options){
        this.fieldsToValidate = this._fieldsToValidate(options.meta);
        Handlebars.registerPartial("wizard-page.header", app.template.get("wizard-page.header"));
        Handlebars.registerPartial("wizard-page.footer", app.template.get("wizard-page.footer"));
        app.view.View.prototype.initialize.call(this, options);
    },
    /**
     * Additionally update current progress and button status during a render.
     *
     * @override
     * @private
     */
    _render: function(){
        this._buildGridsFromPanelsMetadata(this.meta.panels);
        this.progress = this.layout.getProgress();
        this.percentComplete = this._getPercentageComplete();
        this.wizardCompleted = (this.progress.page === this.progress.lastPage)?true:false;
        app.view.View.prototype._render.call(this);
        this.checkIfPageComplete();
    },
    /**
     * We have to check if required fields are pre-filled once we've sync'd. For example,
     * user might have valid required field values (in which case we enable next button).
     */
    bindDataChange: function() {
        var self = this;
        if (this.model) {
            this.listenTo(this.model, "sync", function() {
                self.checkIfPageComplete();
            });
            _.each(this.fieldsToValidate, function(field) {
               if (field && field.required) {
                  self.listenTo(self.model, 'change:'+field.name, function() {
                      self.checkIfPageComplete();
                  });
               }
            });
        }
    },
    /**
     * Used to build our multi-column grid (user wizard is 2 col panel).
     * @param  {Object} panels the meta.panels
     * @protected
     */
    _buildGridsFromPanelsMetadata: function(panels) {
        _.each(panels, function(panel) {
            if (_.isFunction(this.getGridBuilder)) {
                var options = {
                        fields:      panel.fields,
                        columns:     panel.columns,
                        labels:      panel.labels,
                        labelsOnTop: panel.labelsOnTop
                    },
                    gridResults = this.getGridBuilder(options).build();
                panel.grid   = gridResults.grid;
            }
        }, this);
    },
    /**
     * Gets the percentage of pages complete. We consider being on a page as counting towards
     * completed pages (as this seems to be the norm) e.g. arriving at 1 of 3 results in 33%
     * @protected
     * @return {Number} Percentage complete as int
     */
    _getPercentageComplete: function() {
        return Math.floor(this.progress.page / this.progress.lastPage * 100);
    },
    /**
     * Called after render to update status of next/previous buttons.
     */
    updateButtons: function(){
        var prevBtn = this.getField("previous_button");
        if (prevBtn) {
            if (this.progress && this.progress.page > 1) {
                prevBtn.show();
            } else {
                prevBtn.hide();
            }
        }
        var nextBtn = this.getField("next_button");
        if (nextBtn) {
            nextBtn.setDisabled(!this.isPageComplete());
        }
    },
    /**
     * Called after initialization of the wizard page but just before it gets
     * added as a component to the Wizard layout.  Allows implementers to
     * control when a wizard page is included. Default implementation hides
     * page if it will not render because of ACL checks.
     *
     * @returns {boolean} TRUE if this page should be included in wizard
     */
    showPage: function(){
        return app.acl.hasAccessToModel(this.action, this.model);
    },
    /**
     * We can advance the page once we know it is complete. Wizard page's
     * should override this function to provide custom validation logic.
     *
     * @returns {boolean} TRUE if this page is complete
     * @override
     */
    isPageComplete: function(){
        return true;
    },
    /**
     * Listen to changes on required fields. If all required fields contain
     * at least one character, we enable the next button. Implementers of
     * wizard pages may override this method to customize if desired, although
     * you may be able to just override `requiredTypesToPrevalidate`.
     * @see SUGAR.App.view.views.UserWizardPageView
     * @param {Object} evt the event
     */
    checkIfPageComplete: function(evt) {
        var self = this;
        this.areAllRequiredFieldsNonEmpty = true;
        _.each(this.fields, function(field) {
            if (!field.def.required) return;
            var value = field.$(field.fieldTag + ".required").val();
            var invalid = app.validation.requiredValidator(field.def, field.name, field.model, value);
            if (invalid) {
                self.areAllRequiredFieldsNonEmpty = false;
            }
        });
        this.updateButtons();
    },
    /**
     * Only validate fields pertinent to wizard page
     * @param  {Object} meta The meta
     * @return {Object} fields The fields to validate on
     * @private
     */
    _fieldsToValidate: function(meta) {
        meta = meta || {};
        var fields = {};
        _.each(_.flatten(_.pluck(meta.panels, "fields")), function(field) {
            fields[field.name] = field;
        });
        return fields;
    },
    /**
     * Next button pressed
     */
    next: function() {
        var self = this;
        if (this.progress.page !== this.progress.lastPage) {
            this.beforeNext(function(success) {
                if (success) {
                    self.progress = self.layout.nextPage();
                } else {
                    //If no validation error and this happens throw up a generic error
                    app.logger.debug("There was an unknown issue after calling beforeNext from wizard");
                    app.alert.show('server-error', {
                        level: 'error',
                        messages: 'ERR_AJAX_LOAD_FAILURE',
                        autoClose: false
                    });
                }
            });
        } else {
            this.beforeFinish(function(success) {
                if (success) {
                    self.finish();
                } else {
                    app.logger.debug("There was an unknown issue after calling beforeFinish from wizard");
                    app.alert.show('server-error', {
                        level: 'error',
                        messages: 'ERR_AJAX_LOAD_FAILURE',
                        autoClose: false
                    });
                }
            });
        }
    },
    /**
     * Do any actions like http requests, etc., before allowing user to proceed to next
     * page. Implementers should override this.
     * @param {Function} callback The callback to call once actions are completed
     * @returns {Boolean} Whether action was performed successfully or not
     */
    beforeNext: function(callback) {
        app.logger.debug("wizard's beforeNext called directly. Derived controller's should have overridden this!");
        callback(true);
    },
    /**
     * Previous button pressed
     */
    previous: function(){
        this.progress = this.layout.previousPage();
    },
    /**
     * Next button pressed and this is the last page. We need to PUT /me to indicate that the
     * "instance is configured". Calls finished on WizardLayout on complete.
     */
    finish: function(){
        this.layout.finished();
    }

})
