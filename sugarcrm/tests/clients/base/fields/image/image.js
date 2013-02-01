describe("image field", function() {

    var app, field, model;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","test_image_upload", "image", "detail", {});
        model = field.model;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        model = null;
        field = null;
    });

    describe("image", function() {

        it("should define widget height and width on initialize", function() {
            expect(field.width).toEqual(50);
            expect(field.height).toEqual(50);

            field = SugarTest.createField("base","test_image_search", "image", "detail", {width: "120"});
            expect(field.width).toEqual(120);
            expect(field.height).toEqual(120);

            field = SugarTest.createField("base","test_image_search", "image", "detail", {height: "160"});
            expect(field.width).toEqual(160);
            expect(field.height).toEqual(160);

            field = SugarTest.createField("base","test_image_search", "image", "detail", {width: "180", height: 100});
            expect(field.width).toEqual(180);
            expect(field.height).toEqual(100);
        });

        it("should resize height", function() {
            field.$el.html($('<span></span>').addClass('image_field').addClass('image_detail').html('<label></label><input>'));
            field.resizeHeight(300);
            expect(field.$("input").height()).toEqual(300);
            field.resizeHeight(200);
            expect(field.$("label").height()).toEqual(200);
            field.resizeHeight(100);
            expect(field.$(".image_field").height()).toEqual(100);


            //Must add 18 for the edit button on edit views !
            field.$el.html( $('<span></span>').addClass('image_field').addClass('image_edit').html('<label></label><input>') );
            field.$('.image_field').append( $('<span></span>').addClass('image_btn').css({height: '15px', borderWidth: '3px'}) );
            field.resizeHeight(200);
            expect(field.$("label").height()).toEqual(218);
            field.$('.image_btn').css({height: '12px', borderWidth: '24px'});
            field.resizeHeight(100);
            expect(field.$(".image_field").height()).toEqual(136);
        });

        it("should resize width", function() {
            field.$el.html($('<span></span>').addClass('image_field').html('<label></label><input>'));
            field.resizeWidth(100);
            expect(field.$(".image_field").width()).toEqual(100);
            field.resizeWidth(200);
            expect(field.$("label").width()).toEqual(200);
            field.resizeWidth(300);
            expect(field.$("label").width()).toEqual(300);
        });


        it("should trigger change with a param for the record view", function() {
            var triggerSpy = sinon.spy(model, "trigger");
            field.model.uploadFile = function () {};
            var uploadFileStub = sinon.stub(field.model,"uploadFile", function(fieldName, $files, callbacks, options) {
                // Force production code's success hook to fire passing our fake meta
                callbacks.success({
                    test_image_upload: {
                        guid: "image-guid"
                    }
                });
            });
            field.selectImage();
            expect(triggerSpy).toHaveBeenCalledWith("change", "image");
            triggerSpy.restore();
        });

    });

    describe("image upload", function() {

        it("should format value", function() {
            expect(field.format("")).toEqual("");
            expect(field.format("filename3.jpg")).not.toEqual("");
            expect(field.format("filename3.jpg")).not.toEqual("filename3.jpg");
        });

        it("make an api call to delete the image", function() {
            var deleteStub = sinon.stub(app.api, "call");
            var renderSpy = sinon.spy(field, "render");
            $("<a></a>").addClass("delete").appendTo(field.$el);
            field.undelegateEvents();
            field.delegateEvents();

            field.$(".delete").trigger("click");
            expect(deleteStub).toHaveBeenCalled();

            field.preview = true;
            field.$(".delete").trigger("click");
            expect(renderSpy).toHaveBeenCalled();
            deleteStub.restore();
            renderSpy.restore();
        });

    });
});
