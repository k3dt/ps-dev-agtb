describe('accessibility', function() {
    var app;

    beforeEach(function() {
        app = SugarTest.app;
    });

    describe('determine which helpers to run', function() {
        var helpersBackup;

        beforeEach(function() {
            helpersBackup = app.accessibility.helpers;
            app.accessibility.helpers = {
                foo: {},
                bar: {},
                baz: {},
                qux: {}
            };
        });

        afterEach(function() {
            app.accessibility.helpers = helpersBackup;
        });

        it('should return all helpers when the parameter is undefined', function() {
            expect(_.size(app.accessibility.whichHelpers())).toBe(_.size(app.accessibility.helpers));
        });

        _.each(['foo', ['foo']], function(data) {
            it('should return only the "foo" helper', function() {
                expect(_.keys(app.accessibility.whichHelpers(data))).toEqual(['foo']);
            });
        });

        it('should return only the "foo" and "bar" helpers', function() {
            var helpers = ['foo', 'bar'];
            expect(_.keys(app.accessibility.whichHelpers(helpers))).toEqual(helpers);
        });

        it('should return no helpers when the parameter is not a valid helper', function() {
            expect(_.size(app.accessibility.whichHelpers('foobar'))).toBe(0);
        });
    });

    describe('click compliance', function() {
        _.each(['button', 'input', 'select', 'textarea'], function(data) {
            it('should not add a tabindex attribute to an "' + data + '" element', function() {
                var tag = '<' + data + '/>',
                    $el = $(tag, {name: 'foo'}).on('click', $.noop);
                app.accessibility.run($el, 'click');
                expect($el[0].hasAttribute('tabindex')).toBe(false);
            });
        });

        _.each(['a', 'area'], function(data) {
            it('should add a tabindex attribute to an "' + data + '" element without an href attribute', function() {
                var tag = '<' + data + '/>',
                    $el = $(tag, {name: 'foo'}).on('click', $.noop);
                app.accessibility.run($el, 'click');
                expect($el.attr('tabindex')).toBe('-1');
            });

            it('should not add a tabindex attribute to an "' + data + '" element with an href attribute', function() {
                var tag = '<' + data + '/>',
                    $el = $(tag, {name: 'foo', href: 'http://www.foo.com/'}).on('click', $.noop);
                app.accessibility.run($el, 'click');
                expect($el[0].hasAttribute('tabindex')).toBe(false);
            });
        });

        it('should add a tabindex attribute of "0" to an "a" element with an role attribute of "button"', function() {
            var $el = $('<a/>', {name: 'foo'}).attr('role', 'button').on('click', $.noop);
            app.accessibility.run($el, 'click');
            expect($el.attr('tabindex')).toBe('0');
        });

        it('should not modify tabindex attribute of an "a" element with an role attribute of "button"', function() {
            var $el = $('<a/>', {name: 'foo', role: 'button'}).attr('tabindex', -1).on('click', $.noop);
            app.accessibility.run($el, 'click');
            expect($el.attr('tabindex')).toBe('-1');
        });

        it('should add a tabindex attribute to a non-compliant element without a tabindex', function() {
            var $el = $('<foo/>', {name: 'foo'}).on('click', $.noop);
            app.accessibility.run($el, 'click');
            expect($el.attr('tabindex')).toBe('-1');
        });

        it('should not add a tabindex attribute to an element with a tabindex', function() {
            var $el = $('<foo/>', {name: 'foo'}).attr('tabindex', 0).on('click', $.noop);
            app.accessibility.run($el, 'click');
            expect($el.attr('tabindex')).not.toBe('-1');
        });

        it('should not add a tabindex attribute to an element without any click events', function() {
            var $el = $('<foo/>', {name: 'foo'});
            app.accessibility.run($el, 'click');
            expect($el[0].hasAttribute('tabindex')).toBe(false);
        });
    });

    describe('label compliance', function() {
        var field, sidecarFieldBefore;

        beforeEach(function() {
            // mock out Field so we have an instance of Field
            sidecarFieldBefore = app.view.Field;
            app.view.Field = Backbone.View;
            field = new app.view.Field();

            // set test values
            field.fieldTag = 'input';
            field.label = 'foo';
            field.$el = $('<span><input type="text" name="bar" /></span>');
        });

        afterEach(function() {
            // restore original Field definition
            app.view.Field = sidecarFieldBefore;
        });

        it('should add aria-label for an input field', function() {
            app.accessibility.run(field, 'label');
            expect(field.$el.find(field.fieldTag).attr('aria-label')).toEqual('foo');
        });

        it('should not add aria-label again for an input field that already has it set', function() {
            field.$el.find(field.fieldTag).attr('aria-label', 'baz');
            app.accessibility.run(field, 'label');
            expect(field.$el.find(field.fieldTag).attr('aria-label')).toEqual('baz');
        });

        it('should not add aria-label for fields that do not require one', function() {
            field.$el = $('<span><div class="blah"></div></span>');
            field.fieldTag = 'div';
            app.accessibility.run(field, 'label');
            expect(field.$el.find(field.fieldTag).attr('aria-label')).toBeUndefined();
        });
    });
});
