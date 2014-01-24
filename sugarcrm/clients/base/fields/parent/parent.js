/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    minChars: 1,
    extendsFrom: 'RelateField',
    fieldTag: 'input.select2[name=parent_name]',
    typeFieldTag: 'select.select2[name=parent_type]',
    _render: function() {
        var result, self = this;
        this._super("_render");
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

            //FIXME: Once select2 upgrades to 3.4.3, this code should use on('select2-focus')
            var plugin = this.$(this.typeFieldTag).data('select2');
            if (plugin) {
                plugin.focusser.on('focus', _.bind(_.debounce(this.handleFocus, 0), this));
            }
            var domParentTypeVal = this.$(this.typeFieldTag).val();
            if(this.model.get(this.def.type_name) !== domParentTypeVal) {
                //SP-1654: Prevents quickcreate from incorrectly considering model changed
                this.model.set(this.def.type_name, domParentTypeVal, {silent: true});
                this.model.setDefaultAttribute(this.def.type_name, domParentTypeVal);
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
                    this.model.removeDefaultAttribute('parent_type');
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
        this._super("unbindDom");
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
