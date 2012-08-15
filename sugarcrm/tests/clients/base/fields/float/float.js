describe("sugarfields", function() {

    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","foo", "float", "detail",{
            round: 3,
            precision: 4,
            number_group_seperator: ",",
            decimal_seperator: "."
        });
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    describe("float", function() {
        it("should format the value", function() {
            expect(field.format("12351616461.2551616")).toEqual("12,351,616,461.2550");
            expect(field.unformat("12,351,616,461.2550")).toEqual("12351616461.2550");
        });

    });
});
