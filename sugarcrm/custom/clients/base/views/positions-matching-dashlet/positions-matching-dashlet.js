({
    extendsFrom: 'DashablelistView',
    /**
     * @inheritdoc
     * Don't load data if dashlet filter is not accessible.
     */
    loadData: function(options) {
        if(!this.already && this.model) {
            this.model.on('sync', this.loadData, this);
            this.already = true;
        }
        this._super('loadData', [options]);
        filterDef = [];
        if(typeof this.context.get('collection') !== 'undefined') {
            this._displayDashlet(filterDef);
        }
    },

    _displayDashlet: function(filterDef) {
        var candidateFunction = this.model.get('gtb_function_match_c');
        var candidateCountry = this.model.get('gtb_country_match_c');
        var candidateCluster = this.model.get('gtb_cluster_match_c');
        filterDef = {
            'country': {'$contains': candidateCountry},
            'gtb_cluster': {'$equals': candidateCluster},
        };
        // Add condition on pos_function field if Candidate don't have All_Functions match
        if(Array.isArray(candidateFunction) && !candidateFunction.includes('All_Functions')) {
            filterDef = _.extend({}, filterDef, {'pos_function': {'$in': candidateFunction}});
        }
        this._super('_displayDashlet', [filterDef]);
    },

    _dispose: function() {
        this.model.off('sync', this.loadData, this);
        this._super('_dispose');
    },
})
