({

    /**
     * Used to determine whether or not to visibly show the Commit button
     */
    showCommitButton : true,

    /**
     * Used to determine whether or not the Commit button is enabled
     */
    commitButtonEnabled: false,

    /**
     * Used to determine whether the config setting cog button is displayed
     */
    showConfigButton: false,
            
    /**
     * Adds event listener to elements
     */
    events: {
        "click a[id=commit_forecast]" : "triggerCommit",
        "click a[id=save_draft]" : "triggerSaveDraft",
        "click a[name=forecastSettings]" : "triggerConfigModal",
        "click a.drawerTrig" : "triggerRightColumnVisibility"
    },

    initialize: function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this.showConfigButton = (app.user.getAcls()['Forecasts'].admin == "yes");
    },

    /**
     * Fires during initialization and if any data changes on this model
     */
    bindDataChange: function() {
        var self = this;
        if(this.context && this.context.forecasts) {
            this.context.forecasts.on("change:selectedUser", function(context, user) {
                var oldShowButtons = self.showCommitButton;
                self.showCommitButton = self.checkShowCommitButton(user.id);
                // if show buttons has changed, need to re-render
                if(self.showCommitButton != oldShowButtons) {
                    self._render();
                }
            });
            this.context.forecasts.on("change:commitButtonEnabled", this.commitButtonStateChangeHandler, self);
            this.context.forecasts.on("change:reloadCommitButton", function(){
            	self._render();
            }, self);
            this.context.forecasts.worksheet.on("change", this.showSaveButton, self);
            this.context.forecasts.worksheetmanager.on("change", this.showSaveButton, self);
        }
    },

    /**
     * Renders the component
     */
    _renderHtml : function(ctx, options) {
        app.view.View.prototype._renderHtml.call(this, ctx, options);
        if(this.showCommitButton) {
            if(this.commitButtonEnabled) {
                this.$el.find('a[id=commit_forecast]').removeClass('disabled');
            } else {
                this.$el.find('a[id=commit_forecast]').addClass('disabled');               
            }
        }        
    },
        
    /**
     *	Shows the save button
     * 
     */
    showSaveButton: function(){
    	var self = this;
    	var worksheet = this.context.forecasts[this.context.forecasts.get("currentWorksheet")];
    	
		_.each(worksheet.models, function(model, index){
			var isDirty = model.get("isDirty");
			if(typeof(isDirty) == "boolean" && isDirty ){
				self.context.forecasts.set({commitButtonEnabled: true});
				self.$el.find('a[id=save_draft]').removeClass('disabled');
				//if something in the worksheet is dirty, we need to flag the entire worksheet as dirty.
				worksheet.isDirty = true;
			}
		});
    	
    },

    /**
     * Event Handler for when the context commitButtonEnabled variable changes
     * @param context
     * @param commitButtonEnabled boolean value for the changed commitButtonEnabled from the context
     */
    commitButtonStateChangeHandler: function(context, commitButtonEnabled){
    	var commitbtn =  this.$el.find('#commit_forecast');
    	if(commitButtonEnabled){
    		commitbtn.removeClass("disabled");
    	}
    	else{
    		commitbtn.addClass("disabled");
    	}
    },

    /**
     * Sets the flag on the context so forecastsCommitted.js will call commitForecast
     * as long as commit button is not disabled
     */
    triggerCommit: function() {
    	var commitbtn =  this.$el.find('#commit_forecast');
    	var savebtn = this.$el.find('#save_draft');
    	var worksheet = this.context.forecasts[this.context.forecasts.get("currentWorksheet")];
    	var self = this;
    	var modelCount = 0;
		var saveCount = 0;
        if(!commitbtn.hasClass("disabled")){
    		var models = worksheet.models;
    		_.each(models, function(model, index){
    			var isDirty = model.get("isDirty");
    			if(model.get("version") == 0 || (typeof(isDirty) == "boolean" && isDirty)){
    				var values = {};
    				modelCount++;
                    values["draft"] = 0;
                    values["isDirty"] = false;
                    values["timeperiod_id"] = self.context.forecasts.get("selectedTimePeriod").id;
        			values["current_user"] = app.user.get('id');
        			model.set(values, {silent:true});
    				model.url = worksheet.url.split("?")[0] + "/" + model.get("id");
    				model.save({}, {success:function(){
    					saveCount++;
    					if(saveCount === modelCount){
    						self.context.forecasts.set({reloadWorksheetFlag: true});
    					}
    				}});
    				worksheet.isDirty = false;
    			}    			    				
    		});
    		
    		savebtn.addClass("disabled");
    		self.context.forecasts.set({commitForecastFlag: true});
    	}        
    },

    /**
     * Triggers the event expected by the modal layout to show the config panels
     */
    triggerConfigModal: function() {
        var params = {
            title: app.lang.get("LBL_FORECASTS_CONFIG_TITLE", "Forecasts"),
            components: [{layout:"forecastsWizardConfig"}],
            span: 10
        };
        var callback = function(){};

        if(app.user.getAcls()['Forecasts'].admin == "yes") {
            this.layout.trigger("modal:forecastsWizardConfig:open", params, callback);
        }
    },

    /**
     * Handles Save Draft button being clicked
     */
    triggerSaveDraft: function() {
        //todo: implement save draft functionality, or trigger flag on context if save is handled elsewhere
    	var savebtn = this.$el.find('#save_draft');
    	if(!savebtn.hasClass("disabled")){
    		var worksheet = this.context.forecasts[this.context.forecasts.get("currentWorksheet")];
    		var self = this;
    		var modelCount = 0;
    		var saveCount = 0;
    		_.each(worksheet.models, function(model, index){
    			var isDirty = model.get("isDirty");
    			if(typeof(isDirty) == "boolean" && isDirty ){
    				modelCount++;
    				model.set({draft: 1}, {silent:true});
    				model.save({}, {success:function(){
    					saveCount++;
    					if(saveCount === modelCount){
    						self.context.forecasts.set({reloadWorksheetFlag: true});
    					}
    				}});
    				model.set({isDirty: false}, {silent:true});
    				worksheet.isDirty = false;
    			}    			    				
    		});
    		
    		savebtn.addClass("disabled");
    		this.context.forecasts.set({commitButtonEnabled: true});
    	}
    	
    },

    /**
     * returns boolean value indicating whether or not to show the commit button
     */
    checkShowCommitButton: function(id) {
        return app.user.get('id') == id;
    },

    /**
     * Toggle the right Column Visibility
     * @param evt
     */
    triggerRightColumnVisibility : function(evt) {
        // we need to use currentTarget so we always get the a and not any child that was clicked on
        var el = $(evt.currentTarget);
        el.find('i').toggleClass('icon-chevron-right icon-chevron-left');

        // we need to go up and find the parent containg the two rows
        el.parents('#contentflex').find('>div.row-fluid').find('>div:first').toggleClass('span8 span12');
        el.parents('#contentflex').find('>div.row-fluid').find('>div:last').toggleClass('span4 hide');

        // toggle the "event" to make the chart stop rendering if the sidebar is hidden
        this.context.forecasts.set({hiddenSidebar: el.find('i').hasClass('icon-chevron-left')});
    }

})