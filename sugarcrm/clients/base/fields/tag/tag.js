/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

({
    plugins: ['EllipsisInline'],

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        // init bean collection used for type aheads
        this.filterResults = app.data.createBeanCollection('Tags');
    },

    /**
     * {@inheritDoc}
     */
    _render: function() {
        // Set up tagList variable for use in the list view
        this.value = this.getFormattedValue();
        if (this.value) {
            this.tagList = _.pluck(this.value, 'name').join(', ');
        }

        this._super('_render');

        this.initializeSelect2();
        this.$select2.on('change', _.bind(this.storeValues, this));
        this.$select2.on('select2-selecting', this.handleNewSelection);
    },

    /**
     * Upon selection of a tag, if it's a new tag, get rid of the text indicating new tag
     * @param {event} e
     */
    handleNewSelection: function(e) {
        // For new tags, look for New Tag indicator and remove it if it's there
        if (e.object.newTag) {
            var newTagIdx = e.object.text.lastIndexOf(' ' + app.lang.get('LBL_TAG_NEW_TAG'));
            e.object.text = e.object.text.substr(0, newTagIdx);
        }
    },

    /**
     * Initialize select2 jquery widget
     */
    initializeSelect2: function() {
        var self = this,
            escapeChars = '!\"#$%&\'()*+,./:;<=>?@[\]^`{|}~';

        this.$select2 = this.$('.select2field').select2({
            placeholder: '',
            minimumResultsForSearch: 5,
            minimumInputLength: 1,
            tags: true,
            multiple: true,
            closeOnSelect: false,
            width: '100%',
            containerCssClass: 'select2-choices-pills-close',

            initSelection: function(element, callback) {
                var data = self.parseRecords(self.value);
                callback(data);
            },

            createSearchChoice: function(term) {
                // If tag is for filter, don't allow new choices to be selected
                if (self.view.action === 'filter-rows') {
                    return false;
                }

                var selectedRecord = self.filterResults.find(function(record) {
                    // To ensure sameness of search, make both values lowercase
                    return term.toLowerCase() == record.get('name').toLowerCase();
                });

                if (selectedRecord) {
                    // Search term exists
                    return self.parseRecords([selectedRecord]);
                } else {
                    // Search term is new
                    return {
                        id: term,
                        text: term + ' ' + app.lang.get('LBL_TAG_NEW_TAG'),
                        locked: false,
                        newTag: true
                    };
                }
            },

            query: _.debounce(function(query) {
                var shortlist = {results: []};

                self.filterResults.filterDef = {
                    'filter': [{
                        'name_lower': { '$starts': query.term.toLowerCase() }
                    }]
                };

                self.filterResults.fetch({
                    success: function(data) {
                        shortlist.results = self.parseRecords(data.models);
                        query.callback(shortlist);
                    },
                    error: function() {
                        app.alert.show('collections_error', {
                            level: 'error',
                            messages: 'LBL_TAG_FETCH_ERROR'
                        });
                    }
                });
            }, 300),

            sortResults: function(results, container, query) {
                results = _.sortBy(results, 'text');
                return results;
            }
        });

        var records = _.map(this.value, function(record) {
            // If a special character is the first character of a tag, it breaks select2 and jquery and everything
            // So escape that character if it's the first char
            if (escapeChars.indexOf(record.name.charAt(0)) >= 0) {
                return '\\\\' + record.name;
            }
            return record.name;
        });

        if (records.length) {
            this.$select2.select2('val', records);
        }
    },

    /**
     * Format related records in select2 format
     * @param {array} list of objects/beans
     */
    parseRecords: function(list) {
        var select2 = [];

        _.each(list, function(item) {
            var record = item;

            // we may have a bean from a collection
            if (_.isFunction(record.toJSON)) {
                record = record.toJSON();
            }

            // locked parameter can be used in the future to prevent removal
            select2.push({id: record.name, text: record.name, locked: false});
        });

        return select2;
    },

    /**
     * Store selected/removed values on our field which is put to the server
     * @param {event} e - event data
     */
    storeValues: function(e) {
        this.value = app.utils.deepCopy(this.value) || [];
        if (e.added) {
            // Check to see if the tag we're adding has already been added.
            var valFound = _.find(this.value, function(vals) {
                return (vals.name === e.added.text);
            });

            if (!valFound) {
                this.value.push({id: e.added.id, name: e.added.text});
            }
        } else if (e.removed) {
            // Remove the tag
            this.value = _.reject(this.value, function(record) {
                return record.name === e.removed.text;
            });
        }

        this.model.set('tag', this.value);
    },

    /**
     * Avoid rendering process on Select2 change in order to keep focus
     * @override
     */
    bindDataChange: function() {
    },

    /**
     * Override to remove default DOM change listener, we use Select2 events instead
     * @override
     */
    bindDomChange: function() {
    },

    /**
     * {@inheritDoc}
     */
    unbindDom: function() {
        this.$('.select2field').select2('destroy');
        this._super('unbindDom');
    }
})
