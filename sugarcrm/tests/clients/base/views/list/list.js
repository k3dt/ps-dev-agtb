describe("sugarviews", function() {
    var view, layout, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate("list", 'view', 'base');
        SugarTest.testMetadata.addViewDefinition("list", {
            "panels": [{
                "name": "panel_header",
                "header": true,
                "fields": ["name", "case_number","type","created_by","date_entered","date_modified","modified_user_id"]
            }]
        }, "Cases");
        SugarTest.testMetadata.set();
        //SugarTest.app.data.declareModels();
        view = SugarTest.createView("base", "Cases", "list", null, null);
        layout = SugarTest.createLayout('base', "Cases", "list", null, null);
        view.layout = layout;
        app = SUGAR.App;
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe("list",function() {
        it('should open an alert message on sort', function() {
            view.render();
            var ajaxStub = sinon.stub(app.api, 'call');
            var alertStub = sinon.stub(app.alert, 'show');
            view.setOrderBy({target:'[data-fieldname=case_number]'});
            expect(alertStub).toHaveBeenCalled();
            alertStub.restore();
            ajaxStub.restore();
        });
        it('should be able to remove hidden default=false fields from meta', function() {
            var viewMeta = {
                panels: [
                    {
                        fields: [
                            {
                                name: 'test1',
                                default: false
                            },
                            {
                                name: 'test2',
                                default: false
                            }
                        ]
                    },
                    {
                        fields: [
                            {
                                name: 'test3',
                                default: true
                            },
                            {
                                name: 'test4',
                                default: false
                            }
                        ]
                    }
                ]
            };

            var resultMeta = view.filterFields(viewMeta);

            _.each(resultMeta, function(panel){
                _.each(panel.fields, function(field){
                    expect(field.default).toEqual(true);
                })
            });
        });
    });
});