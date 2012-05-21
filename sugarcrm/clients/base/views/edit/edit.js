/**
 * View that displays edit view on a model
 * @class View.Views.EditView
 * @alias SUGAR.App.layout.EditView
 * @extends View.View
 */
({
    events: {
        'click [name=save_button]': 'saveModel'
    },
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
     //   this.context.get('subnavModel').set('title', 'Create');
    },
    saveModel: function() {
        var self = this;

        // TODO we need to dismiss this in global error handler
        app.alert.show('save_edit_view', {level: 'process', title: 'Saving'});
        this.model.save(null, {
            success: function() {
                app.alert.dismiss('save_edit_view');
                self.app.navigate(self.context, self.model, 'detail');
            },
            fieldsToValidate: this.getFields(this.model.module)
        });
    },
    bindDataChange: function() {
        if (this.model) {
            this.model.on("change", function() {
                if (this.context.get('subnavModel')) {
                    this.context.get('subnavModel').set({
                        'title': this.model.get('name'),
                        'meta': this.meta
                    });
                }
            }, this);
        }
    }
})