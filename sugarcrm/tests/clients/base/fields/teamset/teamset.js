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
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */
describe('Base.Field.Teamset', function() {

    var app, field, sinonSandbox, oRouter, buildRouteStub;

    beforeEach(function () {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        var fieldDef = {
            "name": "team_name",
            "rname": "name",
            "vname": "LBL_TEAM_NAME",
            "type": "relate",
            "custom_type": "teamset",
            "link": "accounts",
            "table": "accounts",
            "join_name": "accounts",
            "isnull": "true",
            "module": "Accounts",
            "dbType": "varchar",
            "len": 100,
            "source": "non-db",
            "unified_search": true,
            "comment": "The name of the account represented by the account_id field",
            "required": true, "importable": "required"
        };
        sinonSandbox = sinon.sandbox.create();
        SugarTest.loadComponent("base", "field", "relate");
        var model = new Backbone.Model({
            id: 'blahblahid',
            team_name: [{id: 'test-id', name: 'blahblah', primary: false}],
            setDefaultAttribute: sinon.stub(),
            removeDefaultAttribute: sinon.stub()
        });
        field = SugarTest.createField("base", "team_name", "teamset", "edit", fieldDef, null, model);

        if (!$.fn.select2) {
            $.fn.select2 = function (options) {
                var obj = {
                    on: function () {
                        return obj;
                    }
                };
                return obj;
            };
        }

        // Workaround because router not defined yet
        oRouter = SugarTest.app.router;
        SugarTest.app.router = {buildRoute: function () {
        }};
        buildRouteStub = sinon.stub(SugarTest.app.router, 'buildRoute', function (module, id, action, params) {
            return module + '/' + id;
        });
    });

    afterEach(function () {
        app.cache.cutAll();
        app.view.reset();
        sinonSandbox.restore();
        buildRouteStub.restore();
        SugarTest.app.router = oRouter;
        Handlebars.templates = {};
        field.model = null;
        field = null;
    });

    it("should set value correctly", function () {
        var index = 0;
        field.render();
        field.$el.append($("<select data-index=" + index + "></select><div class='chzn-container-active'></div>"));
        var expected_id = '0987',
            expected_name = 'blahblah';
        field.setValue({id: expected_id, value: expected_name});
        var actual_model = field.model.get('team_name'),
            actual_id = actual_model[index].id,
            actual_name = actual_model[index].name;

        expect(actual_id).toEqual(expected_id);
        expect(actual_name).toEqual(expected_name);
    });

    it("should load the default team setting that is specified in the user profile settings", function () {
        field.model = new Backbone.Model();
        field.model.setDefaultAttribute = sinon.stub();
        field.model.removeDefaultAttribute = sinon.stub();
        var expected = [
                {
                    id: '1', name: 'global'
                }
            ],
            getPreference = sinon.stub(app.user, 'getPreference', function () {
                return expected;
            });
        field.render();
        var actual = field.value;
        expect(actual.length).toEqual(1);
        expect(expected[0].id).toEqual(actual[0].id);
        expect(expected[0].name).toEqual(actual[0].name);
        var actual_var = field.model.get('team_name');
        expect(actual_var.length).toEqual(1);
        expect(actual_var[0].id).toEqual(expected[0].id);
        expect(actual_var[0].name).toEqual(expected[0].name);
        expect(actual_var).not.toBe(expected);
        getPreference.restore();
    });

    it("should add or remove team from the list", function () {
        field.render();
        var expected = (field.model.get(field.def.name)).length + 1;
        field.addTeam();
        // A team object shouldn't appear in model unless a team is specified.
        field.setValue({id: 'test', value: 'test'});
        var actual = (field.model.get(field.def.name)).length;
        expect(expected).toEqual(actual);

        expected = actual - 1;
        field.removeTeam(0);
        actual = (field.model.get(field.def.name)).length;
        expect(expected).toEqual(actual);
    });

    it("should set a team as primary", function () {
        field.model.set('team_name', [
            {id: '111-222', name: 'blahblah', primary: false},
            {id: 'abc-eee', name: 'poo boo', primary: true}
        ]);
        field.render();
        expect(field.value[0].primary).toBe(false);
        expect(field.value[1].primary).toBe(true);

        field.setPrimary(0);
        expect(field.value[0].primary).toBe(true);
        expect(field.value[1].primary).toBe(false);

    });


    it("should toggle out the primary option only if the teamset appends to the massupdate and it sets the existing primary index as primary once again", function () {
        field.model.set('team_name', [
            {id: '111-222', name: 'blahblah', primary: false},
            {id: 'abc-eee', name: 'poo boo', primary: true}
        ]);
        //Setup for appeding team
        field.model.set('team_name_type', '1');
        field.render();
        expect(field.value[0].primary).toBe(false);
        expect(field.value[1].primary).toBe(true);

        field.setPrimary(1);
        expect(field.value[0].primary).toBe(false);
        expect(field.value[1].primary).toBe(false);


        field.setPrimary(1);
        expect(field.value[0].primary).toBe(false);
        expect(field.value[1].primary).toBe(true);

    });

    it("should set the first item as primary when user choose the appending team option as false and no teams are assigned to primary", function() {
        field.model.set('team_name', [
            {id: '111-222', name: 'blahblah', primary: false},
            {id: 'abc-eee', name: 'poo boo', primary: true}
        ]);
        //Setup for appeding team
        field.model.set('team_name_type', '1');
        field.render();
        field.setPrimary(1);
        expect(field.value[0].primary).toBe(false);
        expect(field.value[1].primary).toBe(false);

        field.model.set('team_name_type', '0');
        expect(field.value[0].primary).toBe(true);
        expect(field.value[1].primary).toBe(false);
    });

    it("should not let you remove last team when there is only one team left", function () {
        field.model.set('team_name', [
            {id: '111-222', name: 'blahblah', primary: true}
        ]);
        field.render();
        field.removeTeam(0);
        expect(field.value[0].id).toEqual('111-222');
    });

    it("should have an add button but not a remove button when there is only one team left", function () {
        field.model.set('team_name', [
            {id: '111-222', name: 'blahblah', primary: true}
        ]);
        field.render();
        expect(field.value).toEqual([
            {id: '111-222', name: 'blahblah', primary: true, add_button: true}
        ]);
    });

    it("with multiple teams should have remove buttons and an add button on last team", function () {
        field.model.set('team_name', [
            {id: '111-222', name: 'blahblah', primary: true},
            {id: '222-333', name: 'blahblah2', primary: false}
        ]);
        field.render();
        expect(field.value[0]).toEqual({id: '111-222', name: 'blahblah', primary: true, remove_button: true});
        expect(field.value[1]).toEqual({id: '222-333', name: 'blahblah2', primary: false, remove_button: true, add_button: true});
    });

    it("cannot make a blank team the primary team", function () {
        field.model.set('team_name', [
            {id: 'abc-eee', name: 'poo boo', primary: true},
            {primary: false}
        ]);
        field.render();
        expect(field.value[0].primary).toBe(true);
        expect(field.value[1].primary).toBe(false);
        field.setPrimary(1);
        expect(field.value[1].primary).toBe(false);
    });

    it("cannot make an unselected team (that has no ID) the primary team", function () {
        var jQueryDataStub = sinonSandbox.stub(jQuery, "data", function () {
            // Mocks current target's data attribute index to return index to second item
            return 1;
        });
        var setPrimaryStub = sinonSandbox.stub(field, "setPrimary");
        field.model.set('team_name', [
            {id: 1, "name": "Global", "primary": true},
            {"add_button": true, "remove_button": true, primary: false}
        ]);
        field.render();
        field.setPrimaryItem({});
        expect(field.value[1].primary).toBe(false);
        expect(setPrimaryStub).not.toHaveBeenCalled();
    });

    it("should let you add an item if team IS selected for very last item", function () {
        var addTeamStub = sinonSandbox.stub(field, "addTeam");
        field.model.set('team_name', [
            {id: 1, "name": "Global", "primary": true},
            {"add_button": true, primary: false, id: 2}
        ]);
        field.render();
        var dataStub = sinonSandbox.stub(jQuery.fn, "data", function () {
            return  1;
        });
        field.addItem({});
        waits(50);
        runs(function () {
            expect(addTeamStub).toHaveBeenCalled();
        });
    });
    it("should NOT let you add an item if team hasn't been selected for very last item", function () {
        var addTeamStub = sinonSandbox.stub(field, "addTeam");
        field.model.set('team_name', [
            {id: 1, "name": "Global", "primary": true},
            {"add_button": true, primary: false}
        ]);
        field.render();
        var dataStub = sinonSandbox.stub(jQuery.fn, "data", function () {
            return  1;
        });
        field.addItem({});
        waits(50);
        runs(function () {
            expect(addTeamStub).not.toHaveBeenCalled();
        });
    });
    it("should leave model in `hasChanged` state upon calling _updateAndTriggerChange", function() {
        var teamsetValue = [{"id": "West", "primary": true}];
        field._updateAndTriggerChange(teamsetValue);
        expect(field.model.hasChanged()).toBeTruthy();
    });

    it('should be able to compare for the equality', function() {
        var teamsetValue = [
                {'id': 'East', 'primary': true},
                {'id': 'West', 'stuff': 'ignore'}
            ],
            equalTeamsetValue = [
                {'id': 'East', 'primary': true, 'additional': 'blah'},
                {'id': 'West'}
            ],
            getFormatStub = sinonSandbox.stub(app.view.Field.prototype, 'getFormattedValue', function() {
                return this.format(this.model.get(this.name));
            }),
            otherFielddef = {
                'name': 'other_team_name',
                'type': 'teamset'
            },
            otherField = app.view.createField({
                def: otherFielddef,
                view: field.view,
                context: field.context,
                model: field.model,
                module: field.model.module,
                platform: 'base'
            });

        field.model.set(field.name, teamsetValue, {silent: true});
        expect(field.equals(otherField)).toBe(false);
        otherField.model.set(otherField.name, equalTeamsetValue, {silent: true});
        expect(field.equals(otherField)).toBe(true);
    });

    describe('_loadTemplate', function(){
        it("should load list template when doing inline editing (See SP-1197)", function(){
            field.view.action = "list";
            field.action = "edit";
            field._loadTemplate();
            expect(field.tplName).toEqual("list");

            field.view.action = "list";
            field.action = "list";
            field._loadTemplate();
            expect(field.tplName).toEqual("list");
        });
    });
});
