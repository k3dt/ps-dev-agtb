describe("Base.Fields.Float", function() {

    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base", "foo", "float", "detail", {
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

    it("should format the value", function() {
        expect(field.format("12351616461.2551616")).toEqual("12,351,616,461.2550");
        expect(field.unformat("12,351,616,461.2550")).toEqual("12351616461.2550");
    });

    it("should truncate to specified precision", function() {
        var defaultField = SugarTest.createField("base", "foo", "float", "detail", {
            precision: 8
        });
        expect(defaultField.format("123456.123456789")).toEqual("123456.12345679");
        expect(defaultField.format("123456.987654321")).toEqual("123456.98765432");
    });

    it("should format zero", function() {
        expect(field.format(0.00)).toEqual('0.0000');
    });

    it("should not format a non number string", function() {
        expect(field.format("Asdt")).toBeUndefined();
    });
});
