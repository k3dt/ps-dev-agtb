({
    minChars: 1,
    extendsFrom: 'RelateField',
    fieldTag: 'input.select2[name=parent_name]',
    typeFieldTag: 'select.select2[name=parent_type]',
    _render: function() {
        var result, self = this;
        app.view.invokeParent(this, {type: 'field', name: 'relate', method: '_render'});
        if(this.tplName === 'edit') {
            this.checkAcl('access', this.model.get('parent_type'));

            var inList = (this.view.name === 'recordlist') ? true : false;

            this.$(this.typeFieldTag).select2({
                dropdownCssClass: inList?'select2-narrow':'',
                containerCssClass: inList?'select2-narrow':'',
                width: inList?'off':'100%',
                minimumResultsForSearch: 5
            }).on("change", function(e) {
                var module = e.val;
                self.checkAcl.call(self, 'edit', module);
                self.setValue({
                    id: '',
                    value: '',
                    module: module
                });
                self.$(self.fieldTag).select2('val', '');
            });


            if(this.model.get(this.def.type_name) !== this.$(this.typeFieldTag).val()) {
                this.model.set(this.def.type_name, this.$(this.typeFieldTag).val());
            }

            if(app.acl.hasAccessToModel('edit', this.model, this.name) === false) {
                this.$(this.typeFieldTag).select2("disable");
            } else {
                this.$(this.typeFieldTag).select2("enable");
            }
        } else if(this.tplName === 'disabled'){
            this.$(this.typeFieldTag).select2('disable');
        }
        return result;
    },
    _getRelateId: function() {
        return this.model.get("parent_id");
    },
    format: function(value) {
        this.def.module = this.getSearchModule();
        var moduleString = app.lang.getAppListStrings('moduleListSingular'),
            module;
        if(this.getSearchModule()) {
            if (!moduleString[this.getSearchModule()]) {
                app.logger.error("Module '" + this.getSearchModule() + "' doesn't have singular translation.");
                // graceful fallback
                module = this.getSearchModule();
            } else {
                module = moduleString[this.getSearchModule()];
            }
        }

        this.context.set("record_label", {
            field: this.name,
            label: (this.tplName === 'detail') ? module : app.lang.get(this.def.label, this.module)
        });
        this._buildRoute();

        return value;
    },
    checkAcl: function(action, module) {
        if(app.acl.hasAccess(action, module) === false) {
            this.$(this.typeFieldTag).select2("disable");
        } else {
            this.$(this.typeFieldTag).select2("enable");
        }
    },
    setValue: function(model) {
        if (model) {
            var silent = model.silent || false;
            if(app.acl.hasAccess(this.action, this.model.module, this.model.get('assigned_user_id'), this.name)) {
                if(model.module) {
                    this.model.set('parent_type', model.module, {silent: silent});
                }
                this.model.set('parent_id', model.id, {silent: silent});
                this.model.set('parent_name', model.value, {silent: silent});
            }
        }
    },
    getSearchModule: function() {
        return this.model.get('parent_type') || this.$(this.typeFieldTag).val();
    },
    getPlaceHolder: function() {
        return  app.lang.get('LBL_SEARCH_SELECT', this.module);
    },
    unbindDom: function() {
        this.$(this.typeFieldTag).select2('destroy');
        app.view.invokeParent(this, {type: 'field', name: 'relate', method: 'unbindDom'});
    },

    /**
     * {@inheritDoc}
     * Avoid rendering process on select2 change in order to keep focus.
     */
    bindDataChange: function() {
        this._super('bindDataChange');
        if (this.model) {
            this.model.on('change:parent_type', function() {
                if (_.isEmpty(this.$(this.typeFieldTag).data('select2'))) {
                    this.render();
                } else {
                    this.$(this.typeFieldTag).select2('val', this.model.get('parent_type'));
                }
            }, this);
        }
    }
})
