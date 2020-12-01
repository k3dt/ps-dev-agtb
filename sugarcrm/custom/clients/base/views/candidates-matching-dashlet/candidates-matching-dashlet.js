({
    extendsFrom: 'DashablelistView',
    /**
     * @inheritdoc
     * Don't load data if dashlet filter is not accessible.
     */
    loadData: function(options) {
        if(!this.already) {
            this.model.on('change:country', this.loadData, this);
            this.model.on('change:pos_function', this.loadData, this);
            this.model.on('change:gtb_cluster', this.loadData, this);
            this.already = true;
        }
        this._super('loadData', [options]);
        filterDef = [];
        if(typeof this.context.get('collection') !== 'undefined') {
            this._displayDashlet(filterDef);
        }
    },

    _displayDashlet: function(filterDef) {
        var posCluster = this.model.get('gtb_cluster');
        var posFunction = this.model.get('pos_function');
        var posCountry = this.model.get('country');
        filterDef = {
            '$or': [
                {'gtb_function_match_c': {'$contains': posFunction}},
                {'gtb_function_match_c': {'$contains': 'All_Functions'}},
            ],
            'gtb_country_match_c': {'$contains': posCountry},
            'gtb_cluster_match_c': {'$equals': posCluster},
        };
        this._super('_displayDashlet', [filterDef]);
    },

    _dispose: function() {
        this.model.on('change:country', this.loadData);
        this.model.on('change:pos_function', this.loadData);
        this.model.on('change:gtb_cluster', this.loadData);

        this._super('_dispose');
    },
})
