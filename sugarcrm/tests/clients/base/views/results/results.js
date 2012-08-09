describe("Results View", function() {

    var app, spyContextGet, context, view;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('query', 'fubar');
        view = SugarTest.createView("base","Cases", "results", null, context);
        view.model = new Backbone.Model();
        spyContextGet = sinon.spy(context, 'get');
        view.collection = new app.MixedBeanCollection();
    });
    
    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        context.get.restore();
        delete Handlebars.templates;
        view = null;
    });

    it("should initialize with custom meta", function() {
        expect(view.meta).toBeDefined();
    });
    it("should get last query from context", function() {
        // Prevent http request from going out
        var stub = sinon.stub(jQuery, 'ajax');
        view.render();
        stub.restore();
        expect(spyContextGet).toHaveBeenCalled();
        expect(spyContextGet.lastCall.args[0]).toEqual("query");
    });
    it("should call update collection and render subnav if search api returns records", function() {
        var stubFireSearch, stubRenderSubnav;

        stubFireSearch = sinon.stub(view, "fireSearchRequest", function(cb) {
            cb({next_offset: 1,
                    records: [{
                    id: "824ac1ce-8ef2-1c42-7d5e-4fc193416db2",
                    name: "System not responding"}]});
        });

        stubRenderSubnav = sinon.stub(view, 'renderSubnav', function(){});
        view.render();
        expect(stubRenderSubnav).toHaveBeenCalled();
    });
    it("should NOT call update collection but should call render subnav if search api returns no data", function() {
        var stubFireSearch, stubRenderSubnav;
        stubFireSearch = sinon.stub(view, "fireSearchRequest", function(cb) {
            cb({next_offset: -1, records: null});
        });
        stubRenderSubnav     = sinon.stub(view, 'renderSubnav', function(){});

        view.render();
        expect(stubRenderSubnav).toHaveBeenCalled();
        expect(stubRenderSubnav.args[0][0]).toMatch(/No.?results.*/i);
    });

    it("should use mixed collection for showMoreResults", function() {
        var stubCtxGet, localContext, localView, spyPaginate; 
        spyPaginate = sinon.spy(app.bean, 'paginate', function() {});
        localContext     = app.context.getContext();
        localContext.get = function() {};
        stubCtxGet   = sinon.stub(localContext, 'get', function() { return {paginate: spyPaginate}; });
        localView        = new app.view.views.ResultsView({context: localContext});
        localView.showMoreResults();
        expect(spyPaginate.args[0][0].add).toEqual(true);
        expect(spyPaginate.args[0][0].success).toBeDefined(true);
    });
});
