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
/**
 * @class View.Views.Base.ModalConfirmView
 * @alias SUGAR.App.view.views.BaseModalConfirmView
 * @extends View.View
 */
({
    events: {
        'click [name=close_button]' : 'close',
        'click [name=ok_button]' : 'ok'
    },
    initialize: function(options) {
        this.message = options.layout.confirmMessage;
        app.view.View.prototype.initialize.call(this, options);
    },
    close: function(evt) {
        this.layout.context.trigger("modal:close");
    },
    ok: function(evt) {
        this.layout.context.trigger("modal:callback");
    }
})
