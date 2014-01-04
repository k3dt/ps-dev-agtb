describe("Module List", function() {
    var moduleName = 'Cases',
        viewName = 'modulelist',
        app,
        view,
        backupIsSynced;

    var $newLink = function(module, route) {
        return $('<a href="#' + module + '" data-route="' + (route ? '#' + module : '') + '">' + module + '</a>');
    };
    var $newTab = function(module, route) {
        return $('<li data-module="' + module + '">' + $newLink(module, route) + '</li>');
    };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'favorites');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();

        // Fake app is synced
        backupIsSynced = app.isSynced;
        app.isSynced = true;

        view = SugarTest.createView("base", moduleName, "modulelist", null, null);
    });

    afterEach(function() {
        app.isSynced = backupIsSynced;
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
    });

    describe('Render', function() {
        var isAuthenticatedStub, getModuleNamesStub, modStrings;

        beforeEach(function() {

            isAuthenticatedStub = sinon.stub(SugarTest.app.api, 'isAuthenticated', function() {
                return true;
            });
            getModuleNamesStub = sinon.stub(SugarTest.app.metadata, 'getModuleNames', function() {
                return {
                    Accounts: 'Accounts',
                    Bugs: 'Bugs',
                    Calendar: 'Calendar',
                    Calls: 'Calls',
                    Campaigns: 'Campaigns',
                    Cases: 'Cases',
                    Contacts: 'Contacts',
                    Forecasts: 'Forecasts',
                    Home: 'Home',
                    Opportunities: 'Opportunities',
                    Prospects: 'Prospects',
                    Reports: 'Reports',
                    Tasks: 'Tasks'
                };
            });
            modStrings = sinon.stub(SugarTest.app.metadata, 'getStrings', function() {
                return {
                    Accounts: {}
                };
            });

        });

        afterEach(function() {
            view.dispose();
            modStrings.restore();
            isAuthenticatedStub.restore();
            getModuleNamesStub.restore();
        });

        it("Should display all the modules in the module list metadata", function() {
            var modules = SugarTest.app.metadata.getModuleNames();
            view.render();
            _.each(modules, function(module, key) {
                expect(view.$el.find("[data-module='" + module+"']").length).not.toBe(0);
            });
        });

        it("Should select Cases module to be currently active module", function() {
            var getModuleStub = sinon.stub(SugarTest.app.controller.context, 'get', function() {
                return moduleName;
            });

            view.activeModule._moduleList = view;
            view.render();

            expect(view.activeModule.isActive(view.$el.find("[data-module='" + moduleName+"']"))).toBe(true);

            getModuleStub.restore();
        });

        it("Should know that Contacts module is next to the Cases module", function() {
            var getModuleStub = sinon.stub(SugarTest.app.controller.context, 'get', function() {
                return moduleName;
            });

            view.activeModule._moduleList = view;
            view.render();

            expect(view.activeModule.isNext(view.$el.find("[data-module='Contacts']"))).toBe(true);

            getModuleStub.restore();
        });
        describe('getRecentlyViewedAndFavoriteRecords', function() {
            var $fakeHtml, evt, module, meta, stubs;
            beforeEach(function() {
                $fakeHtml = $('<div class="moduleHolder"><div><div class="event"></div></div></div>');
                evt = {
                    currentTarget: $fakeHtml.find('.event')
                };
                stubs = {
                    metadata: sinon.stub(app.metadata, 'getModule', function() {
                        return meta;
                    }),
                    populateDashboards: sinon.stub(view, 'populateDashboards'),
                    populateFavorites: sinon.stub(view, 'populateFavorites'),
                    populateRecents: sinon.stub(view, 'populateRecents')
                };
            });
            afterEach(function() {
                stubs.metadata.restore();
                stubs.populateDashboards.restore();
                stubs.populateFavorites.restore();
                stubs.populateRecents.restore();
            });
            it('should populate dashboard if module is Home', function() {
                $fakeHtml.data('module', 'Home');
                view.getRecentlyViewedAndFavoriteRecords(evt);
                expect(stubs.populateDashboards).toHaveBeenCalled();
                expect(stubs.populateFavorites).not.toHaveBeenCalled();
                expect(stubs.populateRecents).not.toHaveBeenCalled();

            });
            it('should populate recents only because favorites are disabled', function() {
                meta = { fields: { _hash: 'meta_hash', account_type: {} } };
                $fakeHtml.data('module', 'Accounts');
                view.getRecentlyViewedAndFavoriteRecords(evt);
                expect(stubs.populateDashboards).not.toHaveBeenCalled();
                expect(stubs.populateFavorites).not.toHaveBeenCalled();
                expect(stubs.populateRecents).toHaveBeenCalled();
            });
            it('should populate favorites because favorites are enabled', function() {
                meta = { favoritesEnabled: true, fields: { _hash: 'meta_hash', account_type: {} } };
                $fakeHtml.data('module', 'Accounts');
                view.getRecentlyViewedAndFavoriteRecords(evt);
                expect(stubs.populateDashboards).not.toHaveBeenCalled();
                expect(stubs.populateFavorites).toHaveBeenCalled();
                expect(stubs.populateRecents).toHaveBeenCalled();
            });
            it('should not populate because this module does not have fields (ex: Calendar)', function() {
                meta = { favoritesEnabled: true, fields: { _hash: 'meta_hash' } };
                $fakeHtml.data('module', 'Calendar');
                view.getRecentlyViewedAndFavoriteRecords(evt);
                expect(stubs.populateDashboards).not.toHaveBeenCalled();
                expect(stubs.populateFavorites).not.toHaveBeenCalled();
                expect(stubs.populateRecents).not.toHaveBeenCalled();
            });
        });
        it("Should populate favorites and call favorite populate callback", function() {
            var cbMock = sinon.mock();
            var module = 'Accounts';
            var beanCreateMock = sinon.stub(SugarTest.app.data,'createBeanCollection', function(module, models) {
                var collection = new Backbone.Collection(models);
                return collection;
            });
            // Workaround because router not defined yet
            var oRouter = SugarTest.app.router;
            SugarTest.app.router = {buildRoute: function(){}};
            sinon.stub(SugarTest.app.router,'buildRoute',function(){
                    return 'testRouteString';
                }
            );
            var apiStub = sinon.stub(SugarTest.app.api, 'call', function(){
                if(arguments){
                    arguments[3].success.call(view, {
                        records: [
                            new Backbone.Model({
                                id:'model1',
                                name:'model1'
                            }),
                            new Backbone.Model({
                                id:'model2',
                                name:'model2'
                            })
                        ]
                    });
                }
            });
            var getModuleStub = sinon.stub(SugarTest.app.metadata, 'getModule', function(key) {
                var data = {
                    Accounts: {
                        menu: {
                            header: {
                                meta: {
                                    acl_action: "create",
                                    acl_module: "Accounts",
                                    icon: "icon-plus",
                                    label: "LNK_NEW_ACCOUNT",
                                    route: "#Accounts/create"
                                }
                            }
                        }
                    }
                }
                return data[key];
            });

            view.activeModule._moduleList = view;
            view.render();

            view.populateFavorites(module, cbMock);
            expect(apiStub).toHaveBeenCalled();
            expect(cbMock).toHaveBeenCalled();
            expect(view.$el.find("[data-module='Accounts']").find('.favoritesContainer').find('li').length).toEqual(2);
            beanCreateMock.restore();
            apiStub.restore();
            SugarTest.app.router = oRouter;
            getModuleStub.restore();
        });
        it("Should populate Recents and call recents populate callback", function() {
            var cbMock = sinon.mock();
            var module = 'Accounts';
            var beanCreateMock = sinon.stub(SugarTest.app.data,'createBeanCollection', function(module, models) {
                var collection = new Backbone.Collection(models);
                return collection;
            });
            // Workaround because router not defined yet
            var oRouter = SugarTest.app.router;
            SugarTest.app.router = {buildRoute: function(){}};
            sinon.stub(SugarTest.app.router,'buildRoute',function(){
                    return 'testRouteString';
                }
            );
            var getModuleStub = sinon.stub(SugarTest.app.metadata, 'getModule', function(key) {
                var data = {
                    Accounts: {
                        menu: {
                            header: {
                                meta: {
                                    acl_action: "create",
                                    acl_module: "Accounts",
                                    icon: "icon-plus",
                                    label: "LNK_NEW_ACCOUNT",
                                    route: "#Accounts/create"
                                }
                            }
                        }
                    }
                }
                return data[key];
            });
            view.activeModule._moduleList = view;
            view.render();

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith("POST", /.*\/Accounts.*/,
                [200, {  "Content-Type": "application/json"},
                    JSON.stringify( {
                            records: [
                                new Backbone.Model({
                                    id:'model1',
                                    name:'model1'
                                }),
                                new Backbone.Model({
                                    id:'model2',
                                    name:'model2'
                                })
                            ]
                        }
                    )]);

            view.populateRecents(module, cbMock);
            SugarTest.server.respond();
            expect(cbMock).toHaveBeenCalled();
            expect(view.$el.find("[data-module='Accounts']").find('.recentContainer').find('li').length).toEqual(2);
            beanCreateMock.restore();
            SugarTest.app.router = oRouter;
            getModuleStub.restore();
        });
        it("Should be able to filter menu items by acl", function() {
            sinon.stub(SugarTest.app.acl, 'hasAccess', function(action,module) {
                if (module == 'noAccess' || action =='edit') {
                    return false;
                } else {
                    return true;
                }
            });
            var meta = [
                {
                    label: 'blah',
                    acl_action: 'edit',
                    module:'test'
                },
                {
                    label: 'blah',
                    acl_action: 'edit',
                    module:'noAccess'
                },
                {
                    label: 'blah',
                    acl_action: 'read',
                    module:'testModule'
                }
            ];
            var result = view.filterAvailableMenuActions(meta);
            meta.shift();
            meta.shift();
            expect(result).toEqual(meta);
            SugarTest.app.acl.hasAccess.restore();
        });
        it("should trigger data event on click of action links", function() {
            var cbspy = sinon.spy();

            SugarTest.app.events.register("sugar:app:testEvent", view);
            SugarTest.app.events.on("sugar:app:testEvent", cbspy, view);
            var testEl = $('<li class="dropdown" data-module="testModule"><div><div><div><div data-event="sugar:app:testEvent"></div></div></div></div></li>');
            view.$el.append(testEl);
            var event = {
                currentTarget:testEl.find('[data-event=\'sugar:app:testEvent\']')
            };
            view.handleMenuEvent(event);
            expect(cbspy).toHaveBeenCalled();
            expect(cbspy).toHaveBeenCalledWith("testModule", event);
            SugarTest.app.events.unregister(view,"sugar:app:testEvent");
        });
    });

    describe("handle data route events", function() {
        var refreshStub,
            navigateStub,
            oRouter;

        beforeEach(function() {
            // Workaround because router not defined yet
            oRouter              = SugarTest.app.router;
            SugarTest.app.router = {navigate: function() {}, refresh: function() {}};

            refreshStub = sinon.stub(SugarTest.app.router, "refresh");
            navigateStub = sinon.stub(SugarTest.app.router, "navigate");
        });

        afterEach(function() {
            SugarTest.app.router = oRouter;
            view.dispose();
            refreshStub.restore();
            navigateStub.restore();
        });

        it("should not call navigate or loadUrl when data-route is empty", function() {
            var link = $newLink('Contacts', false);

            view.$el.append(link);
            link.click();

            expect(refreshStub).not.toHaveBeenCalled();
            expect(navigateStub).not.toHaveBeenCalled();
        });

        it("should call loadUrl when data-route matches the current route", function() {
            var link = $newLink('Contacts', true),
                getFragmentStub = sinon.stub(Backbone.history, "getFragment", function() {
                    return "Contacts";
                });

            view.$el.append(link);
            link.click();

            expect(refreshStub).toHaveBeenCalled();
            expect(navigateStub).not.toHaveBeenCalled();

            getFragmentStub.restore();
            refreshStub.restore();
        });

        it("should call navigate when data-route is a new route", function() {
            var link = $newLink('Contacts', true),
                getFragmentStub = sinon.stub(Backbone.history, "getFragment", function() {
                    return "Accounts";
                });

            view.$el.append(link);
            link.click();

            expect(refreshStub).not.toHaveBeenCalled();
            expect(navigateStub).toHaveBeenCalled();

            getFragmentStub.restore();
        });
    });

    describe('activeModule.set', function() {
        var resetStub, getFullModuleListStub, getModuleTabMapStub, completeMenuMetaStub, templateStub;
        beforeEach(function() {
            view.$el.append($('<ul id="module_list"></ul>'));
            // Add Accounts tab
            view.$('#module_list').append($newTab('Contacts', true));
            resetStub = sinon.stub(view.activeModule, 'reset');
            getFullModuleListStub = sinon.stub(app.metadata, 'getFullModuleList', function() {
                return {
                    'Contacts': 'Contacts',
                    'Leads': 'Leads',
                    'ReportMaker': 'ReportMaker',
                    'CustomQueries': 'CustomQueries'
                };
            });
            getModuleTabMapStub = sinon.stub(app.metadata, 'getModuleTabMap', function() {
                return {
                    'Leads': 'Contacts',
                    'ReportMaker': 'ReportMaker',
                    'CustomQueries': 'ReportMaker'
                };
            });
            completeMenuMetaStub = sinon.spy(view.activeModule._moduleList, 'completeMenuMeta');
            templateStub = sinon.stub(app.template, 'get', function() { return function() {}; });
        });
        afterEach(function() {
            resetStub.restore();
            getFullModuleListStub.restore();
            getModuleTabMapStub.restore();
            completeMenuMetaStub.restore();
            templateStub.restore();
        });
        it('should select Contacts tab because it exists', function() {
            view.activeModule.set('Contacts');
            expect(getModuleTabMapStub).not.toHaveBeenCalled();
            expect(completeMenuMetaStub).not.toHaveBeenCalled();
        });
        it('should select Contacts tab because Leads tab does not exist', function() {
            view.activeModule.set('Leads');
            expect(getModuleTabMapStub).toHaveBeenCalled();
            expect(completeMenuMetaStub).not.toHaveBeenCalled();
        });
        it('should create ReportMaker tab because it does not exist', function() {
            view.activeModule.set('ReportMaker');
            expect(getModuleTabMapStub).toHaveBeenCalled();
            expect(completeMenuMetaStub).toHaveBeenCalled();
            expect(completeMenuMetaStub).toHaveBeenCalledWith({ReportMaker: 'ReportMaker'});
            expect(templateStub).toHaveBeenCalled();
        });
        it('should not create ReportMaker because updateNav = false (property from the metadata)', function() {
            var _oLayout = app.controller.layout;
            //Fake layout
            app.controller.layout = new Backbone.View();
            app.controller.layout.meta = { updateNav: false };
            view.activeModule.set('ReportMaker');
            expect(getModuleTabMapStub).toHaveBeenCalled();
            expect(completeMenuMetaStub).not.toHaveBeenCalled();
            expect(templateStub).not.toHaveBeenCalled();
            app.controller.layout = _oLayout;
        });
        it('should create ReportMaker tab even because it is mapped module though the tab does not exist', function() {
            view.activeModule.set('CustomQueries');
            expect(getModuleTabMapStub).toHaveBeenCalled();
            expect(completeMenuMetaStub).toHaveBeenCalled();
            expect(completeMenuMetaStub).toHaveBeenCalledWith({ReportMaker: 'ReportMaker'});
            expect(templateStub).toHaveBeenCalled();
        });
    });
});
