describe('Base.Field.Nested-set', function() {
    var module = 'KBSContents',
        fieldDef = {
            category_root: '76c5ad26-21db-1be5-85ee-54258f68dd4a',
            data_provider: 'Categories'
        },
        app, field, sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'nested-set');
        SugarTest.loadPlugin('NestedSetCollection');
        SugarTest.loadHandlebarsTemplate('nested-set', 'field', 'base', 'edit');
        SugarTest.testMetadata.set();

        app.data.declareModels();
        SugarTest.loadPlugin('JSTree');

        sinon.stub(_, 'defer', function() {
            var args = _.toArray(arguments),
                callback = args.shift();
            callback.apply(this, args);
        });
        field = SugarTest.createField('base', 'nested-set', 'nested-set', 'edit', fieldDef, module);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        _.defer.restore();
        Handlebars.templates = {};
        field.model = null;
        field._loadTemplate = null;
        field = null;
        delete app.plugins.plugins['field']['JSTree'];
        delete app.plugins.plugins['field']['NestedSetCollection'];
        sinonSandbox.restore();
    });

    it('Should render tree and togle icon on render only in edit mode.', function() {
        var treeRenderSpy = sinonSandbox.stub(field, '_renderTree');
        var toggleSearchIconSpy = sinonSandbox.spy(field, 'toggleSearchIcon');

        field.action = 'record';
        field.render();

        expect(treeRenderSpy).not.toHaveBeenCalled();
        expect(toggleSearchIconSpy).not.toHaveBeenCalled();

        field.action = 'edit';
        field.render();

        expect(treeRenderSpy).toHaveBeenCalled();
        expect(toggleSearchIconSpy).toHaveBeenCalled();
    });

    it('Should show dropdown.', function() {
        var aSel = '[data-action=create-new]',
            iSel = '[data-role=add-item]',
            dSel = '[data-role=treeinput]',
            expected = {
                records: SugarTest.loadFixture('tree', '../tests/modules/Categories/fixtures'),
                next_offset: -1
            },
            clearSelectionSpy = sinonSandbox.spy(field, 'clearSelection');
        expected = JSON.stringify(expected);
        field.action = 'edit';
        SugarTest.seedFakeServer();
        SugarTest.server.respondWith('GET', new RegExp(".*rest\/v10\/Categories\/76c5ad26-21db-1be5-85ee-54258f68dd4a\/tree.*"),
            [200, {'Content-Type': 'application/json'}, expected]);
        field.render();
        field.$treeContainer.jstree = function () {return this;};
        SugarTest.server.respond();

        field.$(dSel).click();
        expect(field.$(field.ddEl).length).not.toBe(0);
        expect(field.$(field.ddEl).data('dropdown').opened).toBeTruthy();
        document.body.click();
        expect(field.$(field.ddEl).data('dropdown').opened).toBeFalsy();
        expect(clearSelectionSpy).toHaveBeenCalled();
    });

    it('Should show input for creation.', function() {
        var aSel = '[data-action=create-new]',
            iSel = '[data-role=add-item]',
            dSel = '[data-role=treeinput]';
        sinonSandbox.stub(field, '_renderTree', function () {});
        field.action = 'edit';
        field.render();
        field.$(dSel).click();
        field.$(aSel).click();
        expect(field.$(iSel).length).not.toBe(0);
        expect(field.$(aSel).css('display')).toBe('none');
        field.switchCreate();
        expect(field.$(iSel).length).toBe(0);
        expect(field.$(aSel).css('display')).not.toBe('none');
    });

});
