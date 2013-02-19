describe('Base.Field.Rowaction', function() {

    var app, field, view, moduleName = 'Contacts';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        field = SugarTest.createField("base","rowaction", "rowaction", "edit", {
            'type':'rowaction',
            'css_class':'btn',
            'tooltip':'LBL_PREVIEW',
            'event':'list:preview:fire',
            'icon':'icon-eye-open',
            'value':'view'
        }, moduleName);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    it('should render action if the user has acls', function() {
        field.model._acl = { "view": "yes" };
        var stub_render = sinon.stub(app.view.fields.ButtonField.prototype, "_render");
        field.module = moduleName;
        field._render();
        expect(stub_render).toHaveBeenCalled();
        stub_render.restore();
    });

    it('should hide action if the user doesn\'t have acls', function() {
        field.model = app.data.createBean(moduleName);
        field.model._acl = { "view": "no" };
        field._render();
        expect(field.isHidden).toBeTruthy();
    });
});
