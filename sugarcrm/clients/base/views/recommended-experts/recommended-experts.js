({
    events: {
        "click .find-experts": "getRecommendations",
        "keyup .job-title": "submit"
    },

    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);
        this.getJobTitles();
        this.collection = app.data.createBeanCollection("Users");
    },

    render: function() {
        app.view.View.prototype.render.call(this);

        if (this.$(".job-title") && this.typeahead_collection) {
            this.$(".job-title").typeahead({source: this.typeahead_collection});
        }
    },

    getJobTitles: function() {
        var self = this,
            url = app.api.buildURL(this.module, "expertsTypeahead",
                {"id": app.controller.context.get("model").id});

        app.api.call("read", url, null, { success: function(data) {
            self.typeahead_collection = data;
            if( self.$(".job-title") ) {
                self.$(".job-title").typeahead({source: self.typeahead_collection});
            }
        }});
    },

    submit: function(e) {
        if( this.$(".job-title").val().length && e.keyCode === 13 ) {
            this.getRecommendations();
        }
    },

    getRecommendations: function() {
        var self = this;
            this.jobTitle = this.$(".job-title").val();

        if( this.jobTitle.length ) {
            // build the URL for the custom "experts" REST endpoint
            var url = app.api.buildURL(this.module, "experts",
                {"id": app.controller.context.get("model").id},
                {"title": this.jobTitle});

            app.api.call("read", url, null, { success: function(data) {
                self.collection.reset();
                if( data.length ) {
                    _.each(data, function(key, value) {
                        data[value]["guid"] = _.uniqueId("recommended-experts-item");
                        data[value]["picture_url"] = data[value]["picture"] ? app.api.buildFileURL({
                            module: "Users",
                            id: data[value]["id"],
                            field: "picture"
                        }) : "../styleguide/assets/img/profile.png";

                        var model = app.data.createBean("User");
                        model.attributes = data[value];
                        self.collection.add(model);
                    });
                }
                self.render();
            }});
        }
    }
})
