/**
 * View that displays the activity stream.
 * @class View.Views.ActivityView
 * @alias SUGAR.App.layout.ActivityView
 * @extends View.View
 */
({
    events: {
        'click #saveNote': 'saveNote',
        'click .search': 'showSearch',
        'click .addNote': 'openNoteModal',
        'click .icon-eye-open': 'loadChildDetailView'
    },
    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    },
    // Delegate events
    saveNote: function() {
        var self = this;
        this.$('#saveNote').button('loading');

        var args = {
            name: this.$('[name=subject]').val(),
            description: this.$('[name=description]').val(),
            portal_flag: true
        }

        var newNote = app.data.createRelatedBean(app.controller.context.get('model'), null, "notes", args);
        newNote.save(null, {
            relate: true,
            success: function(data) {
                self.$('#saveNote').button();
                self.$('#noteModal').modal('hide').find('form').get(0).reset();
                self.collection.add(newNote);
                self.render();
            },
            error: function(data) {
                self.$('#saveNote').button();
                self.$('#noteModal').modal('hide').find('form').get(0).reset();
            }
        });
    },
    showSearch: function() {
        var $searchEl = $('.search');
        $searchEl.toggleClass('active');
        $searchEl.parent().parent().parent().find('.dataTables_filter').toggle();
        $searchEl.parent().parent().parent().find('.form-search').toggleClass('hide');
        return false;
    },
    openNoteModal: function() {
        this.$('#noteModal').modal('show');
        this.$('li.open').removeClass('open');
        return false;
    },
    loadChildDetailView: function(e) {
        // UI fix
        this.$("li.activity").removeClass("on");
        this.$(e.currentTarget).parent().parent().parent().addClass("on");

        // gets the activityId in the data attribute
        var activityId = this.$(e.currentTarget).parent().parent().parent().data("id");

        // gets the activity model
        var activity = this.collection.get(activityId);

        // clears the current listened model and push the new one
        this.model.clear().set(activity.toJSON());
    }

})
