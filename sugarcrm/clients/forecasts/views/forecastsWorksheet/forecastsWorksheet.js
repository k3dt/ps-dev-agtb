/**
 * View that displays header for current app
 * @class View.Views.WorksheetView
 * @alias SUGAR.App.layout.WorksheetView
 * @extends View.View
 */
({

    url: 'rest/v10/ForecastWorksheets',
    show: false,
    viewModule: {},
    selectedUser: {},
    gTable:'',
    // boolean for enabled expandable row behavior
    isExpandableRows:'',
    _collection:{},


    /**
     * This function handles updating the totals calculation and calling the render function.  It takes the model entry
     * that was updated by the toggle event and calls the Backbone save function on the model to invoke the REST APIs
     * to handle persisting the changes
     *
     * @param model Backbone model entry that was affected by the toggle event
     */
    toggleIncludeInForecast:function(model)
    {
        var self = this;
        self._collection.url = self.url;
        model.save(null, { success:_.bind(function() { this.calculateTotals(), this.render(); }, this)});
    },

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        var self = this;

        this.viewModule = app.viewModule;

        //set expandable behavior to false by default
        this.isExpandableRows = false;

        app.view.View.prototype.initialize.call(this, options);
        this._collection = this.context.forecasts.worksheet;


        //set up base selected user
    	this.selectedUser = {id: app.user.get('id'), "isManager":app.user.get('isManager'), "showOpps": false};

        //Create a Backbone.Model instance to represent the included amounts
        this.includedModel = new Backbone.Model(
            {
                includedAmount : 0,
                includedBest : 0,
                includedLikely : 0,
                includedCount : 0
            }
        );

        //Create a Backbone.Model instance to represent the overall amounts
        self.overallModel = new Backbone.Model(
            {
                overallAmount : 0,
                overallBest : 0,
                overallLikely : 0
            }
        )


        var IncludedView = Backbone.View.extend({
            id : 'included_totals',
            tagName : 'tr',

            initialize: function() {
                _.bindAll(this, 'render');
                this.model.bind('change', this.render);
            },

            render: function() {
                var self = this;
                var source = $("#included_template").html();
                var hb = Handlebars.compile(source);
                $('#included_totals').html(hb(self.model.toJSON()));
                return this;
            }
        });

        this.includedView = new IncludedView({
            model : self.includedModel
        });


        var OverallView = Backbone.View.extend({
            id : 'overall_totals',
            tagName : 'tr',

            initialize: function() {
                _.bindAll(this, 'render');
                this.model.bind('change', this.render);
            },

            render: function() {
                var self = this;
                var source = $("#overall_template").html();
                var hb = Handlebars.compile(source);
                $('#overall_totals').html(hb(self.model.toJSON()));
                return this;
            }
        });

        self.overallView = new OverallView({
            model : self.overallModel
        });


        var ExpectedOpportunitiesView = app.view.View.extend({
            id : 'expected_opportunities',
            tagName : 'tr',

            initialize: function() {
                //app.view.View.prototype.initialize.call(this, options);
                _.bindAll(this, 'render');
                this.collection.bind('change', this.render);
            },

            _renderField: function(field) {
                app.view.View.prototype._renderField.call(self, field);

                if (self.isMyWorksheet() && field.viewName != "edit" && field.def.clickToEdit === true) {
                    new app.view.ClickToEditField(field, this);
                }
            },

            render: function() {
                var self = this;
                var source   = $("#expected_template").html();
                //var hb = Handlebars.compile(source);
                //$('#expected_opportunities').html(hb("{test:'hello'}"));

                var fields = [
                    {
                        name : 'expected_amount',
                        sfId : 'expected_amount_sf_id',
                        viewName : 'detail',
                        type : 'int',
                        field : { def: { clickToEdit : true }}
                    },
                    {
                        name : 'expected_best_case',
                        sfId : 'expected_best_case_sf_id',
                        viewName : 'detail',
                        type : 'int',
                        field : { def: { clickToEdit : true }}
                    },
                    {
                        name : 'expected_likely_case',
                        sfId : 'expected_likely_case_sf_id',
                        viewName : 'detail',
                        type : 'int',
                        field : { def: { clickToEdit : true }}
                    }
                ];

                _.each(fields, function (value, key) {
                    //app.view.View.prototype._renderField.call(self, value);
                });

                return this;
            }
        });

        this.expectedOpportunitiesView = new ExpectedOpportunitiesView({
            collection : this.context.forecasts.forecastschedule
        });


        // INIT tree with logged-in user       
        this.updateWorksheetBySelectedUser(this.selectedUser);
        this.updateWorksheetBySelectedCategory(app.defaultSelections.category);
        this.updateWorksheetBySelectedTimePeriod(app.defaultSelections.timeperiod_id)
    },

    createURL:function() {
        var url = this.url;
        var args = {};
        if(this.timePeriod) {
           args['timeperiod_id'] = this.timePeriod;
        }

        if(this.selectedUser)
        {
           args['user_id'] = this.selectedUser.id;
        }

        var params = '';
        _.each(args, function (value, key) {
            params += '&' + key + '=' + encodeURIComponent(value);
        });

        if(params)
        {
            url += '?' + params.substr(1);
        }
        return url;
    },

    /**
     * Sets up the save event and handler for the commit_stage dropdown fields in the worksheet.
     * @param field the commit_stage field
     * @return {*}
     * @private
     */
    _setUpCommitStage: function (field) {
        field._save = function(event, input) {
            this.model.set('commit_stage', input.selected);
            this.view.context.set('selectedToggle', field);
        };
        field.events = _.extend({"change select": "_save"}, field.events);
        return field;
    },

    /**
     * Renders a field.
     *
     * This method sets field's view element and invokes render on the given field.  If clickToEdit is set to true
     * in metadata, it will also render it as clickToEditable.
     * @param {View.Field} field The field to render
     * @protected
     */
    _renderField: function(field) {

        if (this.isMyWorksheet() && field.name == "commit_stage") {
            field = this._setUpCommitStage(field);
        }

        app.view.View.prototype._renderField.call(this, field);

        if (this.isMyWorksheet() && field.viewName !="edit" && field.def.clickToEdit === true) {
            new app.view.ClickToEditField(field, this);
        }

        if( this.isMyWorksheet() && field.name == "commit_stage") {
            new app.view.BucketGridEnum(field, this);
        }
    },

    bindDataChange: function(params) {
        var self = this;
        if (this._collection) {
            this._collection.on("reset", function() { self.refresh(); }, this);

            this._collection.on("change", function() {
                _.each(this._collection.models, function(element, index){
                    if(element.hasChanged("forecast")) {
                        this.toggleIncludeInForecast(element);
                    }
                }, this);
            }, this);
        }

        this.context.on("change:selectedToggle",
            function(context, toggle) {
                self._collection.url = self.url;
                model = toggle.model;
                model.set('forecast', (toggle.value === true) ? false : true);
                model.save(null, {wait: true});
                self.refresh();
            }, this);

        // listening for updates to context for selectedUser:change
        if (this.context.forecasts) {
            this.context.forecasts.on("change:selectedUser",
                function(context, selectedUser) {
                    this.updateWorksheetBySelectedUser(selectedUser);
                }, this);
            this.context.forecasts.on("change:selectedTimePeriod",
                function(context, timePeriod) {
                    this.updateWorksheetBySelectedTimePeriod(timePeriod);
                }, this);
            this.context.forecasts.on("change:selectedCategory",
                function(context, category) {
                    this.updateWorksheetBySelectedCategory(category);
                },this);
            this.context.forecasts.worksheet.on("change", function() {
            	this.calculateTotals();
            	this.includedView.render();
            }, this);
        }
    },

    /**
     * Refresh the view
     *
     * This method ensures that we first calculate the totals from the collection before calling render to redraw results
     * @param context
     */
    refresh:function(context) {
        $.when(this.calculateTotals(), this.render());
    },

    _setForecastColumn: function(fields) {
        var self = this;
        var forecastField, commitStageField;
        var isOwner = self.isMyWorksheet();

        _.each(fields, function(field) {
            if (field.name == "forecast") {
                field.enabled = !app.config.showBuckets;
                forecastField = field;
            } else if (field.name == "commit_stage") {
                field.enabled = app.config.showBuckets;
                if(!isOwner)
                {
                   field.view = 'default';
                }
                commitStageField = field;
            }
        });
        return app.config.showBuckets?forecastField:commitStageField;
    },

    /**
     * Renders view
     */
    _render:function () {
        var self = this;

        if(!this.showMe()){
        	return false;
        }
        $("#view-sales-rep").show();
        $("#view-manager").hide();

        var unusedField = this._setForecastColumn(this.meta.panels[0].fields);

        app.view.View.prototype._render.call(this);

        // parse metadata into columnDefs
        // so you can sort on the column's "name" prop from metadata
        var columnDefs = [];
        var fields = _.without(this.meta.panels[0].fields, unusedField);
        var columnKeys = {};

        _.each(fields, function(field, key){
            var name = field.name;
            columnDefs.push( { "sName": name, "aTargets": [ key ] } );
            columnKeys[name] = key;
        });

        this.gTable = this.$('.worksheetTable').dataTable(
            {
                "aoColumnDefs": columnDefs,
                "aaSorting": [],
                "bInfo":false,
                "bPaginate":false
            }
        );

        // if isExpandable, add expandable row behavior
        if (this.isExpandableRows) {
            $('.worksheetTable tr').on('click', function () {
                if (self.gTable.fnIsOpen(this)) {
                    self.gTable.fnClose(this);
                } else {
                    self.gTable.fnOpen(this, self.formatAdditionalDetails(this), 'details');
                }
            });
        }

        this.expectedOpportunitiesView.render();
        this.includedView.render();
        this.overallView.render();

    },

    /**
     * Add a click event listener to the commit button
     */
    events: {
        "div a[id=include_expected]" : "includeExpected"
    },

    /**
     * Function to handle the toggle state of including/excluding expected amounts
     */
    includeExpected: function() {
        debugger;
    },

    /**
     * Determines if this Worksheet belongs to the current user, applicable for determining if this view should show,
     * or whether to render the clickToEdit field
     * @return {Boolean} true if it is the worksheet of the logged in user, false if not.
     */
    isMyWorksheet: function() {
        return _.isEqual(app.user.get('id'), this.selectedUser.id);
    },

    /**
     * Determines if this Worksheet should be rendered
     */
    showMe: function(){
    	var selectedUser = this.selectedUser;
    	this.show = false;

    	if(selectedUser.showOpps || !selectedUser.isManager){
    		this.show = true;
    	}

    	return this.show;
    },

    /**
     *
     * @param selectedUser
     */
    calculateTotals: function() {
        var self = this;
        var includedAmount = 0;
        var includedBest = 0;
        var includedLikely = 0;
        var overallAmount = 0;
        var overallBest = 0;
        var overallLikely = 0;
        var includedCount = 0;

        _.each(self._collection.models, function (model) {
            var included = model.get('forecast');
            var amount = parseFloat(model.get('amount'));
            var likely = parseFloat(model.get('likely_case'));
            var best = parseFloat(model.get('best_case'));

            if(included == true || included == 1)
            {
                includedAmount += amount;
                includedLikely += likely;
                includedBest += best;
                includedCount++;
            }
            overallAmount += amount;
            overallLikely += likely;
            overallBest += best;
        });

        self.includedModel.set('includedAmount', includedAmount);
        self.includedModel.set('includedBest', includedBest);
        self.includedModel.set('includedLikely', includedLikely);

        self.overallModel.set('overallAmount', overallAmount);
        self.overallModel.set('overallBest', overallBest);
        self.overallModel.set('overallLikely', overallLikely);

        var totals = {
            'likely_case' : includedLikely,
            'best_case' : includedBest,
            'timeperiod_id' : self.timePeriod,
            'opp_count' : includedCount,
            'amount' : includedAmount
        };

        this.context.forecasts.set("updatedTotals", totals);
    },

    /**
     * Event Handler for updating the worksheet by a selected user
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedUser:function (selectedUser) {
        this.selectedUser = selectedUser;
        if(!this.showMe()){
        	return false;
        }
        this._collection.url = this.createURL();
        this._collection.fetch();
    },

    /**
     * Event Handler for updating the worksheet by a selected category
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedCategory:function (params) {
        // Set the filters for the datatable then re-render
        if(params.id == "Committed")
        {
            $.fn.dataTableExt.afnFiltering.push (
                function(oSettings, aData, iDataIndex)
                {
                    var val = $(aData[0]).html();
                    return /(value="100" selected=""|checked)/.test(val);
                }
            );
        } else {
            //Remove the filters
            $.fn.dataTableExt.afnFiltering.splice(0, $.fn.dataTableExt.afnFiltering.length);
        }
        this.refresh();
    },

    /**
     * Event Handler for updating the worksheet by a timeperiod id
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedTimePeriod:function (params) {
        this.timePeriod = params.id;
        if(!this.showMe()){
        	return false;
        }
        this._collection.url = this.createURL();
        this._collection.fetch();
    },

    /**
     * Formats the additional details div when a user clicks a row in the grid
     *
     * @param dRow the row from the datagrid that user has clicked on
     * @return {String} html output to be shown to the user
     */
    formatAdditionalDetails:function (dRow) {
        // grab reference to the datatable
        var dTable = this.gTable;
        // get row data from datatable
        var data = dTable.fnGetData(dRow);
        // grab column headings array
        var colHeadings = this.getColumnHeadings(dTable);

        // TEMPORARY PLACEHOLDER OUTPUT - inline CSS, no class
        // this will all be changed once we have a more firm requirement for what should display here
        var output = '<table cellpadding="5" cellspacing="0" border="0" style="margin: 10px 0px 10px 50px">';
        output += '<tr><td>' + colHeadings[0] + '</td><td>' + data[0] + '</td></tr>';
        output += '<tr><td>' + colHeadings[1] + '</td><td>' + data[1] + '</td></tr>';
        output += '<tr><td>' + colHeadings[2] + '</td><td>' + data[2] + '</td></tr>';
        output += '<tr><td>' + colHeadings[3] + '</td><td>' + data[3] + '</td></tr>';
        output += '<tr><td>' + colHeadings[4] + '</td><td>' + data[4] + '</td></tr>';
        output += '</table>';

        return output;
    },

    /**
     * Returns an array of column headings
     *
     * @param dTable datatable param so we can grab all the column headings from it
     * @param onlyVisible -OPTIONAL, defaults true- if we want to return only visible column headings or not
     * @return {Array} column heading title strings in an array ["heading","heading2"...]
     */
    getColumnHeadings:function (dTable, onlyVisible) {
        // onlyVisible needs to default to true if it is not false
        if (onlyVisible !== false) {
            onlyVisible = typeof onlyVisible !== 'undefined' ? onlyVisible : true;
        }

        var cols = dTable.fnSettings().aoColumns;
        var retColumns = [];

        for (var i in cols) {

            var title = this.app.lang.get(cols[i].sTitle);

            if (onlyVisible) {
                if (cols[i].bVisible) {
                    retColumns.push(title);
                }
            } else {
                retColumns.push(title);
            }
        }

        return retColumns;
    },

    /***
     * Checks current gTable to see if a particular column name exists
     * @param columnName the column sName you're checking for.  NOT the Column sTitle/heading
     * @return {Boolean} true if it exists, false if not
     */
    hasColumn:function(columnName) {
        var containsColumnName = false;
        var cols = this.gTable.fnSettings().aoColumns;

        for (var i in cols) {
            if(cols[i].sName == columnName)  {
                containsColumnName = true;
                break;
            }
        }

        return containsColumnName;
    }
})
