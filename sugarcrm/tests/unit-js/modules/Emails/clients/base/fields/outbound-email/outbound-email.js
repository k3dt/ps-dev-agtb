describe('Emails.BaseEmailOutboundEmailField', function() {
    var app;
    var context;
    var field;
    var model;
    var sandbox;

    beforeEach(function() {
        sandbox = sinon.sandbox.create();

        SugarTest.testMetadata.init();
        SugarTest.declareData('base', 'Emails', true, false);
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'edit');
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.loadComponent('base', 'field', 'outbound-email', 'Emails');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');

        field = SugarTest.createField({
            name: 'outbound_email_id',
            type: 'outbound-email',
            viewName: 'edit',
            module: 'Emails',
            model: model,
            context: context,
            loadFromModule: true
        });
    });

    afterEach(function() {
        sandbox.restore();

        field.dispose();
        app.cache.cutAll();
        app.view.reset();

        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    it('should show a warning to the user when a not_authorized error is returned', function() {
        var callback = sandbox.spy();
        var onError = sandbox.spy();
        var error = {
            status: 403,
            code: 'not_authorized',
            message: 'You are not authorized to perform this action.'
        };

        sandbox.stub(app.api, 'enumOptions', function(module, field, callbacks) {
            callbacks.error(error);
            callbacks.complete();
        });
        sandbox.stub(app.api, 'defaultErrorHandler');
        sandbox.stub(app.lang, 'get').withArgs('LBL_NO_DATA', field.module).returns('No Data');
        sandbox.stub(app.alert, 'show', function(key, options) {
            expect(key).toBe('email-client-status');
            expect(options.level).toBe('warning');
            expect(options.autoClose).toBe(false);
        });
        sandbox.stub(field.view, 'trigger').withArgs('email_not_configured', error);

        field.loadEnumOptions(true, callback, onError);

        expect(callback).toHaveBeenCalledOnce();
        expect(onError).toHaveBeenCalledWith(error);
        expect(app.api.defaultErrorHandler).toHaveBeenCalledWith(error);
        expect(_.size(field.items)).toBe(1);
        expect(field.items['']).toBe('No Data');
        expect(app.alert.show).toHaveBeenCalledOnce();
        expect(field.view.trigger).toHaveBeenCalledOnce();
    });
});