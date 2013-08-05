({
    inlineEditMode: false,
    createMode: false,
    plugins: ['SugarLogic', 'ellipsis_inline', 'error-decoration', 'GridBuilder', 'editable', 'tooltip'],
    enableHeaderButtons: true,
    enableHeaderPane: true,
    events: {
        'click .record-edit-link-wrapper': 'handleEdit',
        'click a[name=cancel_button]': 'cancelClicked',
        'click .more': 'toggleMoreLess',
        'click .less': 'toggleMoreLess'
    },
    // button fields defined in view definition
    buttons: null,

    // "Show More" state per module
    MORE_LESS_KEY: "more_less", //gets namespaced in initialize function
    MORE_LESS: {
        MORE: 'more',
        LESS: 'less'
    },

    // button states
    STATE: {
        EDIT: 'edit',
        VIEW: 'view'
    },

    // current button states
    currentState: null,

    // fields that should not be editable
    noEditFields: null,

    initialize: function (options) {
        _.bindAll(this);
        options.meta = _.extend({}, app.metadata.getView(null, 'record'), options.meta);
        app.view.View.prototype.initialize.call(this, options);
        this.buttons = {};
        this.createMode = this.context.get("create") ? true : false;

        // Even in createMode we want it to start in detail so that we, later, respect
        // this.editableFields (the list after pruning out readonly fields, etc.)
        this.action = 'detail';

        this.context.on("change:record_label", this.setLabel, this);
        this.context.set("viewed", true);
        this.model.on("duplicate:before", this.setupDuplicateFields, this);
        this.on("editable:keydown", this.handleKeyDown, this);

        this.delegateButtonEvents();

        if (this.createMode) {
            this.model.isNotEmpty = true;
        }

        this.noEditFields = [];
        // properly namespace SHOW_MORE_KEY key
        this.MORE_LESS_KEY = app.user.lastState.key(this.MORE_LESS_KEY, this);
    },

    /**
     * Compare with last fetched data and return true if model contains changes
     *
     * @return true if current model contains unsaved changes
     * @link {app.plugins.view.editable}
     */
    hasUnsavedChanges: function() {
        var changedAttributes = this.model.changedAttributes(this.model.getSyncedAttributes());

        if (_.isEmpty(changedAttributes)) {
            return false;
        }
        // if model contains changed attributes,
        // check whether those are among the editable fields or not
        var formFields = _.compact(_.pluck(this.editableFields, 'name')),
            unsavedFields = _.intersection(_.keys(changedAttributes), formFields);

        return !_.isEmpty(unsavedFields);
    },

    /**
     * Called when current record is being duplicated to allow customization of fields
     * that will be copied into new record.
     *
     * Override to setup the fields on this bean prior to being displayed in Create dialog
     *
     * @param {Object} prefill Bean that will be used for new record
     */
    setupDuplicateFields: function (prefill) {

    },

    setLabel: function (context, value) {
        this.$(".record-label[data-name=" + value.field + "]").text(value.label);
    },

    /**
     * Called each time a validation pass is completed on the model
     * @param {boolean} isValid TRUE if model is valid
     */
    validationComplete: function(isValid){
        if (isValid) {
            this.setButtonStates(this.STATE.VIEW);
            this.handleSave();
        }
    },

    delegateButtonEvents: function () {
        this.context.on('button:edit_button:click', this.editClicked, this);
        this.context.on('button:save_button:click', this.saveClicked, this);
        this.context.on('button:delete_button:click', this.deleteClicked, this);
        this.context.on('button:duplicate_button:click', this.duplicateClicked, this);
        this.context.on('button:find_duplicates_button:click', this.findDuplicatesClicked, this);
    },

    _render: function () {
        this._buildGridsFromPanelsMetadata(this.meta.panels);

        app.view.View.prototype._render.call(this);

        var record_label = this.context.get("record_label");
        if(record_label) {
            this.setLabel(this.context, record_label);
        }

        // Field labels in headerpane should be hidden on view but displayed in edit and create
        _.each(this.fields, function (field) {
            var toggleLabel = _.bind(function () {
                this.toggleLabelByField(field);
            }, this);

            field.off('render', toggleLabel);
            if (field.$el.closest('.headerpane').length > 0) {
                field.on('render', toggleLabel);
            }
        }, this);

        this.toggleHeaderLabels(this.createMode);
        this.initButtons();
        this.setButtonStates(this.STATE.VIEW);
        this.setEditableFields();

        if (this.createMode) {
            // RecordView starts with action as detail; once this.editableFields has been set (e.g.
            // readonly's pruned out), we can call toggleFields - so only fields that should be are editable
            this.toggleFields(this.editableFields, true);
        }
        // Restore state of 'Show More' panel by toggling it if 'Show Less' needs to be shown
        if(app.user.lastState.get(this.MORE_LESS_KEY) === this.MORE_LESS.LESS){
            this.toggleMoreLess();
        }

    },

    setEditableFields: function () {
        delete this.editableFields;
        this.editableFields = [];

        var previousField, firstField;
        _.each(this.fields, function (field, index) {
            //Exclude read only fields
            if (field.def.readonly || _.indexOf(this.noEditFields, field.def.name) >= 0 || field.parent || (field.name && this.buttons[field.name])) {
                return;
            }
            if (previousField) {
                previousField.nextField = field;
                field.prevField = previousField;
            } else {
                firstField = field;
            }
            previousField = field;
            this.editableFields.push(field);
        }, this);
        if (previousField) {
            previousField.nextField = firstField;
            firstField.prevField = previousField;
        }
    },
    initButtons: function () {
        if (this.options.meta && this.options.meta.buttons) {
            _.each(this.options.meta.buttons, function (button) {
                this.registerFieldAsButton(button.name);
                if (button.buttons) {
                    var dropdownButton = this.getField(button.name);
                    if(!dropdownButton) {
                        return;
                    }
                    _.each(dropdownButton.fields, function (ddButton) {
                        this.buttons[ddButton.name] = ddButton;
                    }, this);
                }
            }, this);
        }
    },
    showPreviousNextBtnGroup: function () {
        var listCollection = this.context.get('listCollection') || new Backbone.Collection();
        var recordIndex = listCollection.indexOf(listCollection.get(this.model.id));
        if (listCollection && listCollection.models && listCollection.models.length <= 1) {
            this.showPrevNextBtnGroup = false;
        } else {
            this.showPrevNextBtnGroup = true;
        }
        if (this.collection) {
            this.collection.previous = listCollection.models[recordIndex - 1] ? listCollection.models[recordIndex - 1] : undefined;
            this.collection.next = listCollection.models[recordIndex + 1] ? listCollection.models[recordIndex + 1] : undefined;
        }
    },

    registerFieldAsButton: function (buttonName) {
        var button = this.getField(buttonName);
        if (button) {
            this.buttons[buttonName] = button;
        }
    },

    _renderHtml: function () {
        this.showPreviousNextBtnGroup();
        app.view.View.prototype._renderHtml.call(this);
    },

    toggleMoreLess: function () {
        this.$(".less").toggleClass("hide");
        this.$(".more").toggleClass("hide");
        this.$(".panel_hidden").toggleClass("hide");
        var moreLess = this.$(".less").is(".hide") ? this.MORE_LESS.MORE : this.MORE_LESS.LESS;
        app.user.lastState.set(this.MORE_LESS_KEY, moreLess);
    },

    bindDataChange: function () {
        this.model.on("change", function (fieldType) {
            if (this.inlineEditMode) {
                this.setButtonStates(this.STATE.EDIT);
            }
            if (this.model.isNotEmpty !== true && fieldType !== 'image') {
                this.model.isNotEmpty = true;
                if (!this.disposed) {
                    this.render();
                }
            }
        }, this);
    },

    duplicateClicked: function () {
        var self = this,
            prefill = app.data.createBean(this.model.module);

        prefill.copy(this.model);
        self.model.trigger("duplicate:before", prefill);
        prefill.unset("id");
        app.drawer.open({
            layout: 'create-actions',
            context: {
                create: true,
                model: prefill
            }
        }, function (context, newModel) {
            if (newModel && newModel.id) {
                app.router.navigate("#" + self.model.module + "/" + newModel.id, {trigger: true});
            }
        });
    },

    findDuplicatesClicked: function () {
        var model = app.data.createBean(this.model.module);

        model.copy(this.model);
        model.set('id', this.model.id);
        app.drawer.open({
            layout: 'find-duplicates',
            context: {
                dupeCheckModel: model,
                dupelisttype: 'dupecheck-list-multiselect'
            }
        });
    },

    editClicked: function () {
        this.setButtonStates(this.STATE.EDIT);
        this.toggleEdit(true);
    },

    saveClicked: function () {
        this.clearValidationErrors();
        this.model.doValidate(this.getFields(this.module), _.bind(this.validationComplete, this));
    },

    cancelClicked: function () {
        this.handleCancel();
        this.setButtonStates(this.STATE.VIEW);
        this.clearValidationErrors(this.editableFields);
    },

    deleteClicked: function () {
        this.handleDelete();
    },

    /**
     * Render fields into either edit or view mode.
     * @param isEdit
     */
    toggleEdit: function (isEdit) {
        this.$('.record-edit-link-wrapper').toggle(!isEdit);
        this.$('.headerpane .record-label').toggle(isEdit);
        this.toggleFields(this.editableFields, isEdit);
        this.toggleViewButtons(isEdit);
    },

    /**
     * Handler for intent to edit. This handler is called both as a callback from click events, and also
     * triggered as part of tab focus event.
     * @param e {Event} jQuery Event object (should be from click)
     * @param cell {jQuery Node} cell of the target node to edit
     */
    handleEdit: function (e, cell) {
        var target,
            cellData,
            field;

        if (e) { // If result of click event, extract target and cell.
            target = this.$(e.target);
            cell = target.parents(".record-cell");
        }

        cellData = cell.data();
        field = this.getField(cellData.name);

        // Set Editing mode to on.
        this.inlineEditMode = true;

        this.setButtonStates(this.STATE.EDIT);

        this.toggleField(field);
    },

    /**
     * Hide/show all field labels in headerpane
     * @param isEdit
     */
    toggleHeaderLabels: function (isEdit) {
        this.$('.headerpane .record-label').toggle(isEdit);
        this.toggleViewButtons(isEdit);
    },

    /**
     * Hide view specific button during edit
     * @param isEdit
     */
    toggleViewButtons: function(isEdit) {
        this.$('.headerpane span[data-type="badge"]').toggle(!isEdit);
        this.$('.headerpane span[data-type="favorite"]').toggle(!isEdit);
        this.$('.headerpane span[data-type="follow"]').toggle(!isEdit);
        this.$('.headerpane .previous-row').toggle(!isEdit);
        this.$('.headerpane .next-row').toggle(!isEdit);
    },

    /**
     * Hide/show field label given a field
     * @param field
     */
    toggleLabelByField: function (field) {
        if (field.action === 'edit') {
            field.$el.closest('.record-cell').find('.record-label').show();
        } else {
            field.$el.closest('.record-cell').find('.record-label').hide();
        }
    },

    handleSave: function() {
        var self = this;
        self.inlineEditMode = false;

        var options = {
            showAlerts: true,
            success: _.bind(function() {
                if (this.createMode) {
                    app.navigate(this.context, this.model);
                } else if (!this.disposed) {
                    this.render();
                }
            }, this),
            viewed: true
        };

        options = _.extend({}, options, self.getCustomSaveOptions(options));

        app.file.checkFileFieldsAndProcessUpload(self, {
                success: function() {
                    self.model.save({}, options);
                }
            }, {
                deleteIfFails: false
            }
        );

        self.$('.record-save-prompt').hide();
        if (!self.disposed) {
            self.render();
        }
    },

    getCustomSaveOptions: function(options) {
        return {};
    },

    handleCancel: function () {
        this.model.revertAttributes();
        this.toggleEdit(false);
        this.inlineEditMode = false;
    },

    handleDelete: function () {
        var self = this,
            moduleContext = {
                module: app.lang.get('LBL_MODULE_NAME_SINGULAR', this.module),
                name: (this.model.get('name') ||
                    (this.model.get('first_name') + ' ' + this.model.get('last_name')) || '').trim()
            };

        app.alert.show('delete_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('NTC_RECORD_DELETE_CONFIRMATION', null, moduleContext),
            onConfirm: function () {
                self.model.destroy({
                    //Show alerts for this request
                    showAlerts: {
                        'process': true,
                        'success': {
                            messages: app.lang.get('NTC_RECORD_DELETE_SUCCESS', null, moduleContext)
                        }
                    }
                });
                self.context.trigger("record:deleted");
                app.router.navigate("#" + self.module, {trigger: true});
            }
        });
    },
    /**
     * {@inheritdoc}
     * Attach tab handler to jump into the next target field
     */
    handleKeyDown: function (e, field) {
        if (e.which === 9) { // If tab
            e.preventDefault();
            field.$(field.fieldTag).trigger("change");
            var direction = e.shiftKey ? 'prevField' : 'nextField',
                nextField = field[direction];

            if (!nextField) {
                return;
            }

            if (nextField.$el.closest('.panel_hidden').hasClass('hide')) {
                this.toggleMoreLess();
            }
            this.toggleField(field, false);
            this.toggleField(nextField, true);
            // the field we need to toggle until we reach one that's not
            if (field.isDisabled() && nextField) {
                var curField = field;
                while (curField.isDisabled) {
                    if (curField[direction]) {
                        this.toggleField(curField[direction], true);
                        curField = curField[direction];
                    } else {
                        break;
                    }

                }
            }
        }
    },

    /**
     * Show/hide buttons depending on the state defined for each buttons in the metadata
     * @param state
     */
    setButtonStates: function (state) {
        this.currentState = state;

        _.each(this.buttons, function (field) {
            var showOn = field.def.showOn;
            if (_.isUndefined(showOn) || (showOn === state)) {
                field.show();
            } else {
                field.hide();
            }
        }, this);
    },

    /**
     * Set the title in the header pane
     * @param title
     */
    setTitle: function (title) {
        var $title = this.$('.headerpane .module-title');
        if ($title.length > 0) {
            $title.text(title);
        } else {
            this.$('.headerpane').prepend('<h1><span class="module-title">' + title + '</span></h1>');
        }
    },

    _dispose: function () {
        _.each(this.editableFields, function(field) {
            field.nextField = null;
            field.prevField = null;
        });
        this.buttons = null;
        this.editableFields = null;
        this.off("editable:keydown", this.handleKeyDown, this);
        app.view.View.prototype._dispose.call(this);
    },

    _buildGridsFromPanelsMetadata: function(panels) {
        var lastTabIndex  = 0;
        this.noEditFields = [];

        _.each(panels, function(panel) {
            // it is assumed that a field is an object but it can also be a string
            // while working with the fields, might as well take the opportunity to check the user's ACLs for the field
            _.each(panel.fields, function(field, index) {
                if (_.isString(field)) {
                    panel.fields[index] = field = {name: field};
                }

                // disable the pencil icon if the user doesn't have ACLs
                if (field.readonly || !app.acl.hasAccessToModel('edit', this.model, field.name)) {
                    this.noEditFields.push(field.name);
                }
            }, this);

            // Set flag so that show more link can be displayed to show hidden panel.
            if (panel.hide) {
                this.hiddenPanelExists = true;
            }

            // labels: visibility for the label
            if (_.isUndefined(panel.labels)) {
                panel.labels = true;
            }

            if (_.isFunction(this.getGridBuilder)) {
                var options = {
                        fields:      panel.fields,
                        columns:     panel.columns,
                        labels:      panel.labels,
                        labelsOnTop: panel.labelsOnTop,
                        tabIndex:    lastTabIndex
                    },
                    gridResults = this.getGridBuilder(options).build();

                panel.grid   = gridResults.grid;
                lastTabIndex = gridResults.lastTabIndex;
            }
        }, this);
    }
})
