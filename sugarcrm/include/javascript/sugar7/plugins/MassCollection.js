/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

(function(app) {
    app.events.on('app:init', function() {
        app.plugins.register('MassCollection', ['view'], {

            /**
             * This plugin handles the collection (called the mass collection)
             * of selected items in listViews.
             * It has to be attached to any view that has `actionmenu` fields.
             *
             */
            onAttach: function() {
                this.on('init', function() {
                    this.createMassCollection();
                    this.context.on('mass_collection:add', this.addModel, this);
                    this.context.on('mass_collection:add:all', this.addAllModels, this);
                    this.context.on('mass_collection:remove', this.removeModel, this);
                    this.context.on('mass_collection:remove:all', this.removeAllModels, this);

                }, this);

                this.on('render', function() {
                    var massCollection = this.context.get('mass_collection');
                    if (this.collection.length !== 0) {
                        if (this.isAllChecked(massCollection)) {
                            massCollection.trigger('all:checked');
                        }
                    }
                }, this);
            },

            createMassCollection: function() {
                var massCollection = this.context.get('mass_collection');
                if (!massCollection) {
                    var MassCollection = app.BeanCollection.extend({
                        reset: function(models, options) {
                            this.filterDef = null;
                            this.entire = false;
                            Backbone.Collection.prototype.reset.call(this, models, options);
                        }
                    });
                    massCollection = new MassCollection();
                    this.context.set('mass_collection', massCollection);
                    massCollection.on('add remove reset', function() {
                        console.log(massCollection.length);
                    });

                    // Resets the mass collection on collection reset for non
                    // standalone mass collection.
                    if (!this.independentMassCollection) {
                        this.collection.on('reset', function() {
                            massCollection.reset();
                        });
                    }
                    return massCollection;
                }
            },

            /**
             * Adds a model to the mass collection.
             *
             * @param {Model} model The model to add.
             */
            addModel: function(model) {
                if (model.id) { //each selection
                    var massCollection = this.context.get('mass_collection');
                    if (!massCollection) {
                        return;
                    }
                    massCollection.add(model);
                    if (this.isAllChecked(massCollection)) {
                        massCollection.trigger('all:checked');
                    }
                }
            },

            /**
             * Adds all models of the view collection to the mass collection.
             *
             */
            addAllModels: function() {
                var massCollection = this.context.get('mass_collection');
                if (!massCollection) {
                    return;
                }
                if (!this.independentMassCollection) {
                    massCollection.reset(this.collection.models);
                } else {
                    massCollection.add(this.collection.models);
                }
            },

            /**
             * Removes a model from the mass collection.
             *
             * @param {Model} model The model to remove.
             */
            removeModel: function(model) {
                if (model.id) {
                    var massCollection = this.context.get('mass_collection');
                    if (!massCollection) {
                        return;
                    }
                    massCollection.remove(model);
                }
            },

            /**
             * Removes all models of the view collection to the mass collection.
             *
             */
            removeAllModels: function() {
                var massCollection = this.context.get('mass_collection');
                if (!massCollection) {
                    return;
                }
                if (!this.independentMassCollection) {
                    this.clearMassCollection(massCollection);
                } else {
                    massCollection.remove(this.collection.models);
                }
            },

            isAllChecked: function(massCollection) {
                var allChecked = _.every(this.collection.models, function(model) {
                    return _.contains(_.pluck(massCollection.models, 'id'), model.id);
                }, this);

                return allChecked;
            },

            /**
             * Destroy tooltips on dispose.
             */
            onDetach: function() {
                $(window).off('resize.' + this.cid);
                this.off('mass_collection:add');
                this.off('mass_collection:add:all');
                this.off('mass_collection:remove');
                this.off('mass_collection:remove:all');
            }
        });
    });
})(SUGAR.App);
