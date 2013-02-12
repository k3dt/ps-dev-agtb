describe("Base.Field.ActionMenu", function() {
    var app, field, Account;

    beforeEach(function() {
        app = SugarTest.app;
        app.view.Field.prototype._renderHtml = function() {};
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field.model = null;
        field.view = null;
        field._loadTemplate = null;
        field = null;
        Account = null;
    });

    it('should create mass model during init', function() {
        var def = {};
        field = SugarTest.createField("base","actionmenu", "actionmenu", "list", def);
        expect(field.context.get("mass_collection")).toBeDefined();

    });

    it('should populate selected model items', function() {
        var def = {};
        field = SugarTest.createField("base","actionmenu", "actionmenu", "list", def);
        SugarTest.loadComponent("base", "view", "editable");
        SugarTest.loadComponent("base", "view", "list");
        field.view = SugarTest.createView("base", "Account", "recordlist", {});
        field.view.collection = { next_offset : -1 };

        var massCollection = field.context.get("mass_collection");
        expect(massCollection.length).toBe(0);

        Account = Backbone.Model.extend({});
        field.model = new Account({
            id: 'aaa',
            name: 'boo'
        });
        field.toggleSelect(true);
        expect(massCollection.length).toBe(1);
        field.toggleSelect(false);
        expect(massCollection.length).toBe(0);

        field.toggleSelect(true);
        expect(massCollection.length).toBe(1);
        expect(massCollection.get('aaa')).toBe(field.model);

        massCollection.entire = true;
        expect(massCollection.entire).toBe(true);
        massCollection.reset();
        expect(massCollection.entire).toBe(false);
        expect(massCollection.length).toBe(0);
    });

    it('should create action button components on the list header', function() {
        var def = {
            'buttons' : [
                {
                    'name' : 'test_button',
                    'type' : 'button',
                    'events' : {
                        'click' : 'function() { this.callback = "stuff excuted"; }',
                        'blur [name=test_button]' : 'function() { this.callback = "blur excuted"; }'
                    }
                }
            ]
        };

        field = SugarTest.createField("base","actionmenu", "actionmenu", "list-header", def);
        field._loadTemplate = function() { this.template = function(){ return '<a href="javascript:void(0);"></a>'}; };
        field.getPlaceholder();

        expect(def.buttons.length).toBe(field.fields.length);
        _.each(_.pluck(def.buttons, 'events'), function(expected_events, index) {
            _.each(expected_events, function(exp_handler, key){
                var actual_event = field.fields[index]['callback_' + key];
                expect(actual_event).toBeDefined();
                expect(_.isFunction(actual_event)).toBeTruthy();
            });

        });
    });
});
