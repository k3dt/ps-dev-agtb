({
    events: {
        'click .reminders-pills': function(e) { this.pillSwitcher(e, this) },
        'click .reminders-container': 'persistMenu',
        'click .reminders': 'handleEscKey',
        'click .reminders-add': 'submitReminder',
        'keyup .reminders-subject':'submitReminder',
        'keyup .reminders-date':'submitReminder',
        'focus .reminders-date': 'showDatePicker',
        'click .reminders-status': 'changeStatus',
        'click .reminders-remove': 'removeReminder'
    },

    tagName: 'span',
    open: false,

    initialize: function(options) {
        var self = this;

        app.view.View.prototype.initialize.call(this, options);

        app.events.on("app:sync:complete", function() {
            self.collection = app.data.createBeanCollection("Tasks");
            self.collection.fetch({myItems: true, success: function(collection) {

                collection.modelList = {
                    today: [],
                    overdue: [],
                    upcoming: []
                };

                _.each(collection.models, function(model) {
                    collection.modelList[self.getTaskType(model.attributes.date_due)].push(model);
                });

                self.overduePillActive = true;
                self.render();
            }});

            self.bindDataChange();
        });
        app.events.on("app:login:success", this.render, this);
        app.events.on("app:logout", this.render, this);
        app.events.on("app:view:change", function(layout, obj) {
            if( layout == "record" ) {
                    this.modelID = obj.modelId;
            }
            this.currLayout = layout;
            this.currModule = obj.module;
            this.open = false;
            self.render();
        }, this);

        app.events.on("app:view:reminders-record:refresh", function(model, action) {
            var taskType = self.getTaskType(model.attributes.date_due),
                givenModel = model;

            if( self.collection ) {
                switch(action) {
                    case "create":
                        self.collection.add(givenModel);
                        self.collection.modelList[taskType].push(givenModel);
                        break;
                    case "update_status":
                        var record = self.collection.modelList[taskType];

                        self.model = self.collection.get(givenModel.id);
                        self.model.attributes.status = givenModel.attributes.status;
                        var listModel = _.find(record, function(param) {
                            return (givenModel.id == param.id);
                        });
                        listModel.attributes.status = givenModel.attributes.status;
                        break;
                    case "delete":
                        var record = self.collection.modelList[taskType],
                            listModel = _.find(record, function(param) {
                                return (givenModel.id == param.id);
                            }),
                            listModelIndex = _.indexOf(record, listModel);

                        self.collection.remove(givenModel, {silent: true});
                        record.splice(listModelIndex, 1);
                        break;
                }
                self.open = false;
                self.render();
            }
        });
    },
    persistMenu: function(e) {
        // This will prevent the dropup menu from closing
        // when clicking anywhere on it
        e.stopPropagation();
    },
    handleEscKey: function() {
        var self = this;
        _.defer(function() {
            self.$(".reminders-subject").focus();
        });

        if( !(this.$(".reminders-widget").is(".open")) ) {
            // attach namespaced keyup event listener
            this.$(".reminders-subject,.reminders-date").on("keyup.escape", function() {
                // If esc was pressed
                if( event.keyCode == 27 ) {
                    self.$(".reminders-widget").removeClass("open");
                    // remove event listener
                    self.$(".reminders-subject,.reminders-date").off("keyup.escape");
                }
            });
        }
        else {
            // remove event listener
            this.$(".reminders-subject,.reminders-date").off("keyup.escape");
        }
    },
    showDatePicker: function() {
        this.$(".reminders-date").datepicker({
            dateFormat: "yy-mm-dd"
        });
        $("#ui-datepicker-div").css("z-index", 1032);
        $("#ui-datepicker-div").on("click", function(e) {
            e.stopPropagation();
        });
    },
    pillSwitcher: function(e, scope) {
        var clickedEl = scope.$(e.target);
        var clickedIndex = scope.$(".reminders-pills").index(clickedEl.closest(".reminders-pills"));

        scope.$(".reminders-pills.active").removeClass("active");
        scope.$(".tab-pane.active").removeClass("active");
        clickedEl.closest(".reminders-pills").addClass("active");
        scope.$(scope.$(".tab-pane")[clickedIndex]).addClass("active");

        // this is "state-machine information" that will later get fed into render
        switch(clickedIndex) {
            case 0:
                scope.overduePillActive = true;
                scope.todayPillActive = false;
                scope.upcomingPillActive = false;
                scope.allPillActive = false;
                break;
            case 1:
                scope.overduePillActive = false;
                scope.todayPillActive = true;
                scope.upcomingPillActive = false;
                scope.allPillActive = false;
                break;
            case 2:
                scope.overduePillActive = false;
                scope.todayPillActive = false;
                scope.upcomingPillActive = true;
                scope.allPillActive = false;
                break;
            case 3:
                scope.overduePillActive = false;
                scope.todayPillActive = false;
                scope.upcomingPillActive = false;
                scope.allPillActive = true;
                break;
        }
    },
    getModelInfo: function(e) {
        var clickedEl = this.$(e.target).parents(".reminders-item-container")[0],
            modelIndex = (this.$(".tab-pane.active").children()).index(clickedEl),
            parentID = this.$(".tab-pane.active").attr("id"),
            record;

        switch(parentID) {
            case "pane1":
                record = this.collection.modelList['overdue'];
                break;
            case "pane2":
                record = this.collection.modelList['today'];
                break;
            case "pane3":
                record = this.collection.modelList['upcoming'];
                break;
            case "pane4":
                var taskType = this.getTaskType(this.collection.models[modelIndex].attributes.date_due);
                record = this.collection.modelList[taskType];
                modelIndex = _.indexOf(_.pluck(record, 'id'), this.collection.models[modelIndex].id);
                break;
        }

        return {index: modelIndex, modList: record};
    },
    removeReminder: function(e) {
        var self = this,
            modelInfo = this.getModelInfo(e),
            modelIndex = modelInfo.index,
            record = modelInfo.modList;

        this.model = this.collection.get(record[modelIndex].id);
        this.model.destroy({ success: function() {
            record.splice(modelIndex, 1);

            self.open = true;
            self.render();
            if( self.model.attributes.parent_id ) {
                app.events.trigger("app:view:reminders:refresh", self.model, "delete");
            }
        }});
    },
    changeStatus: function(e) {
        var modelInfo = this.getModelInfo(e),
            modelIndex = modelInfo.index,
            record = modelInfo.modList,
            taskStatusListStrings = app.lang.getAppListStrings('task_status_dom');

        this.model = this.collection.get(record[modelIndex].id);

        if( this.model.attributes.status == taskStatusListStrings['Completed'] ) {
            this.model.set({
                "status": taskStatusListStrings['Not Started']
            });
            record[modelIndex].attributes.status = taskStatusListStrings['Not Started'];
        }
        else {
            this.model.set({
                "status": taskStatusListStrings['Completed']
            });
            record[modelIndex].attributes.status = taskStatusListStrings['Completed'];
        }

        this.model.save();
        this.open = true;
        this.render();

        // call trigger only if we're updating a related record
        if( this.model.attributes.parent_id ) {
            app.events.trigger("app:view:reminders:refresh", this.model, "update_status");
        }
    },
    _renderHtml: function(){
        this.isAuthenticated = app.api.isAuthenticated();
        app.view.View.prototype._renderHtml.call(this);
    },
    getTaskType: function(reminderDate) {
        var todayBegin = new Date().setHours(0,0,0,0),
            todayEnd   = new Date().setHours(23,59,59,999),
            splitValue = /^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}:\d{2})\.*\d*([Z+-].*)$/.exec(reminderDate),
            reminderStamp  = app.date.parse(splitValue[1] + " " + splitValue[2]).getTime();

        // If the task falls in today's range
        if( reminderStamp >= todayBegin && reminderStamp <= todayEnd ) {
            return "today";
        }
        else if( reminderStamp < todayBegin ) {
            return "overdue";
        }
        else {
            return "upcoming";
        }
    },
    validateReminder: function(e) {
        var subjectEl = this.$(".reminders-subject"),
            subjectVal = subjectEl.val(),
            dateEl = this.$(".reminders-date"),
            dateVal = dateEl.val(),
            dateObj = app.date.parse(dateVal, app.date.guessFormat(dateVal));

        if( subjectVal == "" ) {
            // apply input error class
            subjectEl.parent().addClass("control-group error");
            subjectEl.one("keyup", function() {
                subjectEl.parent().removeClass("control-group error");
            });
        }
        else if( dateObj == "Invalid Date" || !(dateObj) ) {
            // apply input error class
            dateEl.parent().addClass("control-group error");
            dateEl.one("focus", function() {
                dateEl.parent().removeClass("control-group error");
            });
        }
        else {
            var datetime = dateVal + "T00:00:00+0000";

            this.model = app.data.createBean("Tasks", {
                "name": subjectVal,
                "assigned_user_id": app.user.get("id"),
                "date_due": datetime
            });

            if( this.$(".reminders-related").is(":checked") ) {
                this.model.set({"parent_id": this.modelID});
                this.model.set({"parent_type": this.currModule});
            }

            this.collection.add(this.model);
            this.model.save();
            this.collection.modelList[this.getTaskType(datetime)].push(this.model);

            // only trigger a refresh if the user wants to relate the to-do
            // to the current record
            if( this.model.attributes.parent_id ) {
                app.events.trigger("app:view:reminders:refresh", this.model, "create");
            }

            subjectEl.val("");
            dateEl.val("");
            this.open = true;
            this.render();
        }
    },
    submitReminder: function(e) {
        var target = this.$(e.target),
            dateInput = this.$(".reminders-date-container");

        if( target.hasClass("reminders-subject") || target.hasClass("reminders-date") ) {
            // show the date-picker input field
            if( !(dateInput.is(":visible")) ) {
                dateInput.css("display", "inline-block");
            }
            // if enter was pressed
            if( e.keyCode == 13 ) {
                // validate
                this.validateReminder(e);
            }
        }
        else {
            // Add button was clicked
            this.validateReminder(e);
        }
    },

    bindDataChange: function() {
        if( this.collection ) {
            this.collection.on("reset", this.render, this);
        }
    }
})