(function(app) {
    var _oldMetadataSet = app.metadata.set;
    /**
     * Hack the metadata to fix teamset type
     * @param data
     */
    app.metadata.set = function(data){
        _.each(data.modules, function(module){
            if (!_.isUndefined(module.fields)) {
                var field = module.fields.team_name;
                if (field) {
                    delete field.len;
                    field.type = "teamset";
                }

                _.each(module.fields,function(field) {
                    // Metadata is invalid for relate fields like "account_id"
                    // In certain cases, their type is "relate" and source is "non-db"
                    // See bug 60632
                    if  (field.name &&
                        (field.type === "relate") &&
                        // ends with "_id"
                        (field.name.length > 2 &&
                            (field.name.length -
                             field.name.lastIndexOf("_id")) === 3))
                    {
                        field.type = "id";
                        delete field.source;
                    }
                });
            }
        }, this);
        _oldMetadataSet.apply(this, arguments);
    };
})(SUGAR.App);

// Hack to workaround latest select2 chrome "Oh Snap" crashes
// Tracked here: SP-805
// https://codereview.chromium.org/14060024
// https://github.com/ivaynberg/select2/issues/1099
// TODO: We need to remove this when fix is pushed to stable chrome released
WebKitMutationObserver = function() {};
WebKitMutationObserver.prototype.observe = function() {};