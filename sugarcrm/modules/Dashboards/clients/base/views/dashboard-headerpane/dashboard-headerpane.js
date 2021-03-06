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
 * @class View.Views.Dashboards.DashboardHeaderpaneView
 * @alias SUGAR.App.view.views.DashboardsDashboardHeaderpaneView
 * @extends View.Views.Base.HeaderpaneView
 */
({
    extendsFrom: 'HeaderpaneView',

    buttons: null,

    editableFields: null,

    className: 'preview-headerbar',

    events: {
        'click [name=edit_button]': 'editClicked',
        'click [name=save_button]': 'saveClicked',
        'click [name=cancel_button]': 'cancelClicked',
        'click [name=create_cancel_button]': 'createCancelClicked',
        'click [name=duplicate_button]': 'duplicateClicked',
        'click [name=delete_button]': 'deleteClicked',
        'click [name=add_button]': 'addClicked',
        'click [name=collapse_button]': 'collapseClicked',
        'click [name=expand_button]': 'expandClicked',
        'click [name=edit_overview_tab_button]': 'editOverviewTabClicked',
        'click [name=edit_module_tabs_button]': 'editModuleTabsClicked',
        'click [name=add_dashlet_button]': 'addDashletClicked',
    },

    initialize: function(options) {
        if (options.context.parent) {
            options.meta = app.metadata.getView(options.context.parent.get('module'), options.type, options.loadModule);
            options.template = app.template.getView(options.type);
        }
        this._super('initialize', [options]);
        if (this.model.isNew()) {
            this._setNewModelMeta();
        }
        this.context.set('dataView', '');
        this.model.on('change change:layout change:metadata', function() {
            if (this.inlineEditMode) {
                this.changed = true;
            }
        }, this);
        this.model.on('error:validation', this.handleValidationError, this);

        if (this.context.get('create')) {
            this.changed = true;
            this.action = 'edit';
            this.inlineEditMode = true;
        } else {
            this.action = 'detail';
        }
        this.buttons = {};

        this._bindEvents();
    },

    /**
     * Binds the events that are necessary for this view.
     *
     * @protected
     */
    _bindEvents: function() {
        this.context.on('record:set:state', this.setRecordState, this);
        this.context.on('tabbed-dashboard:switch-tab', this.switchTab, this);
    },

    /**
     * Handles the logic done when the state changes in the record.
     * This is the callback for the `record:set:state` event.
     *
     * @param {string} state The state that the record is set to.
     */
    setRecordState: function(state) {
        this.model.trigger('setMode', state);
        this.setButtonStates(state);
        this.inlineEditMode = state === 'edit';
        this.toggleEdit(this.inlineEditMode);
    },

    /**
     * Event handler for button 'Edit Overview Tab'.
     *
     * @param {Event} evt Triggered mouse event
     */
    editOverviewTabClicked: function(evt) {
        // switch to overview tab
        if (this.context.get('activeTab') !== 0) {
            this.context.trigger('tabbed-dashboard:switch-tab', 0);
        }
        this.editClicked(evt);
    },

    /**
     * Event handler for button 'Edit Module Tabs'.
     *
     * @param {Event} evt Triggered mouse event
     */
    editModuleTabsClicked: function(evt) {
        app.drawer.open({
            layout: 'config-drawer',
            context: {
                module: 'ConsoleConfiguration',
            }
        });
    },

    editClicked: function(evt) {
        this.previousModelState = app.utils.deepCopy(this.model.attributes);
        this.inlineEditMode = true;
        this.setButtonStates('edit');
        this.toggleEdit(true);
        this.model.trigger('setMode', 'edit');
    },

    /**
     * Get the dashboard name field and toggle states
     * @param {boolean} isEdit
     */
    toggleNameField: function(isEdit) {
        var field = this.getField('name');
        this.toggleField(field, !!isEdit);
    },

    /**
     * Run save function and switch to view mode
     */
    saveHandle: function() {
        var changes = this.model.changedAttributes(this.model.getSynced());
        if (changes && changes.name) {
            this.layout.handleSave();
        }

        this.setButtonStates('view');
        this.toggleEdit(false);
        this.model.trigger('setMode', 'view');
    },

    cancelClicked: function(evt) {
        this.changed = false;
        this.model.unset('updated');
        this.clearValidationErrors();
        this.setButtonStates('view');
        this.handleCancel();
        this.model.trigger('setMode', 'view');
        this.toggleNameField();
    },

    /**
     * Create a duplicate of current dashboard and assign it to the user,
     * so that the user can make own modification on top of existing dashboards
     *
     * Some attributes are changed during the duplication:
     *  id, name, assigned_user_id, assigned_user_name, team, default_dashboard, my_favorite
     *
     * @param {Event} evt Triggered mouse event
     */
    duplicateClicked: function(evt) {
        var newModel = app.data.createBean('Dashboards');
        newModel.copy(this.model);

        var oldName = app.lang.get(newModel.get('name'), newModel.get('dashboard_module'));
        // FIXME TY-1463: Will fix the hard coding of 'Dashboards'
        var newName = app.lang.get('LBL_COPY_OF', 'Dashboards', {name: oldName});

        var newAttributes = {
            name: newName,
            my_favorite: true
        };

        // Using void 0 to follow the convention in backbone.js
        var clearAttributes = {
            id: void 0,
            assigned_user_id: void 0,
            assigned_user_name: void 0,
            team_name: void 0,
            default_dashboard: void 0
        };

        newModel.unset(clearAttributes, {silent: true});

        var options = {};
        options.success = _.bind(this._successWhileSave, this, 'add', newModel);
        options.error = this._errorWhileSave;

        newModel.save(newAttributes, options);
    },

    /**
     * Compare with last fetched data and return true if model contains changes
     *
     * See {@link app.plugins.view.editable}. Ignore the favorite icon for
     * checking for unsaved changes.
     *
     * @return {boolean} true if current model contains unsaved changes
     */
    hasUnsavedChanges: function() {
        if (this.model.get('updated')) {
            return true;
        }

        if (this.model.isNew()) {
            return this.model.hasChanged();
        }

        var changes = this.model.changedAttributes(this.model.getSynced());

        // If there are no changes, don't warn.
        if (_.isEmpty(changes)) {
            return false;
        }

        // If the only change is to my_favorite, don't warn.
        var nonFavoriteChange = _.find(changes, function(obj, key) {
            return key !== 'my_favorite';
        });

        return !_.isUndefined(nonFavoriteChange);
    },

    /**
     * @override
     *
     * The save function is handled by {@link View.Layouts.Dashboards.DashboardLayout#handleSave}.
     */
    saveClicked: function(evt) {
        this.toggleNameField();
    },

    createCancelClicked: function(evt) {
        if (this.context.parent) {
            this.layout.navigateLayout('list');
        } else {
            app.navigate(this.context);
        }
    },

    deleteClicked: function(evt) {
        this.handleDelete();
    },

    addClicked: function(evt) {
        if (this.context.parent) {
            this.layout.navigateLayout('create');
        } else {
            var route = app.router.buildRoute(this.module, null, 'create');
            app.router.navigate(route, {trigger: true});
        }
    },

    collapseClicked: function(evt) {
        this.context.trigger('dashboard:collapse:fire', true);
    },

    expandClicked: function(evt) {
        this.context.trigger('dashboard:collapse:fire', false);
    },

    /**
     * Handle event: 'tabbed-dashboard:switch-tab'.
     *
     * @param {number} tabIndex New tab's index
     */
    switchTab: function(tabIndex) {
        this.context.set('activeTab', tabIndex);
        this._enableEditButton(this._isDashboard());
        this._enableAddDashletButton(this._isDashboard());
    },

    /**
     * Check if this is a tabbed dashboard and active tab is a dashboard.
     *
     * @return {bool} True if this is not a tabbed dashboard
     * or active tab is a dashboard, false otherwise
     * @private
     */
    _isDashboard: function() {
        var tabs = this.context.get('tabs');
        if (!tabs) {
            return true;
        }
        var tabIndex = this.context.get('activeTab') || 0;
        return tabs[tabIndex] &&
            ((tabs[tabIndex].components && tabs[tabIndex].components[0].rows) || tabs[tabIndex].dashlets || false);
    },

    /**
     * Show/hide add dashlet button.
     *
     * @param {bool} state True to show, false to hide
     * @private
     */
    _enableAddDashletButton: function(state) {
        var dropdown = _.find(this.buttons, function(button) {
            return button.type === 'actiondropdown';
        });

        if (dropdown) {
            var button =  _.find(dropdown.fields, function(field) {
                return field.name === 'add_dashlet_button';
            });
            if (button) {
                button.setDisabled(!state);
                button.isHidden = !state;
                dropdown._orderButtons();
                dropdown.render();
            }
        }
    },

    /**
     * Show/hide edit button.
     *
     * @param {bool} state True to show, false to hide
     * @private
     */
    _enableEditButton: function(state) {
        var dropdown = _.find(this.buttons, function(button) {
            return button.type === 'actiondropdown';
        });
        if (dropdown) {
            var editButton =  _.find(dropdown.fields, function(field) {
                return field.name === 'edit_button';
            });
            if (editButton) {
                editButton.setDisabled(!state);
                editButton.isHidden = !state;
                dropdown._orderButtons();
                dropdown.render();
            }
        }
    },

    /**
     * Defer rendering until after the data loads. See #_renderHeader for more info.
     *
     * We defer rendering until after data load because by default, the fields
     * will render once on initialization and then will re-render once the data
     * is loaded. This means that while the model is being fetched, it is still
     * possible to interact with the fields, even if the field is in the wrong
     * state (such as favorite/unfavorite). Additionally, this causes a
     * distracting and annoying flickering effect.
     *
     * To avoid both the flickering effect and prevent users from accidentally
     * setting field values during data fetch, we defer rendering until after
     * the data is loaded.
     *
     * @override
     * @private
     */
    _render: function() {
        // When creating a dashboard, there is no model to load, so there is
        // no need to defer rendering.
        if (this.context.get('create')) {
            this._renderHeader();
        } else {
            this.model.once('sync', this._renderHeader, this);
        }
        return this;
    },

    /**
     * Render the view manually.
     *
     * This function handles the responsibility typically handled in _render,
     * but unlike `_render`, it is not called automatically.
     *
     * See #_render for more information.
     */
    _renderHeader: function() {
        app.view.View.prototype._render.call(this);

        this._setButtons();
        this.setButtonStates(this.context.get('create') ? 'create' : 'view');
        this.setEditableFields();
        this._enableEditButton(false);
        if (!this._isDashboard()) {
            this._enableAddDashletButton(false);
        }
    },

    handleCancel: function() {
        this.inlineEditMode = false;
        if (!_.isEmpty(this.previousModelState)) {
            this.model.set(this.previousModelState);
        }
        this.toggleEdit(false);
    },

    /**
     * This method handles the deletion of a dashboard. It alerts the user
     * before deleting the dashboard, and if the user chooses to delete the
     * dashboard, it handles the deletion logic as well.
     *
     * @override
     */
    handleDelete: function() {
        var modelName = app.lang.get(this.model.get('name'), this.model.get('dashboard_module'));
        app.alert.show('delete_confirmation', {
            level: 'confirmation',
            // FIXME TY-1463: Will fix the hard coding of 'Dashboards'.
            messages: app.lang.get('LBL_DELETE_DASHBOARD_CONFIRM', 'Dashboards', {name: modelName}),
            onConfirm: _.bind(function() {
                var message = app.lang.get('LBL_DELETE_DASHBOARD_SUCCESS', this.module, {
                    name: modelName
                });
                this.model.destroy({
                    success: _.bind(this._successWhileSave, this, 'delete', this.model),
                    error: this._errorWhileSave,
                    //Show alerts for this request
                    showAlerts: {
                        'process': true,
                        'success': {
                            messages: message
                        }
                    }
                });
            }, this)
        });
    },

    /**
     * Handler for saving success, it navigates to the layout or
     * the page based on the context
     *
     * @param {string} change The change that's made to the model
     *  This is either 'delete' or 'add'
     * @param {Data.Bean} model The model that's changed
     * @private
     */
    _successWhileSave: function(change, model) {
        if (this.disposed) {
            return;
        }
        // If we don't have a this.context.parent, that means we are
        // navigating to a Home Dashboard, otherwise it's a RHS Dashboard
        if (!this.context || !this.context.parent) {
            var id = change === 'add' ? model.get('id') : null;
            var route = app.router.buildRoute(this.module, id);
            app.router.navigate(route, {trigger: true});
            return;
        }
        var contextBro = this.context.parent && this.context.parent.get('layout') === 'multi-line' ?
            this.context.parent : this.context.parent.getChildContext({module: 'Home'});
        switch (change) {
            case 'delete':
                contextBro.get('collection').remove(model);
                this.layout.navigateLayout('list');
                break;
            case 'add':
                contextBro.get('collection').add(model);
                this.layout.navigateLayout(model.get('id'));
                break;
        }
    },

    /**
     * Error handler for Dashboard saving
     *
     * @private
     */
    _errorWhileSave: function() {
        app.alert.show('error_while_save', {
            level: 'error',
            title: app.lang.get('ERR_INTERNAL_ERR_MSG'),
            messages: ['ERR_HTTP_500_TEXT_LINE1', 'ERR_HTTP_500_TEXT_LINE2']
        });
    },

    bindDataChange: function() {
        //empty out because dashboard header does not need to switch the button sets while model is changed
    },

    toggleEdit: function(isEdit) {
        this.editableFields = this.editableFields.filter(function(item) {
            return item.name !== 'name';
        });

        this.toggleFields(this.editableFields, isEdit);
    },

    /**
     * Initialize metadata on new dashboard
     * @private
     */
    _setNewModelMeta: function() {
        var metadata = {
            dashlets: []
        };
        this.model.set('metadata', metadata, {silent: true});
        this.model.trigger('change:metadata');
        this.model.changed = {};
    },
})
