({

/**
 * View that displays search results.
 * @class View.Views.ProfileView
 * @alias SUGAR.App.layout.ProfileView
 * @extends View.View
 */
    events: {},
    initialize: function(options) {
        this.options.meta = app.metadata.getView('Contacts', 'detail');
        app.view.View.prototype.initialize.call(this, options);
        //this.fallbackFieldTemplate = "detail"; // will use detail sugar fields

        // Explanation: So until the Contacts vardefs.php provide label mappings, etc.,
        // (which may not happen because a lot of these fields are inherited from Person)
        // we need to supply the label in Contacts/metadata/portal/views/detail.php. But 
        // this causes an issue since fields defs in client/base/fields/text for example,
        // for detail assume the label is already there. So I'm using the preview field
        // which does something like: {{str label model.module}} 
        this.fallbackFieldTemplate = "preview"; 
    },
    render: function() {
        var self = this, currentUserAttributes;

        ////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////
        // TODO: Stubbing by fetching first contact and using that .. seems
        // these change from build to build so hard coding didn't work :(
        // Later, we'll have access to current user id so this will go away.
        app.api.records("read", "Contacts", {}, null, {
            success: function(data) {
                if(data) {

                    // ---------------------------------------------- //
                    // ---------------------------------------------- //
                    // This will more or less stay depending on if we will
                    // already have the full user data cached or not. If not,
                    // we'll use current user id to make this call.
                    currentUserAttributes = {id: data.records[0].id}; // later w/be something like currentUser.id
                    self.loadCurrentUser(currentUserAttributes, function(data) {
                        if(data) {
                            self.setModel(data);
                            app.view.View.prototype.render.call(self);
                            self.renderSubnav(data);
                        } 
                    });
                    // ---------------------------------------------- //
                    // ---------------------------------------------- //
                } 
            },

            error: function(xhr, error) {
                app.error.handleHttpError(xhr, error, self);
            }
        });
        ////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////

    },
    loadCurrentUser: function(currentUserAttributes, cb) {
        var self = this;
        app.alert.show('fetch_contact_record', {level:'process', title:'Loading'});
        app.api.records("read", "Contacts", currentUserAttributes, null, {
            success: function(data) {
                app.alert.dismiss('fetch_contact_record');
                cb(data);
            },
            error: function(xhr, error) {
                app.alert.dismiss('fetch_contact_record');
                app.error.handleHttpError(xhr, error, self);
            }
        });
    },
    /**
     * Updates model for this contact.
     */
    setModel: function(data) {
        this.model = app.data.createBean("Contacts", data);
        this.model.module = 'Contacts';
    },
    renderSubnav: function(data) {
        var self = this, fullName = '', subnavModel = null;
        if (self.context.get('subnavModel')) {
            fullName = data.name ? data.full_name : data.first_name +' '+data.last_name;
            self.context.get('subnavModel').set({
                'title': fullName,
                'meta': self.meta
            });
        }
    }
})

