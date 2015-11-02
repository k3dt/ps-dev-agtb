/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

describe('NotificationCenter.Field.Address', function() {
    var app, field, sandbox,
        module = 'NotificationCenter',
        fieldType = 'address',
        fieldDef,
        layout,
        carriers = {foo: {selectable: true, status: true}},
        options = ['address1', 'address2'],
        model;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        carriers = {foo: {status: false}};
        layout = SugarTest.createLayout('base', module, 'config-drawer', null, null, true);
        model = layout.model;
        model.set('configMode', 'user');
        model.set('global', {carriers: carriers});
        model.set('personal', {carriers: carriers});
        fieldDef = {
            name: 'dummy',
            type: fieldType,
            view: 'edit',
            options: options,
            carrier: 'foo'
        };
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'edit', module);
        SugarTest.testMetadata.set();
        field = SugarTest.createField('base', 'dummy', fieldType, 'edit', fieldDef, module, model, null, true);
        field.carrier = 'foo';
    });

    afterEach(function() {
        if (field) {
            field.dispose();
        }
        Handlebars.templates = {};
        sandbox.restore();
        app.cache.cutAll();
        layout = null;
        model = null;
        field = null;
    });

    it('should set "_currentIndex" property to 0', function() {
        expect(field._currentIndex).toEqual(0);
    });

    it('should call showHideField() on render', function() {
        var method = sandbox.stub(field, 'showHideField');
        field.render();
        expect(method).toHaveBeenCalled();
    });

    describe('showHideField()', function() {
        var global, personal;

        beforeEach(function() {
            global = {foo: {status: null}};
            personal = {foo: {status: null}};
        });

        using('statues and action',
            [
                [true, true, 'show'],
                [true, false, 'hide'],
                [false, true, 'hide'],
                [false, false, 'hide']
            ],
            function(globalStatus, personalStatus, action) {
                it('should show or hide field according to both global & personal carrier status', function() {
                    var method = sandbox.stub(field, action);
                    global['foo']['status'] = globalStatus;
                    personal['foo']['status'] = personalStatus;
                    model.set('global', {carriers: global});
                    model.set('personal', {carriers: personal});
                    field.showHideField();
                    expect(method).toHaveBeenCalled();
                });
            });
    });

    describe('getFormattedValue()', function() {
        using('selectedAddresses and value',
            [
                [['email1', 'email2'], [{key: 0, id: 'email1'}, {key: 1, id: 'email2'}]],
                [['email1'], [{key: 0, id: 'email1'}]],
                [[], []]
            ],
            function(selectedAddresses, value) {
                it('should call format() with correct value', function() {
                    var format = sandbox.stub(field, 'format');
                    model.set('selectedAddresses', {'foo': selectedAddresses});
                    field.getFormattedValue();
                    expect(format).toHaveBeenCalledWith(value);
                });
            });
    });

    describe('setValue()', function() {
        using('model , currentIndex and value',
            [
                [[{id: 'foo'}], 0, [{id: 'bar'}]],
                [[{id: 'foo'}], null, [{id: 'bar'}]],
                [[{}, {}, {id: 'foo'}], 2, [{}, {}, {id: 'bar'}]]
            ],
            function(oldValue, currentIndex, newValue) {
                it('should call _updateModelAndTriggerChange() with correct value', function() {
                    var method = sandbox.stub(field, '_updateModelAndTriggerChange');
                    field._currentIndex = currentIndex;
                    field.value = oldValue;
                    field.setValue({id: 'bar'});
                    expect(method).toHaveBeenCalledWith(newValue);
                });
            });
    });

    describe('addItem()', function() {
        var evt, method;

        beforeEach(function() {
            evt = {
                currentTarget: "<a data-index='0'></a>"
            };
            method = sandbox.stub(field, '_updateModelAndTriggerChange');
        });

        it('should call _updateModelAndTriggerChange() with dummy value', function() {
            field._currentIndex = 0;
            field.value = [];
            field.addItem(evt);
            expect(method).toHaveBeenCalledWith([{id: ''}]);
        });

        it('should increment current index by one', function() {
            field._currentIndex = 0;
            field.value = [];
            field.addItem(evt);
            expect(field._currentIndex).toEqual(1);
        });
    });


    describe('removeItem()', function() {
        var evt, method;

        beforeEach(function() {
            evt = {currentTarget: null};
            method = sandbox.stub(field, '_updateModelAndTriggerChange');
        });

        it('should should call _updateModelAndTriggerChange() with correct value', function() {
            evt.currentTarget = "<a data-index='1'></a>";
            field._currentIndex = 1;
            field.value = [{id: 'foo'}, {id: 'bar'}];
            field.removeItem(evt);
            expect(method).toHaveBeenCalledWith([{id: 'foo'}]);
        });

        using('old value, data-index, new value list',
            [
                [[{id: 'foo'}], 0, [{id: 'foo'}]],
                [[{id: 'foo'}, {id: 'bar'}], 0, [{id: 'bar'}]],
                [[{id: 'foo'}, {id: 'bar'}], 1, [{id: 'foo'}]]
            ],
            function(oldValue, index, newValue) {
                it('should remove current value from values list correctly', function() {
                    evt.currentTarget = '<a data-index=' + index + '></a>';
                    field._currentIndex = index;
                    field.value = oldValue;
                    field.removeItem(evt);
                    expect(field.value).toEqual(newValue);
                });
            });

        using('old current index, data-index, value, new current index list',
            [
                [0, 0, [{id: 'foo'}], 0],
                [1, 1, [{id: 'foo'}, {id: 'bar'}, {id: 'baz'}], 1],
                [1, 1, [{id: 'foo'}, {id: 'bar'}], 0]

            ],
            function(oldIdx, dataIdx, value, newIdx) {
                it('should decreases current index correctly', function() {
                    evt.currentTarget = '<a data-index=' + dataIdx + '></a>';
                    field._currentIndex = oldIdx;
                    field.value = value;
                    field.removeItem(evt);
                    expect(field._currentIndex).toEqual(newIdx);
                });
            });
    });

    describe('_updateModelAndTriggerChange()', function() {
        beforeEach(function() {
            field.model.set('selectedAddresses', {'foo': []});
        });

        it('should call render()', function() {
            var render = sandbox.stub(field, 'render');
            field._updateModelAndTriggerChange([]);
            expect(render).toHaveBeenCalled();
        });

        using('old current index, data-index, value, new current index list',
            [
                [[], {foo: []}],
                [[{key: 0, id: 'email1'}], {foo: ['email1']}],
                [[{key: 0, id: 'email1'}, {key: 1, id: 'email2'}], {foo: ['email1', 'email2']}]

            ],
            function(value, selectedAddresses) {
                it('should populate model\'s selectedAddresses', function() {
                    field._updateModelAndTriggerChange(value);
                    expect(field.model.get('selectedAddresses')).toEqual(selectedAddresses);
                });
            });
    });
});
