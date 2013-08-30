describe("Drawer Layout", function() {
    var moduleName = 'Contacts',
        layoutName = 'drawer',
        sinonSandbox,
        $drawers,
        drawer,
        components,
        app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('drawer', 'layout', 'base', 'expand');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.addViewDefinition('record', {
            "panels":[
                {
                    "name":"panel_header",
                    "placeholders":true,
                    "header":true,
                    "labels":false,
                    "fields":[
                        {
                            "name":"first_name",
                            "label":"",
                            "placeholder":"LBL_NAME"
                        },
                        {
                            "name":"last_name",
                            "label":"",
                            "placeholder":"LBL_NAME"
                        }
                    ]
                }, {
                    "name":"panel_body",
                    "columns":2,
                    "labels":false,
                    "labelsOnTop":true,
                    "placeholders":true,
                    "fields":[
                        "phone_work",
                        "email1",
                        "phone_office",
                        "full_name"
                    ]
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        sinonSandbox = sinon.sandbox.create();

        $drawers = $('<div id="drawers"></div>');
        SugarTest.createLayout('base', moduleName, layoutName, {}, undefined, false, {
            el: $drawers
        });

        drawer = SugarTest.app.drawer;
        components = drawer._components;
        app = SugarTest.app;
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sinonSandbox.restore();
        delete SugarTest.app.drawer;
    });

    describe('Initialize', function() {
        it('Should not have any components and the close callback should be empty', function() {
            expect(drawer._components.length).toBe(0);
            expect(drawer.onCloseCallback.length).toBe(0);
        });
    });

    describe('Open', function() {
        it('Should add drawers every time it is called', function() {
            sinon.stub(drawer, '_animateOpenDrawer', function(){});

            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });

            expect(components.length).toBe(1);
            expect(components[components.length-1].name).toBe('foo');

            drawer.open({
                layout: {
                    "name": "bar",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });

            expect(components.length).toBe(2);
            expect(components[components.length-1].name).toBe('bar');
        });
        it('should trigger an app:view:change event', function(){
            sinonSandbox.stub(drawer, '_animateOpenDrawer', function(callback){
                callback();
            });
            var triggerStub = sinonSandbox.stub(app, "trigger", $.noop());
            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });
            expect(triggerStub.calledOnce).toBe(true);
            expect(triggerStub.firstCall.args[0]).toEqual("app:view:change");
            expect(triggerStub.firstCall.args[1]).toEqual("foo");

        });
    });

    describe('Close', function() {
        it('Should remove drawers every time it is called', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer', $.noop());
            sinonSandbox.stub(drawer, '_animateCloseDrawer', function(callback){
                callback();
            });

            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });
            drawer.open({
                layout: {
                    "name": "bar",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });

            expect(components.length).toBe(2);
            expect(components[components.length-1].name).toBe('bar');

            drawer.close();

            expect(components.length).toBe(1);
            expect(components[components.length-1].name).toBe('foo');

            drawer.close();

            expect(components.length).toBe(0);
        });

        it('Should call the onClose callback function', function() {
            var spy = sinon.spy();
            sinonSandbox.stub(drawer, '_animateOpenDrawer', function(){});
            sinonSandbox.stub(drawer, '_animateCloseDrawer', function(callback){
                callback();
            });

            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            }, spy);

            expect(drawer.onCloseCallback.length).toBe(1);

            drawer.close('foo');

            expect(spy.calledWith('foo')).toBe(true);
            expect(drawer.onCloseCallback.length).toBe(0);
        });

        it('Should call closeImmediately if browser does not support css transitions', function() {
            var stub = sinon.stub(drawer, 'closeImmediately'),
                cssTransitions = Modernizr.csstransitions,
                animateCloseStub = sinon.stub(drawer, '_animateCloseDrawer');

            Modernizr.csstransitions = false;
            drawer.close('foo');
            expect(stub.calledWith('foo')).toBe(true);
            expect(animateCloseStub.called).toBe(false);
            Modernizr.csstransitions = cssTransitions;
            stub.restore();
            animateCloseStub.restore();
        });

        it('should trigger an app:view:change event', function(){
            sinonSandbox.stub(drawer, '_animateOpenDrawer', function(){});
            sinonSandbox.stub(drawer, '_animateCloseDrawer', function(callback){
                callback();
            });

            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            }, $.noop());

            var triggerStub = sinonSandbox.stub(app, "trigger", $.noop());

            drawer.close('foo');
            expect(triggerStub.calledOnce).toBe(true);
            expect(triggerStub.firstCall.args[0]).toEqual("app:view:change");
            triggerStub.restore();
        });
    });

    describe('Close immediately', function() {
        it('Should remove drawers every time it is called', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer', function(){});
            sinonSandbox.stub(drawer, '_animateCloseDrawer', function(callback){
                callback();
            });

            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });
            drawer.open({
                layout: {
                    "name": "bar",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });

            expect(components.length).toBe(2);
            expect(components[components.length-1].name).toBe('bar');

            drawer.closeImmediately();

            expect(components.length).toBe(1);
            expect(components[components.length-1].name).toBe('foo');

            drawer.closeImmediately();

            expect(components.length).toBe(0);
        });

        it('Should call the onClose callback function', function() {
            var spy = sinon.spy();
            sinonSandbox.stub(drawer, '_animateOpenDrawer', function(){});
            sinonSandbox.stub(drawer, '_animateCloseDrawer', function(callback){
                callback();
            });

            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            }, spy);

            expect(drawer.onCloseCallback.length).toBe(1);

            drawer.closeImmediately('foo');

            expect(spy.calledWith('foo')).toBe(true);
            expect(drawer.onCloseCallback.length).toBe(0);
        });

        it('Should still have transition class on the drawer afterwards', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer', function(){});
            sinonSandbox.stub(drawer, '_animateCloseDrawer', function(callback){
                callback();
            });

            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });
            drawer.open({
                layout: {
                    "name": "bar",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });

            drawer.closeImmediately('foo');

            expect(drawer._getDrawers(false).$top.hasClass('transition')).toBe(true);
        });
    });

    describe('Load', function() {
        it('Should replace the top-most drawer', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer', function(){});

            drawer.open({
                layout: {
                    "name": "foo",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });

            expect(components.length).toBe(1);
            expect(components[components.length-1].name).toBe('foo');

            drawer.load({
                layout: {
                    "name": "bar",
                    "components":[{"view":"record"}]
                },
                context: {create: true}
            });

            expect(components.length).toBe(1);
            expect(components[components.length-1].name).toBe('bar');
        });
    });

    describe('Reset', function() {
        it('Should remove all drawers', function() {
            sinonSandbox.stub(drawer, '_animateOpenDrawer', function(){});

            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            expect(drawer._components.length).toBe(2);
            expect(drawer.onCloseCallback.length).toBe(2);

            drawer.reset();

            expect(drawer._components.length).toBe(0);
            expect(drawer.onCloseCallback.length).toBe(0);
        });
    });

    describe('_getDrawers(true)', function() {
        var $contentEl, $mainDiv;

        beforeEach(function() {
            $contentEl = SugarTest.app.$contentEl;
            $mainDiv = $('<div></div>');

            SugarTest.app.$contentEl = $('<div id="content"></div>').append($mainDiv);
            sinon.stub(drawer, '_animateOpenDrawer', function(){});
        });

        afterEach(function() {
            SugarTest.app.$contentEl = $contentEl;
        });

        it('Should return no drawers when there are none opened', function() {
            var result = drawer._getDrawers(true);

            expect(result.$next).not.toBeDefined();
            expect(result.$top).not.toBeDefined();
            expect(result.$bottom).not.toBeDefined();
        });

        it('Should return the correct drawers when there is one open', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            var result = drawer._getDrawers(true);

            expect(result.$next.is(components[components.length-1].$el)).toBe(true);
            expect(result.$top.is($mainDiv)).toBe(true);
            expect(result.$bottom).not.toBeDefined();
        });

        it('Should return the correct drawers when there are two open', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            var result = drawer._getDrawers(true);

            expect(result.$next.is(components[components.length-1].$el)).toBe(true);
            expect(result.$top.is(components[components.length-2].$el)).toBe(true);
            expect(result.$bottom.is($mainDiv)).toBe(true);
        });

        it('Should return the correct drawers when there are three open', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            var result = drawer._getDrawers(true);

            expect(result.$next.is(components[components.length-1].$el)).toBe(true);
            expect(result.$top.is(components[components.length-2].$el)).toBe(true);
            expect(result.$bottom.is(components[components.length-3].$el)).toBe(true);
        });
    });

    describe('_getDrawers(false)', function() {
        var $contentEl, $mainDiv;

        beforeEach(function() {
            $contentEl = SugarTest.app.$contentEl;
            $mainDiv = $('<div></div>');

            SugarTest.app.$contentEl = $('<div id="content"></div>').append($mainDiv);
            sinon.stub(drawer, '_animateOpenDrawer', function(){});
        });

        afterEach(function() {
            SugarTest.app.$contentEl = $contentEl;
        });

        it('Should return no drawers when there are none opened', function() {
            var result = drawer._getDrawers(false);

            expect(result.$next).not.toBeDefined();
            expect(result.$top).not.toBeDefined();
            expect(result.$bottom).not.toBeDefined();
        });

        it('Should return the correct drawers when there is one open', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            var result = drawer._getDrawers(false);

            expect(result.$next).not.toBeDefined();
            expect(result.$top.is(components[components.length-1].$el)).toBe(true);
            expect(result.$bottom.is($mainDiv)).toBe(true);
        });

        it('Should return the correct drawers when there are two open', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            var result = drawer._getDrawers(false);

            expect(result.$next.is($mainDiv)).toBe(true);
            expect(result.$top.is(components[components.length-1].$el)).toBe(true);
            expect(result.$bottom.is(components[components.length-2].$el)).toBe(true);
        });

        it('Should return the correct drawers when there are three open', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            var result = drawer._getDrawers(false);

            expect(result.$next.is(components[components.length-3].$el)).toBe(true);
            expect(result.$top.is(components[components.length-1].$el)).toBe(true);
            expect(result.$bottom.is(components[components.length-2].$el)).toBe(true);
        });
    });

    describe('isActive()', function(){
        beforeEach(function() {
            $contentEl = SugarTest.app.$contentEl;
            $mainDiv = $('<div id="target"></div>');

            SugarTest.app.$contentEl = $('<div id="content"></div>').append($mainDiv);
            sinon.stub(drawer, '_animateOpenDrawer', $.noop());
        });

        afterEach(function() {
            SugarTest.app.$contentEl = $contentEl;
        });
        it('should return true for elements when no drawer is open', function(){
            expect(drawer.isActive($("<div></div>"))).toBe(true);
        });
        it('should return true for elements on active drawer', function(){
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });
            expect(drawer.isActive(drawer._getDrawers(false).$top.find(".record"))).toBe(true);
        });
        it('should return false for elements not on active drawer', function(){
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });
            expect(drawer.isActive($("<div></div>"))).toBe(false);
            expect(drawer.isActive(drawer._getDrawers(false).$bottom.find(".record"))).toBe(false);
            expect(drawer.isActive(drawer._getDrawers(false).$top.find(".record"))).toBe(true);
        });
    });

    describe('_isMainAppContent()', function() {
        var $contentEl, $mainDiv;

        beforeEach(function() {
            $contentEl = SugarTest.app.$contentEl;
            $mainDiv = $('<div></div>');

            SugarTest.app.$contentEl = $('<div id="content"></div>').append($mainDiv);
            sinon.stub(drawer, '_animateOpenDrawer', function(){});
        });

        afterEach(function() {
            SugarTest.app.$contentEl = $contentEl;
        });

        it('Should return false for a drawer', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            expect(drawer._isMainAppContent(components[components.length-1].$el)).toBe(false);
        });

        it('Should return true for the main application content area', function() {
            drawer.open({
                layout: {"components":[{"view":"record"}]},
                context: {create: true}
            });

            expect(drawer._isMainAppContent($mainDiv)).toBe(true);
        });
    });
});
