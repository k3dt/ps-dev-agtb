describe('Base.Field.Relate', function() {

    var app, field, fieldDef;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('relate', 'field', 'base', 'overwrite-confirmation');
        SugarTest.testMetadata.set();
        app = SugarTest.app;

        fieldDef = {
            "name": "account_name",
            "rname": "name",
            "id_name": "account_id",
            "vname": "LBL_ACCOUNT_NAME",
            "type": "relate",
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
            "required": true,
            "importable": "required"
        };
    });

    afterEach(function() {
        sinon.collection.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        fieldDef = null;
    });

    describe("SetValue", function () {
        beforeEach(function () {
            field = SugarTest.createField("base", "account_name", "relate", "edit", fieldDef);
            field.module = 'Accounts';
            field.model = new Backbone.Model({account_id: "1234", account_name: "bob"});
        });

        afterEach(function() {
            field.dispose();
        });

        it("should set value correctly", function () {
            var expected_id = '0987',
                expected_name = 'blahblah';

            field.setValue({id: expected_id, value: expected_name});
            var actual_id = field.model.get(field.def.id_name),
                actual_name = field.model.get(field.def.name);

            //Relate takes care of its unformating
            //unformat is overriden to return the unformated value off the model
            expect(field.unformat('test')).toEqual(actual_id);
            expect(field.unformat('test')).toEqual(expected_id);

            expect(actual_id).toEqual(expected_id);
            expect(actual_name).toEqual(expected_name);
        });
    });

    describe('custom rname', function() {

        beforeEach(function() {
            fieldDef.rname = 'account_type';
            field = SugarTest.createField('base', 'account_name', 'relate', 'edit', fieldDef);
            field.module = 'Accounts';
            field.model = new Backbone.Model({account_id: '1234', account_name: 'bob'});
        });

        afterEach(function() {
            field.dispose();
        });

        it('should set the displaying name by rname property value', function() {
            var expectedId = '0987',
                expectedName = 'blahblah',
                unexpectedName = 'oh~no';

            field.setValue({id: expectedId, value: unexpectedName, account_type: expectedName});
            var actualId = field.model.get(field.def.id_name),
               actualName = field.model.get(field.def.name);

            expect(actualId).toEqual(expectedId);
            expect(actualName).toEqual(expectedName);
            expect(actualName).not.toEqual(unexpectedName);
        });
    });

    describe("Populate related fields", function () {

        it("should warn the wrong metadata fields that populates unmatched fields", function () {
            sinon.collection.stub(app.metadata, 'getModule', function() {
                return {
                    fields: {
                        'field1': false
                    }
                };
            });
            var loggerStub = sinon.collection.stub(app.logger, 'error');
            fieldDef.populate_list = {
                "field1": "foo",
                "billing_office": "boo"
            };
            field = SugarTest.createField("base", "account_name", "relate", "edit", fieldDef);
            field.module = 'Accounts';
            field.model = new Backbone.Model({account_id: "1234", account_name: "bob"});

            expect(loggerStub).toHaveBeenCalled();

            field.dispose();
        });

        it("should populate related variables when the user confirms the changes", function () {
            fieldDef.populate_list = {
                "billing_office": "primary_address_1"
            };
            field = SugarTest.createField("base", "account_name", "relate", "edit", fieldDef);
            field.module = 'Accounts';
            field.model = new Backbone.Model({account_id: "1234", account_name: "bob"});
            field.model.fields = {
                'primary_address_1': {
                    label: ''
                }
            };

            //Before confirmed the dialog
            var expected_id = '0987',
                expected_name = 'blahblah',
                expected_primary_address_1 = 'should be undefined';

            field.setValue({
                id: expected_id,
                value: expected_name,
                boo: 'should not be in',
                billing_office: expected_primary_address_1
            });
            var actual_id = field.model.get(field.def.id_name),
                actual_name = field.model.get(field.def.name),
                actual_primary_address_1 = field.model.get("primary_address_1");
            expect(actual_id).toEqual(expected_id);
            expect(actual_name).toEqual(expected_name);
            expect(actual_primary_address_1).toBeUndefined();
            expect(field.model.get("boo")).toBeUndefined();

            //After the user confirms the dialog
            sinon.collection.stub(app.alert, 'show', function(msg, param) {
                param.onConfirm();
            });
            expected_primary_address_1 = '1234 blahblah st.';

            field.setValue({
                id: expected_id,
                value: expected_name,
                boo: 'should not be in',
                billing_office: expected_primary_address_1
            });
            actual_id = field.model.get(field.def.id_name);
            actual_name = field.model.get(field.def.name);
            actual_primary_address_1 = field.model.get("primary_address_1");
            expect(actual_id).toEqual(expected_id);
            expect(actual_name).toEqual(expected_name);
            expect(actual_primary_address_1).toEqual(expected_primary_address_1);
            expect(field.model.get("boo")).toBeUndefined();

            field.dispose();
        });
        it("should not populate related variables which does NOT have acl controls", function () {
            fieldDef.populate_list = {
                "billing_office": "primary_address_1",
                "billing_phone": "primary_phone_number"
            };
            field = SugarTest.createField("base", "account_name", "relate", "edit", fieldDef);
            field.module = 'Accounts';
            field.model = new Backbone.Model({account_id: "1234", account_name: "bob"});
            field.model.fields = {
                'primary_address_1': {
                    label: ''
                },
                'primary_phone_number': {
                    label: ''
                }
            };
            var aclMapping = {
                primary_address_1: false,
                primary_phone_number: true
            };
            sinon.collection.stub(app.alert, 'show', function(msg, param) {
                param.onConfirm();
            });
            sinon.collection.stub(app.acl, 'hasAccessToModel', function(action, model, field) {
                return aclMapping[field];
            });
            var expected_id = '0987',
                expected_name = 'blahblah',
                expected_primary_address_1 = 'should be undefined',
                expected_primary_phone_number = '999)111-2222';

            field.setValue({
                id: expected_id,
                value: expected_name,
                boo: 'should not be in',
                billing_office: expected_primary_address_1,
                billing_phone: expected_primary_phone_number
            });
            var actual_id = field.model.get(field.def.id_name),
                actual_name = field.model.get(field.def.name),
                actual_primary_address_1 = field.model.get("primary_address_1"),
                actual_primary_phone_number = field.model.get("primary_phone_number");
            expect(actual_id).toEqual(expected_id);
            expect(actual_name).toEqual(expected_name);
            expect(actual_primary_address_1).toBeUndefined();
            expect(field.model.get("boo")).toBeUndefined();
            expect(actual_primary_phone_number).toBe(expected_primary_phone_number);

            field.dispose();
        });
        it("should build route using check access", function () {
            var aclHasAccessStub = sinon.collection.stub(app.acl, 'hasAccess').returns(false);
            var field = SugarTest.createField("base", "account_name", "relate", "edit", fieldDef);

            field.module = 'Accounts';
            field.model = new Backbone.Model({account_id: "1234", account_name: "bob"});
            field.buildRoute('Users', 1);

            expect(aclHasAccessStub).toHaveBeenCalled();
            expect(field.href).toBeUndefined();

            aclHasAccessStub.restore();
            aclHasAccessStub = sinon.collection.stub(app.acl, 'hasAccess').returns(true);

            field.buildRoute('Users', 1);
            expect(aclHasAccessStub).toHaveBeenCalled();
            expect(field.href).toBeDefined();

            field.dispose();
        });
    });

    describe("alert message", function () {
        var alertShowStub;
        beforeEach(function () {
            fieldDef.populate_list = {
                "populate_field1": "populate_field_dist1",
                "populate_field2": "populate_field_dist2"
            };
            field = SugarTest.createField("base", "account_name", "relate", "edit", fieldDef);
            field.module = 'Accounts';
            field.model = new Backbone.Model({account_id: "1234", account_name: "bob"});
            field.model.fields = {
                'populate_field_dist1': {
                    label: ''
                },
                'populate_field_dist2': {
                    label: ''
                }
            };
            alertShowStub = sinon.collection.stub(app.alert, 'show');
        });

        afterEach(function() {
            field.dispose();
        });

        it("should call app.alert.show() if auto_populate is not defined", function () {
            var expected_id = '0987',
                expected_name = 'blahblah';

            field.setValue({
                id: expected_id,
                value: expected_name,
                populate_field1: 'new value 1',
                populate_field2: 'new value 2'
            });
            expect(app.alert.show).toHaveBeenCalled();
        });

        it("should call app.alert.show() if auto_populate is not true", function () {
            var expected_id = '0987',
                expected_name = 'blahblah';

            field.def.auto_populate = false;

            field.setValue({
                id: expected_id,
                value: expected_name,
                populate_field1: 'new value 1',
                populate_field2: 'new value 2'
            });
            expect(app.alert.show).toHaveBeenCalled();
        });

        it("should not call app.alert.show() if auto_populate is true", function () {
            var expected_id = '0987',
                expected_name = 'blahblah';

            field.def.auto_populate = true;

            field.setValue({
                id: expected_id,
                value: expected_name,
                populate_field1: 'new value 1',
                populate_field2: 'new value 2'
            });
            expect(app.alert.show).not.toHaveBeenCalled();
        });
    });

    describe('render', function() {
        var _renderStub, getSearchModuleStub;

        beforeEach(function() {
            field = SugarTest.createField('base', 'account_name', 'relate', 'edit', fieldDef);
            _renderStub = sinon.collection.stub(app.view.Field.prototype, '_render');
            getSearchModuleStub = sinon.collection.stub(field, 'getSearchModule');
        });

        afterEach(function() {
            field.dispose();
        });

        using('different search modules', [
            {
                module: undefined,
                render: true
            },
            {
                module: 'invalidModule',
                render: false
            },
            {
                module: 'Cases',
                render: true
            }
        ], function(options) {

            it('should not render if the related module is invalid', function() {
                getSearchModuleStub.returns(options.module);
                field.render();

                expect(_renderStub.called).toBe(options.render);
            });
        });
    });

    describe('openSelectDrawer', function() {
        var openStub;

        beforeEach(function() {
            app.drawer = app.drawer || {};
            app.drawer.open = app.drawer.open || $.noop;
            field = SugarTest.createField('base', 'account_name', 'relate', 'edit', fieldDef);
            openStub = sinon.collection.stub(app.drawer, 'open');

            field.model.fields = {
                account_id: {
                    name: 'account_id'
                },
                account_name: {
                    name: 'account_name',
                    id_name: 'account_id'
                },
                contact_id: {
                    name: 'contact_id'
                },
                contact_name: {
                    name: 'contact_name',
                    id_name: 'contact_id'
                }
            };
        });

        afterEach(function() {
            field.dispose();
        });

        it('should open the drawer with no filter options', function() {
            field.openSelectDrawer();
            expect(openStub).toHaveBeenCalled();
            var arguments = openStub.firstCall.args,
                filterOptions = arguments[0].context.filterOptions;
            expect(filterOptions).toBeUndefined();
        });

        using('different definitions', [
            {
                def: {
                    filter_relate: {
                        'account_id': 'account_id'
                    }
                },
                expected: {
                    label: 'The related Account',
                    filter_populate: {
                        'account_id': '1234-5678'
                    }
                }
            },
            {
                def: {
                    filter_relate: {
                        'contact_id': 'id'
                    }
                },
                expected: {
                    label: 'The related Contact',
                    filter_populate: {
                        'id': 'abcd-efgh'
                    }
                }
            }
        ], function(option) {

            beforeEach(function() {
                field.model.set('account_id', '1234-5678');
                field.model.set('account_name', 'The related Account');
                field.model.set('contact_id', 'abcd-efgh');
                field.model.set('contact_name', 'The related Contact');
            });

            it('should open the drawer with filter options', function() {
                field.def.filter_relate = option.def.filter_relate;
                field.openSelectDrawer();
                expect(openStub).toHaveBeenCalled();
                var arguments = openStub.firstCall.args,
                    filterOptions = arguments[0].context.filterOptions;
                expect(filterOptions).toBeDefined();
                expect(filterOptions.initial_filter).toEqual('$relate');
                expect(filterOptions.initial_filter_label).toEqual(option.expected.label);
                expect(filterOptions.filter_populate).toEqual(option.expected.filter_populate);
                expect(filterOptions.stickiness).toEqual(false);
            });
        });
    });
});
