describe("Base.Layout.DupeCheck", function() {
    var app, defaultMeta, defaultListView,
        moduleName = 'Contacts';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate("dupecheck-header", "view", "base", "dupecheck-header");
        SugarTest.loadComponent("base", "view", "dupecheck-header");
        SugarTest.testMetadata.addViewDefinition('list', {
            "panels":[
                {
                    "name":"panel_header",
                    "fields":[
                        {
                            "name":"name",
                            "label":"",
                            "placeholder":"LBL_LIST_NAME"
                        },
                        {
                            "name":"status",
                            "label":"",
                            "placeholder":"LBL_LIST_STATUS"
                        }
                    ]
                }
            ]
        }, moduleName);
        SugarTest.loadHandlebarsTemplate("baselist", "view", "base", "baselist");
        SugarTest.loadComponent('base', 'view', 'baselist');
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent("base", "view", "dupecheck-list");
        SugarTest.testMetadata.set();
        defaultListView = "dupecheck-list";
        defaultMeta = {
            "type": "dupecheck",
            "components": [
                {"view":"dupecheck-header"},
                {"view":defaultListView, "name":"dupecheck-list"}
            ]
        };
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        SugarTest.testMetadata.dispose();
    });

    it("should have default list view type", function(){
        var layout = SugarTest.createLayout("base", moduleName, "dupecheck", defaultMeta);
        debugger;
        expect(layout._components[1].name).toEqual(defaultListView);
    });

    it("should be able to switch list view type", function(){
        var expectedListView, context, layout;

        //if you set dupelisttype on context, the list view will be overridden.
        expectedListView = 'dupecheck-list-select';
        context = app.context.getContext();
        context.set('dupelisttype', expectedListView);
        context.prepare();

        layout = SugarTest.createLayout("base", moduleName, "dupecheck", defaultMeta, context);
        expect(layout._components[1].name).toEqual(expectedListView);
    });

    it("should be calling the duplicate check api", function() {
        var loadDataStub, ajaxStub;

        var layout = SugarTest.createLayout("base", moduleName, "dupecheck", defaultMeta);
        loadDataStub = sinon.stub(layout.context, 'loadData', function(options) {
            options.endpoint(options, {'success':$.noop})
        })
        ajaxStub = sinon.stub($, 'ajax', $.noop)
        layout.loadData();

        expect(ajaxStub.lastCall.args[0].url).toMatch(/.*\/Contacts\/duplicateCheck/);
        loadDataStub.restore();
        ajaxStub.restore();
    });

});