describe('Leads.Base.Layout.ConvertPanel', function() {
    var app, layout, triggerStub, contextTriggerStub, dupeViewContextTriggerStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('convert-panel-header', 'view', 'base', null, 'Leads');
        SugarTest.loadHandlebarsTemplate('convert-panel', 'layout', 'base', null, 'Leads');
        SugarTest.loadComponent('base', 'layout', 'toggle');
        SugarTest.testMetadata.set();
        SugarTest.testMetadata.addViewDefinition('create', {'panels': [{'fields': [{'name': 'last_name'}]}]}, 'Contacts');
        SugarTest.app.data.declareModels();

        layout = SugarTest.createLayout('base', 'Leads', 'convert-panel', {
            moduleNumber: 1,
            module: 'Contacts',
            copyData: true,
            required: true,
            enableDuplicateCheck: true,
            duplicateCheckOnStart: true,
            dependentModules: {
                'Foo': {
                    'fieldMapping': {
                        'foo_id': 'id'
                    }
                }
            }
        }, null, true);

        triggerStub = sinon.stub(layout, 'trigger');
        contextTriggerStub = sinon.stub(layout.context, 'trigger');
        dupeViewContextTriggerStub = sinon.stub(layout.duplicateView.context, 'trigger');
    });

    afterEach(function() {
        triggerStub.restore();
        contextTriggerStub.restore();
        dupeViewContextTriggerStub.restore();
        sinon.collection.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
    });

    it('should set up dependency listeners if dependencies exist', function() {
        layout.addDependencyListeners();

        expect(_.has(layout.context._events, 'lead:convert:Foo:complete')).toBe(true);
        expect(_.has(layout.context._events, 'lead:convert:Foo:reset')).toBe(true);
    });

    it('should show dupecheck subview when dupe check is complete and duplicates were found', function() {
        layout.duplicateView.collection.length = 1;
        layout.dupeCheckComplete();

        expect(layout.currentToggle).toEqual(layout.TOGGLE_DUPECHECK);
        expect(layout.currentState.dupeCount).toEqual(1);
    });

    it('should show create subview when dupe check is complete and no duplicates were found', function() {
        layout.duplicateView.collection.length = 0;
        layout.dupeCheckComplete();

        expect(layout.currentToggle).toEqual(layout.TOGGLE_CREATE);
        expect(layout.currentState.dupeCount).toEqual(0);
    });

    it('should not show create subview when dupe check complete, no dupes found, but already toggled previously', function() {
        layout.duplicateView.collection.length = 0;
        layout.dupeCheckComplete();
        layout.showComponent(layout.TOGGLE_DUPECHECK);
        layout.dupeCheckComplete();

        expect(layout.currentToggle).toEqual(layout.TOGGLE_DUPECHECK);
    });

    it('should remove fields from metadata that are marked as to be hidden in the convert metadata', function() {
        var meta = {
            panels: [
                {
                    fields: [
                        {name: 'foo', type: 'blah'},
                        {name: 'bar', type: 'blah'},
                        'baz'
                    ]
                }
            ]
        };
        var convertMeta = {
            hiddenFields: {
                'foo': 'Foo',
                'baz': 'Foo'
            }
        };
        var expectedMeta = {
            panels: [
                {
                    fields: [
                        {name: 'foo', type: 'blah', readonly: true, required: false},
                        {name: 'bar', type: 'blah'},
                        {name: 'baz', readonly: true, required: false}
                    ]
                }
            ]
        };

        layout.removeFieldsFromMeta(meta, convertMeta);

        expect(meta).toEqual(expectedMeta);
    });

    it('should pass along requests to open if panel is already complete', function() {
        layout.currentState.complete = true;
        layout.handleOpenRequest();

        expect(contextTriggerStub.lastCall.args[0]).toEqual('lead:convert:2:open');
    });

    it('should pass along requests to open if panel is disabled', function() {
        layout.$(layout.accordionHeading).removeClass('enabled');
        layout.handleOpenRequest();

        expect(contextTriggerStub.lastCall.args[0]).toEqual('lead:convert:2:open');
    });

    it('should open panel if enabled, not complete, and a request has been made to open', function() {
        layout.currentState.complete = false;
        layout.$(layout.accordionHeading).addClass('enabled');

        expect(layout.$(layout.accordionBody)).not.toHaveClass('in');

        layout.handleOpenRequest();
        expect(layout.$(layout.accordionBody)).toHaveClass('in');
    });

    describe('Associate Button Click', function() {
        var runValidationStub, markCompleteStub, clickEvent;

        beforeEach(function() {
            runValidationStub = sinon.stub(layout, 'runCreateValidation');
            markCompleteStub = sinon.stub(layout, 'markPanelComplete');
            clickEvent = {
                currentTarget: '<span></span>',
                stopPropagation: $.noop
            };
        });

        afterEach(function() {
            runValidationStub.restore();
            markCompleteStub.restore();
        });

        it('should ignore associate button clicks if button is disabled', function() {
            clickEvent.currentTarget = '<span class="disabled"></span>';

            layout.handleAssociateClick(clickEvent);

            expect(runValidationStub.callCount).toBe(0);
            expect(markCompleteStub.callCount).toBe(0);
        });

        it('should run create validation if associate button clicked and current toggle is create', function() {
            layout.currentToggle = layout.TOGGLE_CREATE;
            layout.handleAssociateClick(clickEvent);

            expect(runValidationStub.callCount).toBe(1);
            expect(markCompleteStub.callCount).toBe(0);
        });

        it('should mark panel complete if associate button clicked and current toggle is dupecheck', function() {
            layout.currentToggle = layout.TOGGLE_DUPECHECK;
            layout.handleAssociateClick(clickEvent);

            expect(runValidationStub.callCount).toBe(0);
            expect(markCompleteStub.callCount).toBe(1);
        });
    });

    it('should close the current panel and fire appropriate events when marking panel complete', function() {
        var mockModel = {id: '123', name: 'Foo Bar'};

        sinon.collection.stub(layout, 'getDisplayName', function(model) {
            return model.name;
        });

        //setup
        layout.openPanel();
        layout.currentState.complete = false;
        layout.markPanelComplete(mockModel);

        expect(layout.currentState.associatedName).toEqual(mockModel.name);
        expect(layout.currentState.complete).toBe(true);
        expect(triggerStub.firstCall.args).toEqual(['lead:convert-panel:complete', mockModel.name]);
        expect(contextTriggerStub.firstCall.args).toEqual(['lead:convert-panel:complete', layout.meta.module, mockModel]);
        expect(contextTriggerStub.secondCall.args).toEqual(['lead:convert:2:open']);
    });

    it('should return the name attribute on the model for the display name if it exists', function() {
        var mockModel = app.data.createBean('Leads', {name: 'Foo Bar'});

        expect(layout.getDisplayName(mockModel)).toEqual(mockModel.get('name'));
    });

    it('should use the name field metadata to build the display name if no name attribute on model', function() {
        var mockModel = app.data.createBean('Leads', {id: '123', first_name: 'Foo', last_name: 'Baz'});
        var expectedName = 'Foo Baz';

        sinon.collection.stub(app.metadata, 'getModule', function() {
            return {
                fields: {
                    name: {
                        db_concat_fields: ['first_name', 'last_name']
                    }
                }
            };
        });

        expect(layout.getDisplayName(mockModel)).toEqual(expectedName);
    });

    it('should trigger the dupe check if dupe check enabled and all required dupe check fields are set', function() {
        var mockModel = app.data.createBean('Leads', {foo: 'Foo', bar: 'Bar'});

        layout.createView.model = mockModel;
        layout.meta.duplicateCheckRequiredFields = ['foo', 'bar'];
        layout.meta.enableDuplicateCheck = true;
        layout.triggerDuplicateCheck();

        expect(dupeViewContextTriggerStub.callCount).toBe(1);
    });

    it('should not trigger dupe check if dupe check disabled', function() {
        layout.meta.enableDuplicateCheck = false;
        layout.triggerDuplicateCheck();

        expect(dupeViewContextTriggerStub.callCount).toBe(0);
    });

    it('should not trigger dupe check if any required dupe check fields are not set', function() {
        var mockModel = app.data.createBean('Leads', {foo: 'Foo'});

        layout.createView.model = mockModel;
        layout.meta.duplicateCheckRequiredFields = ['foo', 'bar'];
        layout.meta.enableDuplicateCheck = true;
        layout.triggerDuplicateCheck();

        expect(dupeViewContextTriggerStub.callCount).toBe(0);
    });

    it('should set the dupe count to 0 and fire appropriate trigger if dupe check is triggered but not run', function() {
        layout.meta.enableDuplicateCheck = false;
        layout.triggerDuplicateCheck();

        expect(layout.currentState.dupeCount).toBe(0);
        expect(triggerStub.lastCall.args).toEqual(['lead:convert-dupecheck:complete', 0]);
    });

    it('should populate create model with lead fields and trigger dupe check when lead model passed on context', function() {
        var leadModel = app.data.createBean('Leads', {
            foo: 'Foo',
            bar: 'Bar',
            baz: 'Baz',
            north: 'Lead Value for NORTH',
            south: 'Lead Value for SOUTH',
            east: 'Lead Value for EAST',
            _module: 'Leads'
        });
        var createModel = app.data.createBean('Leads', {
            north: 'Contact Value for NORTH',
            west: 'Contact Value for WEST',
            east: 'Contact Value for EAST'
        });

        sinon.collection.stub(leadModel, 'setDefault');
        sinon.collection.stub(createModel, 'setDefault');

        layout.createView.model = createModel;
        layout.meta.duplicateCheckOnStart = true;
        layout.meta.fieldMapping = {
            'contact_foo': 'foo',
            'contact_baz': 'baz',
            'east': 'north'
        };

        sinon.collection.stub(app.metadata, 'getModule')
            .withArgs('Leads', 'fields').returns({north: 'north', south: 'south', east: 'east'})
            .withArgs('Contacts', 'fields').returns({north: 'north', west: 'west', east: 'east'});

        layout.handlePopulateRecords(leadModel);

        expect(createModel.get('contact_foo')).toEqual('Foo');
        expect(createModel.get('contact_bar')).toBeUndefined();
        expect(createModel.get('contact_baz')).toEqual('Baz');
        expect(createModel.get('north')).toEqual('Lead Value for NORTH');
        expect(createModel.get('south')).toBeUndefined();
        expect(createModel.get('east')).toEqual('Lead Value for NORTH');
        expect(createModel.get('west')).toEqual('Contact Value for WEST');
        expect(dupeViewContextTriggerStub.callCount).toBe(1);
    });

    it('should not populate create model with lead fields when copyData meta attribute is false', function() {
        var leadModel = app.data.createBean('Leads', {
            north: 'Lead Value for NORTH',
            _module: 'Leads'
        });
        var createModel = app.data.createBean('Leads', {
            north: 'Contact Value for NORTH'
        });

        layout.createView.model = createModel;
        layout.meta.duplicateCheckOnStart = true;
        layout.meta.copyData = false;

        sinon.collection.stub(app.metadata, 'getModule')
            .withArgs('Leads', 'fields').returns({north: 'north'})
            .withArgs('Contacts', 'fields').returns({north: 'north'});

        layout.handlePopulateRecords(leadModel);

        expect(createModel.get('north')).toEqual('Contact Value for NORTH');
        expect(dupeViewContextTriggerStub.callCount).toBe(0);
    });


    it('should not populate create model with lead fields when user does not have edit access to field', function() {
        var leadModel = app.data.createBean('Leads', {
            north: 'Lead Value for NORTH',
            south: 'Lead Value for SOUTH',
            east: 'Lead Value for EAST',
            birthdate: '01/01/2010',
            _module: 'Leads'
        });
        var createModel = app.data.createBean('Leads', {
            north: 'Contact Value for NORTH',
            west: 'Contact Value for WEST',
            east: 'Contact Value for EAST',
            birthdate: '01/01/1975'
        });

        sinon.collection.stub(app.acl, 'hasAccessToModel', function(action, model, field) {
            return action !== 'edit' || field !== 'birthdate';
        });
        sinon.collection.stub(leadModel, 'setDefault');
        sinon.collection.stub(createModel, 'setDefault');

        layout.createView.model = createModel;
        layout.meta.duplicateCheckOnStart = false;

        sinon.collection.stub(app.metadata, 'getModule')
            .withArgs('Leads', 'fields').returns({
                north: 'north',
                south: 'south',
                east: 'east',
                birthdate: 'birthdate'
            })
            .withArgs('Contacts', 'fields').returns({
                north: 'north',
                west: 'west',
                east: 'east',
                birthdate: 'birthdate'
            });

        layout.handlePopulateRecords(leadModel);

        expect(createModel.get('north')).toEqual('Lead Value for NORTH');
        expect(createModel.get('south')).toBeUndefined();
        expect(createModel.get('west')).toEqual('Contact Value for WEST');
        expect(createModel.get('birthdate')).toEqual('01/01/1975');
    });

    it('should trigger dupe check when panel is enabled and not already complete', function() {
        layout.currentState.complete = false;
        layout.handleEnablePanel(true);

        expect(dupeViewContextTriggerStub.callCount).toBe(1);
    });

    it('should not trigger dupe check when panel is enabled but already complete', function() {
        layout.currentState.complete = true;
        layout.handleEnablePanel(true);

        expect(dupeViewContextTriggerStub.callCount).toBe(0);
    });

    it('should update create model if dependency module changes and trigger dupe check', function() {
        var createModel = app.data.createBean('Leads');
        var fooModel = app.data.createBean('Leads', {id: '456'});

        sinon.collection.stub(createModel, 'setDefault');
        sinon.collection.stub(fooModel, 'setDefault');

        layout.createView.model = createModel;
        layout.updateFromDependentModuleChanges('Foo', fooModel);

        expect(createModel.get('foo_id')).toEqual('456');
        expect(dupeViewContextTriggerStub.callCount).toBe(1);
    });

    it('should not trigger dupe check if dependency module changes but no changes to create model', function() {
        var createModel = app.data.createBean('Leads');
        var fooModel = app.data.createBean('Leads', {nonMappedField: 'bar'});

        layout.createView.model = createModel;
        layout.updateFromDependentModuleChanges('Foo', fooModel);

        expect(createModel.attributes).toEqual({});
        expect(dupeViewContextTriggerStub.callCount).toBe(0);
    });

    it('should reset the panel if a dependency module changes', function() {
        layout.currentState.complete = true;
        layout.resetFromDependentModuleChanges('Foo');

        expect(layout.currentState.complete).toBe(false);
    });

    it('should reset the dupe collection if a dependency module changes and dupes were found previously', function() {
        layout.currentState.dupeCount = 1;
        layout.resetFromDependentModuleChanges('Foo');

        expect(layout.currentState.dupeCount).toEqual(0);
    });

    it('should remove unsaved changes when turnOffUnsavedChanges is called', function() {
        var hasUnsavedChanges = function(view) {
            return view.model.isNew() && view.model.hasChanged();
        };

        layout.createView.model.set('test', '123'); //add unsaved changes
        expect(hasUnsavedChanges(layout.createView)).toBeTruthy();

        layout.turnOffUnsavedChanges(); //happens when lead convert completes successfully
        expect(hasUnsavedChanges(layout.createView)).toBeFalsy();
    });
});
