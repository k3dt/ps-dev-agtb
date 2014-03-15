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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
(function(app) {
    app.events.on('app:init', function() {

        /**
         * Pagination plugin helps to fetch the next offset records to append
         * on the next table body element.
         * For the dashlet view, the pagination bottom link will be added
         * automatically. To avoid the default pagination link, please add the
         * below code.
         *
         *     @example
         *     <pre><code>
         *     this.meta.customPagination = true;
         *     </code></pre>
         *
         *     or assigning in the metadata
         *
         *     <pre><code>
         *     array(
         *         ...
         *         customPagination => true,
         *     );
         *     </code></pre>
         *
         * In order to use appending DOM element, the below items are required.
         * - this.tbodyTag: The parent dom selector
         *  where each row template is appended.
         * - Row template (row.hbs): Row level template.
         *  The model will be bound in this.rowModel.
         *
         *     @example
         *     <pre><code>
         *     {{#with rowModel}}
         *         {{#each ../meta.fields}}
         *              {{field ../../this mode=../this}}
         *         {{/each}}
         *     {{/with}}
         *     </code></pre>
         *
         * Otherwise, it will refresh the DOM with the new data collection.
         **/
        app.plugins.register('Pagination', ['view'], {
            onAttach: function(component, plugin) {
                this.on('init', function() {
                    this.rowTemplate = app.template.getView(this.name + '.row', this.module) ||
                        app.template.getView(this.name + '.row') ||
                        this.rowTemplate;
                    if (!_.contains(this.plugins, 'Dashlet') || this.meta.config) {
                        return;
                    }
                    if (this.layout) {
                        this.layout.on('init', this._initPaginationBottom, this);
                    }
                }, this);
                this.on('render', function() {
                    this.$tableBody = this.$(this.tbodyTag || 'tbody');
                }, this);
            },

            /**
             * Add pagination view in the bottom of the dashlet layout.
             * @private
             */
            _initPaginationBottom: function() {
                if (!this.layout) {
                    return;
                }
                var pageComponent = this.layout.getComponent('list-bottom');
                if (pageComponent) {
                    return;
                }
                pageComponent = app.view.createView({
                    context: this.context,
                    name: 'list-bottom',
                    className: 'block-footer',
                    meta: {
                        template: 'list-bottom.dashlet-bottom'
                    },
                    module: this.module,
                    primary: false,
                    layout: this.layout
                });
                this.layout.addComponent(pageComponent);
            },

            /**
             * Paginates a collection and handles rendering appending DOM
             * element.
             *
             * @param {Object} options (optional) Fetch options.
             * See {@link BeanCollection#fetch} for details.
             */
            getNextPagination: function(options) {
                var beanCollection;

                if (_.isFunction(this.getPaginationCollection)) {
                    beanCollection = this.getPaginationCollection();
                }
                beanCollection = beanCollection || this.collection;
                if (!beanCollection.dataFetched) {
                    return;
                }

                options = options || {};
                var defaultOnSuccess = options.success;
                options.success = null;
                if (_.isFunction(this.getPaginationOptions)) {
                    options = _.extend({}, options, this.getPaginationOptions() || {});
                }

                // Indicates records will be added to those already loaded in to view
                options.add = true;
                var origOnSuccess = options.success;
                options.success = _.bind(function(collection, data) {
                    if (_.isFunction(defaultOnSuccess)) {
                        defaultOnSuccess(collection, data);
                    }
                    if (_.isFunction(origOnSuccess)) {
                        origOnSuccess(collection, data);
                    }
                    if (this.disposed) {
                        return;
                    }
                    if (this.module !== collection.module) {
                        this.rowTemplate = app.template.getView(this.name + '.row', collection.module) ||
                            this.rowTemplate;
                    }
                    if (_.isEmpty(this.$tableBody) || !this.rowTemplate) {
                        app.logger.warn('Create a row.hbs template to avoid a full render.');
                        this.render();
                        return;
                    }
                    _.each(data, function(model) {
                        this._renderRow(this.collection.get(model.id));
                    }, this);
                    this.trigger('render');
                }, this);

                if (this.limit) {
                    options.limit = this.limit;
                }
                beanCollection.paginate(options);
            },

            /**
             * Render partial row element by appending to
             * the parent list element.
             *
             * @param {Backbone.Model} model Row model.
             * @private
             */
            _renderRow: function(model) {
                this.rowModel = model;
                var $row = $(this.rowTemplate(this)),
                    self = this;
                this.$tableBody.append($row);
                $row.find('span[sfuuid]').each(function() {
                    var $this = $(this),
                        sfId = $this.attr('sfuuid');
                    if (self.fields[sfId]) {
                        self.fields[sfId].setElement($this);
                        self.fields[sfId].render();
                    }
                });
            }
        });
    });
})(SUGAR.App);
