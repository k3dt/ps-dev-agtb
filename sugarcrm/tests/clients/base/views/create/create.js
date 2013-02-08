describe("Create View", function() {
    var moduleName = 'Contacts',
        viewName = 'create',
        sinonSandbox, view, context,
        drawer;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'detail');
        SugarTest.loadComponent('base', 'view', 'editable');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.addViewDefinition('record', {
            "panels":[
                {
                    "name":"panel_header",
                    "placeholders":true,
                    "header":true,
                    "labels":false,
                    "fields":[
                        {
                            "name":"first_name",
                            "label":"",
                            "placeholder":"LBL_NAME"
                        },
                        {
                            "name":"last_name",
                            "label":"",
                            "placeholder":"LBL_NAME"
                        }
                    ]
                }, {
                    "name":"panel_body",
                    "columns":2,
                    "labels":false,
                    "labelsOnTop":true,
                    "placeholders":true,
                    "fields":[
                        "phone_work",
                        "email1",
                        "full_name"
                    ]
                }
            ]
        }, moduleName);
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.addViewDefinition(viewName, {
            "type":"record",
            "buttons":[
                {
                    "name":"cancel_button",
                    "type":"button",
                    "label":"LBL_CANCEL_BUTTON_LABEL",
                    "css_class":"btn-invisible btn-link"
                }, {
                    "name":"restore_button",
                    "type":"button",
                    "label":"LBL_RESTORE",
                    "css_class":"hide btn-invisible btn-link",
                    "showOn" : "select"
                }, {
                    "name":"save_create_button",
                    "type":"button",
                    "label":"LBL_SAVE_AND_CREATE_ANOTHER",
                    "css_class":"hide btn-invisible btn-link",
                    "showOn" : "create"
                }, {
                    "name":"save_view_button",
                    "type":"button",
                    "label":"LBL_SAVE_AND_VIEW",
                    "css_class":"hide btn-invisible btn-link",
                    "showOn" : "create"
                }, {
                    "name":"save_button",
                    "type":"button",
                    "label":"LBL_SAVE_BUTTON_LABEL",
                    "css_class":"btn-primary"
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        sinonSandbox = sinon.sandbox.create();

        drawer = SugarTest.app.drawer;
        SugarTest.app.drawer = {
            close: function(){}
        };

        context = SugarTest.app.context.getContext();
        context.set({
            module: moduleName,
            create: true
        });
        context.prepare();

        view = SugarTest.createView("base", moduleName, viewName, null, context);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        sinonSandbox.restore();
        SugarTest.app.drawer = drawer;
    });

    describe('Render', function() {
        it("Should render 5 buttons and 5 fields", function() {
            var fields = 0,
                buttons = 0;

            view.render();

            _.each(view.fields, function(field) {
                if (field.type === 'button') {
                    buttons++;
                } else {
                    fields++;
                }
            });

            expect(fields).toBe(5);
            expect(buttons).toBe(5);
        });
    });

    describe('Buttons', function() {
        it("Should hide the restore button when the form is empty", function() {
            view.render();

            expect(view.buttons[view.saveButtonName].getFieldElement().css('display')).not.toBe('none');
            expect(view.buttons[view.cancelButtonName].getFieldElement().css('display')).not.toBe('none');
            expect(view.buttons[view.saveAndCreateButtonName].getFieldElement().css('display')).not.toBe('none');
            expect(view.buttons[view.saveAndViewButtonName].getFieldElement().css('display')).not.toBe('none');
            expect(view.buttons[view.restoreButtonName].getFieldElement().css('display')).toBe('none');
        });

        it("Should hide all buttons except save and cancel when duplicates are found.", function() {
            var flag = false,
                checkForDuplicateStub = sinon.stub(view, 'checkForDuplicate', function(success, error) {
                    var data = {
                        "id":"f360b873-b11c-4f25-0a3e-50cb8e7ad0c2",
                        "first_name":"Foo",
                        "last_name":"Bar",
                        "phone_work":"1234567890",
                        "email1":"foobar@test.com",
                        "full_name":"Mr Foo Bar"
                    },
                        model = SugarTest.app.data.createBean(moduleName, data),
                        collection = SugarTest.app.data.createBeanCollection(moduleName, model);

                    checkForDuplicateStub.restore();
                    success(collection);
                }),
                handleDuplicateFoundStub = sinon.stub(view, 'handleDuplicateFound', function(collection) {
                    handleDuplicateFoundStub.restore();
                    view.handleDuplicateFound(collection);
                    flag = true;
                });

            runs(function() {
                view.render();
                view.model.set({
                    first_name: 'First',
                    last_name: 'Last'
                });
                view.buttons[view.saveButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'handleDuplicateFound should have been called but timeout expired', 1000);

            runs(function() {
                expect(view.buttons[view.saveButtonName].getFieldElement().css('display')).not.toBe('none');
                expect(view.buttons[view.saveButtonName].getFieldElement().text()).toBe('LBL_IGNORE_DUPLICATE_AND_SAVE');
                expect(view.buttons[view.cancelButtonName].getFieldElement().css('display')).not.toBe('none');
                expect(view.buttons[view.saveAndCreateButtonName].getFieldElement().css('display')).toBe('none');
                expect(view.buttons[view.saveAndViewButtonName].getFieldElement().css('display')).toBe('none');
                expect(view.buttons[view.restoreButtonName].getFieldElement().css('display')).toBe('none');
            });
        });

        it("Should show restore button, along with save and cancel, when a duplicate is selected to edit.", function() {
            var data = {
                "id":"f360b873-b11c-4f25-0a3e-50cb8e7ad0c2",
                "first_name":"Foo",
                "last_name":"Bar",
                "phone_work":"1234567890",
                "email1":"foobar@test.com",
                "full_name":"Mr Foo Bar"
            };

            view.render();
            view.model.set({
                first_name: 'First',
                last_name: 'Last'
            });
            view.context.trigger('list:dupecheck-list-select-edit:fire', SugarTest.app.data.createBean(moduleName, data));

            expect(view.buttons[view.saveButtonName].getFieldElement().css('display')).not.toBe('none');
            expect(view.buttons[view.saveButtonName].getFieldElement().text()).toBe('LBL_SAVE_BUTTON_LABEL');
            expect(view.buttons[view.cancelButtonName].getFieldElement().css('display')).not.toBe('none');
            expect(view.buttons[view.saveAndCreateButtonName].getFieldElement().css('display')).toBe('none');
            expect(view.buttons[view.saveAndViewButtonName].getFieldElement().css('display')).toBe('none');
            expect(view.buttons[view.restoreButtonName].getFieldElement().css('display')).not.toBe('none');
        });

        it("Should reset to the original form values when restore is clicked.", function() {
            var data = {
                "id":"f360b873-b11c-4f25-0a3e-50cb8e7ad0c2",
                "first_name":"Foo",
                "last_name":"Bar",
                "phone_work":"1234567890",
                "email1":"foobar@test.com",
                "full_name":"Mr Foo Bar"
            };

            view.render();
            view.model.set({
                first_name: 'First',
                last_name: 'Last'
            });
            view.context.trigger('list:dupecheck-list-select-edit:fire', SugarTest.app.data.createBean(moduleName, data));

            expect(view.model.get('first_name')).toBe('Foo');
            expect(view.model.get('last_name')).toBe('Bar');

            var render = sinon.stub(view, 'render', function() {
                return;
            });
            view.buttons[view.restoreButtonName].getFieldElement().click();

            expect(view.model.get('first_name')).toBe('First');
            expect(view.model.get('last_name')).toBe('Last');

            render.restore();
        });
    });

    describe('Save', function() {
        it("Should save data when save button is clicked, form data are valid, and no duplicates are found.", function() {
            var flag = false,
                isValidStub = sinon.stub(view.model, 'isValid', function() {
                    return true;
                }),
                checkForDuplicateStub = sinon.stub(view, 'checkForDuplicate', function(success, error) {
                    success(SugarTest.app.data.createBeanCollection(moduleName));
                }),
                saveModelStub = sinon.stub(view, 'saveModel', function() {
                    flag = true;
                });

            view.render();

            runs(function() {
                view.buttons[view.saveButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'Save should have been called but timeout expired', 1000);

            runs(function() {
                expect(isValidStub.calledOnce).toBe(true);
                expect(checkForDuplicateStub.calledOnce).toBe(true);
                expect(saveModelStub.calledOnce).toBe(true);

                saveModelStub.restore();
                isValidStub.restore();
                checkForDuplicateStub.restore();
            });
        });

        it("Should close drawer once save is complete", function() {
            var flag = false,
                isValidStub = sinon.stub(view.model, 'isValid', function() {
                    return true;
                }),
                checkForDuplicateStub = sinon.stub(view, 'checkForDuplicate', function(success, error) {
                    success(SugarTest.app.data.createBeanCollection(moduleName));
                }),
                saveModelStub = sinon.stub(view, 'saveModel', function(success) {
                    success();
                }),
                drawerCloseStub = sinon.stub(SugarTest.app.drawer, 'close', function() {
                    flag = true;
                    return;
                });

            view.render();

            runs(function() {
                view.buttons[view.saveButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'close should have been called but timeout expired', 1000);

            runs(function() {
                expect(drawerCloseStub.calledOnce).toBe(true);

                saveModelStub.restore();
                isValidStub.restore();
                checkForDuplicateStub.restore();
                drawerCloseStub.restore();
            });
        });

        it("Should not save data when save button is clicked but form data are invalid", function() {
            var flag = false,
                isValidStub = sinon.stub(view.model, 'isValid', function() {
                    flag = true;
                    return false;
                }),
                checkForDuplicateStub = sinon.stub(view, 'checkForDuplicate', function(success, error) {
                    success(SugarTest.app.data.createBeanCollection(moduleName));
                }),
                saveModelStub = sinon.stub(view, 'saveModel', function() {
                    return;
                });

            view.render();

            runs(function() {
                view.buttons[view.saveButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'isValid should have been called but timeout expired', 1000);

            runs(function() {
                expect(isValidStub.calledOnce).toBe(true);
                expect(checkForDuplicateStub.called).toBe(false);
                expect(saveModelStub.called).toBe(false);

                saveModelStub.restore();
                isValidStub.restore();
                checkForDuplicateStub.restore();
            });
        });

        it("Should not save data when save button is clicked but duplicates are found", function() {
            var flag = false,
                isValidStub = sinon.stub(view.model, 'isValid', function() {
                    return true;
                }),
                checkForDuplicateStub = sinon.stub(view, 'checkForDuplicate', function(success, error) {
                    flag = true;

                    var data = {
                        "id":"f360b873-b11c-4f25-0a3e-50cb8e7ad0c2",
                        "first_name":"Foo",
                        "last_name":"Bar",
                        "phone_work":"1234567890",
                        "email1":"foobar@test.com",
                        "full_name":"Mr Foo Bar"
                    },
                        model = SugarTest.app.data.createBean(moduleName, data),
                        collection = SugarTest.app.data.createBeanCollection(moduleName, model);

                    success(collection);
                }),
                saveModelStub = sinon.stub(view, 'saveModel', function() {
                    return;
                });

            view.render();

            runs(function() {
                view.buttons[view.saveButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'checkForDuplicate should have been called but timeout expired', 1000);

            runs(function() {
                expect(isValidStub.calledOnce).toBe(true);
                expect(checkForDuplicateStub.calledOnce).toBe(true);
                expect(saveModelStub.called).toBe(false);

                saveModelStub.restore();
                isValidStub.restore();
                checkForDuplicateStub.restore();
            });
        });
    });

    describe('Ignore Duplicate and Save', function() {
        it("Should save data and not run duplicate check when ignore duplicate and save button is clicked.", function() {
            var flag = false,
                isValidStub = sinon.stub(view.model, 'isValid', function() {
                    return true;
                }),
                checkForDuplicateStub = sinon.stub(view, 'checkForDuplicate', function(success, error) {
                    flag = true;

                    var data = {
                            "id":"f360b873-b11c-4f25-0a3e-50cb8e7ad0c2",
                            "first_name":"Foo",
                            "last_name":"Bar",
                            "phone_work":"1234567890",
                            "email1":"foobar@test.com",
                            "full_name":"Mr Foo Bar"
                        },
                        model = SugarTest.app.data.createBean(moduleName, data),
                        collection = SugarTest.app.data.createBeanCollection(moduleName, model);

                    success(collection);
                }),
                saveModelStub = sinon.stub(view, 'saveModel', function(success) {
                    success();
                }),
                drawerCloseStub = sinon.stub(SugarTest.app.drawer, 'close', function() {
                    flag = true;
                    return;
                });

            view.render();

            runs(function() {
                expect(view.skipDupeCheck()).toBe(false);
                view.buttons[view.saveButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'checkForDuplicate should have been called but timeout expired', 1000);

            runs(function() {
                flag = false;
                expect(view.skipDupeCheck()).toBe(true);
                view.buttons[view.saveButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'close should have been called but timeout expired', 1000);

            runs(function() {
                expect(isValidStub.calledTwice).toBe(true);
                expect(checkForDuplicateStub.calledOnce).toBe(true);
                expect(saveModelStub.calledOnce).toBe(true);
                expect(drawerCloseStub.calledOnce).toBe(true);

                saveModelStub.restore();
                isValidStub.restore();
                checkForDuplicateStub.restore();
                drawerCloseStub.restore();
            });
        });
    });

    describe('Save and Create Another', function() {
        it("Should save, clear out the form, but not close the drawer.", function() {
            var flag = false,
                isValidStub = sinon.stub(view.model, 'isValid', function() {
                    return true;
                }),
                checkForDuplicateStub = sinon.stub(view, 'checkForDuplicate', function(success, error) {
                    success(SugarTest.app.data.createBeanCollection(moduleName));
                }),
                saveModelStub = sinon.stub(view, 'saveModel', function(success) {
                    success();
                }),
                drawerCloseStub = sinon.stub(SugarTest.app.drawer, 'close', function() {
                    return;
                }),
                clearStub = sinon.stub(view.model, 'clear', function() {
                    flag = true;
                });

            view.render();
            view.model.set({
                first_name: 'First',
                last_name: 'Last'
            });

            runs(function() {
                view.buttons[view.saveAndCreateButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'clear should have been called but timeout expired', 1000);

            runs(function() {
                expect(saveModelStub.calledOnce).toBe(true);
                expect(drawerCloseStub.called).toBe(false);
                expect(clearStub.calledOnce).toBe(true);

                saveModelStub.restore();
                isValidStub.restore();
                checkForDuplicateStub.restore();
                drawerCloseStub.restore();
                clearStub.restore();
            });
        });
    });

    describe('Save and View', function() {
        it("Should save, close the modal, and navigate to the detail view.", function() {
            var flag = false,
                isValidStub = sinon.stub(view.model, 'isValid', function() {
                    return true;
                }),
                checkForDuplicateStub = sinon.stub(view, 'checkForDuplicate', function(success, error) {
                    success(SugarTest.app.data.createBeanCollection(moduleName));
                }),
                saveModelStub = sinon.stub(view, 'saveModel', function(success) {
                    success();
                }),
                navigateStub = sinon.stub(SugarTest.app, 'navigate', function() {
                    flag = true;
                });

            view.render();

            runs(function() {
                view.buttons[view.saveAndViewButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'navigate should have been called but timeout expired', 1000);

            runs(function() {
                expect(saveModelStub.calledOnce).toBe(true);
                expect(navigateStub.calledOnce).toBe(true);

                saveModelStub.restore();
                isValidStub.restore();
                checkForDuplicateStub.restore();
                navigateStub.restore();
            });
        });
    });
});