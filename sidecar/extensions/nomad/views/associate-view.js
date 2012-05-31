(function(app) {

    app.view.views.AssociateView = app.view.views.ListView.extend({

        initialize: function(options) {
            app.view.View.prototype.initialize.call(this, options);

            this.template = app.template.get("list"); //todo:remove it later

            // This view behaves like the list view
            this.meta = app.metadata.getView(this.module, "list");
            this.fallbackFieldTemplate = "list";

            this.parentModule = this.context.get("toModule");
            this.parentId = this.context.get("toId");
            this.link = this.context.get("viaLink");
            this.parentBean = app.data.createBean(this.parentModule, { id: this.parentId });

            // Flag indicating if the list is multi-select or not
            this.multiselect = app.data.canHaveMany(this.parentModule, this.link);

            app.logger.debug("Record(s) are to be associated with " +
                this.parentBean + " via link " + this.link);
            app.logger.debug("Multiselect: " + this.multiselect);
        },

        save: function() {
            var source = this;

            this.$('.selecterd-flag:checked').each(function() {
                var cid = $(this).closest('article').attr('id').replace(source.module, '');
                source.saveBean(source.collection.get(cid));

            });
        },
        saveBean: function(bean) {

            var relateBean = app.data.createRelatedBean(this.parentBean, bean, this.link);
            relateBean.save(null, {
                relate: true,
                success: function() {
                }
            });
        },
        onCancelClicked: function() {

        }


    });

})(SUGAR.App);