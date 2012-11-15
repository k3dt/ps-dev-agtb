({
    events: {
        'click .search': 'showSearch',
        'keyup .dataTables_filter input': 'filterDocuments',
        'click article > a': 'triggerModal'
    },

    initialize: function(o) {
        _.bindAll(this);

        app.view.View.prototype.initialize.call(this, o);

        this.getData();
    },

    render: function() {
        if (_.isEmpty(this.docs)) {
            this.hide();
            return;
        }

        this.show();

        if (this.term) {
            this.showSearch();
        }

        app.view.View.prototype.render.call(this);
    },

    showSearch: function() {
        this.$el.find('.dataTables_filter').toggle();
    },

    triggerModal: function(e) {
        var data = this.$(e.currentTarget).data(),
            el = this.$("#gdrive-modal");

        el.find(".modal-header h3").text("Previewing " + data.name);
        el.find(".modal-body .modal-content iframe").attr("src", data.preview);
        el.find(".modal-body .modal-footer #editLink").attr("href", data.edit);
        el.modal();
    },

    filterDocuments: function(evt) {
        var self = this,
            term = this.$(evt.currentTarget).val();

        if (!this.lazyGetData) {
            this.lazyGetData = _.debounce(function(term) {
                self.getData(term);
            }, 500);
        }

        this.lazyGetData(term);
    },

    getData: function(term) {
        var self = this,
            email = this.model.get("email1") || this.model.get('email2'),
            url = '../rest/v10/google/docs?limit=5';

        if (this.term && term && this.term === term) {
            return;
        }

        this.term = term;

        if (this.term) {
            url += '&q=' + term;
        }

        if (email) {
            url += '&email=' + email;
        }

        app.api.call('GET', url, null, {
            success: function(o) {
                self.docs = o.docs;
                self.render();
            }
        });
    },

    bindDataChange: function() {
        if (this.model) {
            this.model.on("change", this.getData, this);
        }
    }
})
