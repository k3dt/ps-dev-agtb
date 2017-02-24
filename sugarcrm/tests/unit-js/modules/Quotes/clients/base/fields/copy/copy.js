describe('Quotes.Base.Fields.CopyField', function() {
    var app;
    var model;
    var field;
    var fieldDef;
    var view;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;

        model = new Backbone.Model({
            id: 'not-new',
            billing_account_name: 'Billing Account Name'
        });

        fieldDef = {
            mapping: {
                billing_account_name: 'shipping_account_name'
            }
        };

        layout = SugarTest.createLayout('base', 'Quotes', 'record', {});
        view = SugarTest.createView('base', 'Quotes', 'record', null, null, true, layout);
        field = SugarTest.createField('base', 'copy', 'copy', 'edit', fieldDef, 'Quotes', model, null, true);
        sinon.collection.stub(field, '_super', function() {});
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        model = null;
        sinon.collection.restore();
        field.dispose();
        field = null;
        view.dispose();
        view = null;
        layout.dispose();
        layout = null;
    });

    describe('initialize()', function() {
        var initOptions;

        beforeEach(function() {
            initOptions = {
                def: fieldDef,
                model: model
            };
        });

        it('should set isConvertingFromShipping true when view is shipping', function() {
            field.dispose();
            view.isConvertFromShippingOrBilling = 'shipping';
            field.view = view;
            field.initialize(initOptions);

            expect(field.isConvertingFromShipping).toBeTruthy();
        });

        it('should set isConvertingFromShipping false when view is not shipping', function() {
            field.dispose();
            view.isConvertFromShippingOrBilling = 'billing';
            field.view = view;
            field.initialize(initOptions);

            expect(field.isConvertingFromShipping).toBeFalsy();
        });
    });

    describe('sync()', function() {
        var shippingField;
        var shippingFieldSetDisabledSpy;

        beforeEach(function() {
            shippingFieldSetDisabledSpy = sinon.collection.spy();
            shippingField = {
                setDisabled: shippingFieldSetDisabledSpy
            };
            field.getField = function() {
                return shippingField;
            };
        });

        afterEach(function() {
            delete field.getField;
            shippingField = null;
            shippingFieldSetDisabledSpy = null;
        });

        describe('when converting from shipping', function() {
            beforeEach(function() {
                field.isConvertingFromShipping = true;
            });

            it('should call setDisabled on shipping field when copy not checked', function() {
                sinon.collection.stub(field, '_isChecked', function() {
                    return false;
                });
                field.sync(true);

                expect(shippingFieldSetDisabledSpy).toHaveBeenCalledWith(false);
            });

            it('should call setDisabled on shipping field when copy is checked', function() {
                sinon.collection.stub(field, '_isChecked', function() {
                    return true;
                });
                field.sync(true);

                expect(shippingFieldSetDisabledSpy).not.toHaveBeenCalled();
            });
        });

        describe('when not converting from shipping', function() {
            beforeEach(function() {
                field.isConvertingFromShipping = false;
            });

            it('should call setDisabled on shipping field when copy not checked', function() {
                sinon.collection.stub(field, '_isChecked', function() {
                    return false;
                });
                field.sync(true);

                expect(shippingFieldSetDisabledSpy).not.toHaveBeenCalled();
            });

            it('should call setDisabled on shipping field when copy is checked', function() {
                sinon.collection.stub(field, '_isChecked', function() {
                    return true;
                });
                field.sync(true);

                expect(shippingFieldSetDisabledSpy).not.toHaveBeenCalled();
            });
        });
    });

    describe('toggle()', function() {
        beforeEach(function() {
            sinon.collection.stub(field, 'sync', function() {});
            sinon.collection.stub(field, '_isChecked', function() {
                return 'yup';
            });
            field.toggle();
        });

        it('should call field sync with the value from _isChecked', function() {
            expect(field.sync).toHaveBeenCalledWith('yup');
        });

        it('should call field _isChecked', function() {
            expect(field._isChecked).toHaveBeenCalled();
        });
    });

    describe('_isChecked()', function() {
        var $fieldTag;

        beforeEach(function() {
            field.model.set('copy', 'modelValue');
        });

        afterEach(function() {
            $fieldTag = null;
        });

        describe('when $fieldTag is defined', function() {
            it('should return true when $fieldTag is checked', function() {
                $fieldTag = {
                    is: function() {
                        return true;
                    }
                };
                field.$fieldTag = $fieldTag;

                expect(field._isChecked()).toBeTruthy();
            });

            it('should return true when $fieldTag is not checked', function() {
                $fieldTag = {
                    is: function() {
                        return false;
                    }
                };
                field.$fieldTag = $fieldTag;

                expect(field._isChecked()).toBeFalsy();
            });
        });

        describe('when $fieldTag is not defined', function() {
            it('should return the model value for the field', function() {
                field.$fieldTag = undefined;

                expect(field._isChecked()).toBe('modelValue');
            });
        });
    });

    describe('syncCopy()', function() {
        it('should call _super syncCopy when isConvertingFromShipping is false', function() {
            field.isConvertingFromShipping = false;
            field.syncCopy(true);

            expect(field._super).toHaveBeenCalledWith('syncCopy', [true]);
        });

        it('should call _super syncCopy when isConvertingFromShipping and _isChecked are both true', function() {
            field.isConvertingFromShipping = true;
            sinon.collection.stub(field, '_isChecked', function() {
                return true;
            });
            field.syncCopy(true);

            expect(field._super).toHaveBeenCalledWith('syncCopy', [true]);
        });

        it('should not call _super syncCopy when isConvertingFromShipping is true and _isChecked is false', function() {
            field.isConvertingFromShipping = true;
            sinon.collection.stub(field, '_isChecked', function() {
                return false;
            });
            field.syncCopy(true);

            expect(field._super).not.toHaveBeenCalled();
        });

        it('should set _inSync = false when isConvertingFromShipping is false', function() {
            field.isConvertingFromShipping = true;
            field.syncCopy(true);

            expect(field._inSync).toBeFalsy();
        });

        it('should call field.model.off if isConvertingFromShipping and enable are false', function() {
            field.isConvertingFromShipping = true;
            sinon.collection.spy(field.model, 'off');
            field.syncCopy(false);

            expect(field.model.off).toHaveBeenCalled();
        });
    });
});
