({
    events:{
        'click .reply': 'showAddComment',
        'click .postReply': 'addComment'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        // Check to see if we need to make a related activity stream.
        if (this.module !== "ActivityStream") {
            if (this.context.get("modelId")) {
                this.opts = { params: { module: this.module, id: this.context.get("modelId") }};
            } else {
                this.opts = { params: { module: this.module }};
            }

            this.collection = app.data.createBeanCollection("ActivityStream");
            this.collection.fetch(this.opts);
        }
    },

    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    },

    showAddComment: function(event) {
        $(event.currentTarget).closest('li').find('.activitystream-comment').show();
    },

    addComment: function(event) {
        var self = this;
        var myPost = $(event.currentTarget).closest('li');
        var myPostContents = myPost.find('input.sayit')[0].value;
        var myPostId = $(event.currentTarget).data('id');

        this.app.api.call('create',this.app.api.buildURL('ActivityStream/ActivityStream/'+myPostId),{'value':myPostContents},{success:function(){self.collection.fetch(self.opts)}});
    }
})