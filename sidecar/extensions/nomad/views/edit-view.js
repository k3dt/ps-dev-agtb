(function(app) {

    app.view.views.EditView = app.view.View.extend({

        events: {
            "click #saveRecord": "saveRecord",
            "click #backRecord": "cancel"
        },

        initialize: function(options) {
            app.view.View.prototype.initialize.call(this, options);

            this._modelBackup = null;                         //backuped model attributes json
            this.relateField = null;
            this.relationshipFields = null;                   //specific relationship data fields collection

            _.each(this.meta.panels, function(panel, panelIndex) {
                _.each(panel.fields, function(field, fieldIndex) {
                    if (field.name.indexOf("email") == 0) field.type = "singleemail";
                });
            });

            this.model.on("error:validation", function() {
                app.alert.show('validation_error', {
                    level: 'error',
                    messages: 'Validation error!',
                    autoClose: true
                });
            }, this);

            var link = this.context.get("link");
            if (link) {
                // Pre-populate relate field
                var self = this;
                var parentModule = this.model.link.bean.module;
                var parentId = this.model.link.bean.id;
                var relateField = app.data.getRelateField(parentModule, link);
                if (relateField) {
                    this.relateField = relateField.name;
                    this.model.set(relateField.id_name, parentId);
                }

                //add specific relationship fields
                var relFieldNames = app.data.getRelationshipFields(parentModule, link);
                if (relFieldNames && relFieldNames.length) {
                    this.relationshipFields = _.map(relFieldNames, function(fieldName) {
                        return app.metadata.getModule(self.module).fields[fieldName];
                    });
                }
            }
        },

        _renderSelf: function() {
            this.backupModel();
            app.view.View.prototype._renderSelf.call(this);
        },

        saveRecord: function() {
            var self = this;
            this.model.save(null, {
                relate: !!this.context.get('link'),
                fieldsToValidate: this.getFields(),
                success: function(model, resp) {
                    var depth = parseInt(self.context.get("depth")) || 1;
                    app.router.go(-depth);
                }
            });
        },

        cancel: function(e) {
            this.restoreModel();
            var depth = parseInt(this.context.get("depth")) || 1;
            app.router.go(-depth);
        },

        backupModel: function() {
            var serializedModel = JSON.stringify(this.model.attributes);
            this._modelBackup = JSON.parse(serializedModel);
        },

        restoreModel: function() {
            this.model.set(this._modelBackup);
        }
    });

})(SUGAR.App);