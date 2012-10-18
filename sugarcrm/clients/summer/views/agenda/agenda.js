({
/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.ListView
 * @alias SUGAR.App.layout.ListView
 * @extends View.View
 */
    events: {
        'mouseenter tr': 'showActions',
        'mouseleave tr': 'hideActions'
    },
    /**
     * Initializes field and binds all function calls to this
     * @param {Object} options
     */
    initialize: function(options) {

        _.bindAll(this);
        
        var self = this;
        this.loaded = false;
        this.options = options;
        this.collections = {};
        this.context = app.context.getContext();

        this.collections['today'] = app.data.createBeanCollection('Meetings',[]);
        this.collections['tomorrow'] = app.data.createBeanCollection('Meetings',[]);
        this.collections['upcoming'] = app.data.createBeanCollection('Meetings',[]);

        for ( var modelType in this.collections ) {
            this.collections[modelType].bind("change",this.render);
        }

        var requestUrl = app.api.buildURL('Meetings/Agenda');
        app.view.View.prototype.initialize.call(this,options);
    },
    
    loadData: function() {
        var self = this;

        app.api.call('read',app.api.buildURL('Meetings/Agenda'),null,{success:function(data){
            var models = {'today':[],'tomorrow':[],'upcoming':[]};
            
            for (var modelType in models) {
                for (var i = 0; i < data[modelType].length; i++ ) {
                    models[modelType][models[modelType].length] = app.data.createBean('Meetings',data[modelType][i]);
                }
                self.collections[modelType].add(models[modelType]);
            }
            self.loaded = true;

            self.render();
        }});

    },

    showActions: function(e) {
        $(e.currentTarget).children("td").children("span").children(".btn-group").show();
    },
    hideActions: function(e) {
        $(e.currentTarget).children("td").children("span").children(".btn-group").hide();
    }
})
