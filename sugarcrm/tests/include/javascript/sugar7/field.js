describe('Sugar7 field extensions', function () {
    var app,
        field;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
    });

    afterEach(function () {
        SugarTest.testMetadata.dispose();
        if (field) {
            field.dispose();
        }
        field = null;
    });

    describe('decorating required fields', function () {

        it("should call decorateRequired only on required fields on edit mode", function () {
            field = SugarTest.createField("base", "description", "base", "edit", {required: true});
            var spy = sinon.spy(field, 'decorateRequired');
            field.render();
            expect(spy.called).toBe(true);
            spy.reset();
            field.dispose();

            field = SugarTest.createField("base", "description", "base", "edit");
            field.render();
            expect(spy.called).toBe(false);
            spy.reset();
            field.dispose();

            field = SugarTest.createField("base", "description", "base", "detail", {required: true});
            field.render();
            expect(spy.called).toBe(false);
            spy.restore();
        });

        it("should call clearRequiredLabel prior to calling decorateRequired on a field", function () {
            field = SugarTest.createField("base", "description", "base", "edit", {required: true});
            var clearSpy = sinon.spy(field, 'clearRequiredLabel');
            var reqSpy = sinon.spy(field, 'decorateRequired');
            field.render();
            expect(clearSpy.called).toBe(true);
            expect(reqSpy.called).toBe(true);
            expect(clearSpy.calledBefore(reqSpy)).toBe(true);

            clearSpy.restore();
            reqSpy.restore();
        });

        it("should allow a way to opt-out of calling decorateRequired so Required placeholder", function () {
            field = SugarTest.createField("base", "text", "base", "edit", {required: true});
            field.def.no_required_placeholder = true;
            var should = field._shouldRenderRequiredPlaceholder();
            expect(should).toBeFalsy();
            field.def.no_required_placeholder = undefined;
            should = field._shouldRenderRequiredPlaceholder();
            expect(should).toBeTruthy();
        });
    });

    describe('Edit mode css class', function () {
        var editClass = 'edit';
        var detailClass = 'detail';

        it('should render in detail mode without the edit class', function () {
            field = SugarTest.createField("base", "description", "base", "detail");
            field.render();
            expect(field.getFieldElement().hasClass(editClass)).toBeFalsy();
            expect(field.getFieldElement().hasClass(detailClass)).toBeTruthy();
        });

        it('should render in edit mode with edit class', function () {
            field = SugarTest.createField("base", "description", "base", "edit");
            field.render();
            expect(field.getFieldElement().hasClass(editClass)).toBeTruthy();
            expect(field.getFieldElement().hasClass(detailClass)).toBeFalsy();
        });

        it('should add the edit class when toggled to edit mode', function () {
            field = SugarTest.createField("base", "description", "base", "detail");
            field.render();

            field.setMode('edit');
            expect(field.getFieldElement().hasClass(editClass)).toBeTruthy();
            expect(field.getFieldElement().hasClass(detailClass)).toBeFalsy();
        });

        it('should remove the edit class when toggled from edit to detail mode', function () {
            field = SugarTest.createField("base", "description", "base", "edit");
            field.render();

            field.setMode('detail');
            expect(field.getFieldElement().hasClass(editClass)).toBeFalsy();
            expect(field.getFieldElement().hasClass(detailClass)).toBeTruthy();
        });

        describe('Disabled', function () {
            it('has both detail and disabled classes on set disabled', function () {
                field = SugarTest.createField("base", "description", "base", "detail");
                field.render();
                field.setDisabled(true);

                expect(field.getFieldElement().hasClass(detailClass)).toBeTruthy();
                expect(field.getFieldElement().hasClass('disabled')).toBeTruthy();
            });

            it('has both edit and disabled classes on mode change from detail to edit', function () {
                field = SugarTest.createField("base", "description", "base", "detail");
                field.render();
                field.setDisabled(true);

                field.setMode('edit');
                expect(field.getFieldElement().hasClass(detailClass)).toBeFalsy();
                expect(field.getFieldElement().hasClass(editClass)).toBeTruthy();
                expect(field.getFieldElement().hasClass('disabled')).toBeTruthy();
            });

            it('loses the disabled class when re-enabled', function () {
                field = SugarTest.createField("base", "description", "base", "detail");
                field.render();
                field.setDisabled(true);

                field.setDisabled(false);
                expect(field.getFieldElement().hasClass(detailClass)).toBeTruthy();
                expect(field.getFieldElement().hasClass('disabled')).toBeFalsy();
            });
        });
    });

    describe('Test _getFallbackTemplate method', function () {
        it('should return noaccess as name if viewName is noaccess', function() {
            field = SugarTest.createField('base', 'text', 'base', 'list', {});
            expect(field._getFallbackTemplate('noaccess')).toEqual('noaccess');
        });
    });
});
