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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    plugins: ['Dropdown', 'Tooltip'],

    events: {
        'click [data-action=link]': 'linkClicked',
        'click #userTab' : 'hideSubmenuItems',
        'click #userActions' : 'closeSubmenu'
    },

    /**
     * Visibility property for available submenu.
     *
     * @property
     */
    displaySubmenu: false,

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        app.events.on("app:sync:complete", this.render, this);
        app.events.on("app:sync:complete", this.setCurrentUserData, this);
        app.events.on("bwc:profile:entered", this.bwcProfileEntered, this);
        app.events.on("bwc:avatar:removed", this.bwcAvatarRemoved, this);
        app.user.on("change:picture", this.setCurrentUserData, this);
        app.user.on("change:full_name", this.setCurrentUserData, this);
    },

    /**
     * Render profile actions dropdown menu
     * @private
     */
    _renderHtml: function() {
        // FIXME check why the router is not loaded before all the other components are rendered
        if (!app.router || !app.api.isAuthenticated() || app.config.appStatus === 'offline') {
            return;
        }

        if(!_.isEmpty(this.meta)){
            this.menulist = this.filterAvailableMenu(this.meta);
        }
        app.view.View.prototype._renderHtml.call(this);
    },
    /**
     * Handle the button click event.
     * Stop event propagation in order to keep the dropdown box.
     *
     * @param {Event} evt Mouse event.
     */
    linkClicked: function(evt) {
        evt.preventDefault();
        evt.stopPropagation();
        var $menuItem = this.$(evt.currentTarget),
            $submenu = $menuItem.closest('li').find('.dropdown-inset');
        $submenu.toggle();
        // Handles the highlight of the dropdown arrow
        $menuItem.toggleClass("open");

        // Handles css properties when height exceed 330, the scrollbar appears
        var maxHeight = 330,
            currentHeight = this.$("#fullmenu").outerHeight();
        this.$('.dropdown-submenu').toggleClass('with-scroll', currentHeight >= maxHeight);
    },
    /**
     * Closes the submenu
     */
    closeSubmenu: function(){
        this.$('.dropdown-submenu').removeClass("open");
    },
    /**
     * Hides the submenu items
     */
    hideSubmenuItems: function() {
        this.$('.dropdown-inset').hide();
    },

    /**
     * Filters menu metadata
     * @param Array menuMeta
     * @return {Array}
     */
    filterAvailableMenu: function(menuMeta){
        var result = [];
        _.each(menuMeta,function(item){
            item = this.filterMenuProperties(item);
            if(!_.isEmpty(item['acl_module'])) {
                if(app.acl.hasAccess(item.acl_action, item.acl_module)) {
                    result.push(item);
                }
            } else if(item['acl_action'] === 'admin' && item['label'] === 'LBL_ADMIN') {
                //Edge case for admin link. We only show the Admin link when
                //user has the "Admin & Developer" or "Developer" (so developer
                //in either case; see SP-1827)
                if (app.acl.hasAccessToAny('developer')) {
                    result.push(item);
                }
            } else {
                // push the menu item if current user is a admin or
                // current user has access to admin or current user
                // is a developer, the last conditon is for
                // if all three acls checks are not met, it will only
                // push if the menu item is not admin, which skips the admin menu
                if(app.acl.hasAccess('admin', 'Administration') ||
                    app.acl.hasAccessToAny('developer') ||
                    item['acl_action'] !== 'admin') {
                    result.push(item);
                }
            }

        },this);
        return result;
    },

    /**
     * Filters single menu data
     * @param Array menu data
     * @return {Array}
     */
    filterMenuProperties:function(singleItem){
        if(singleItem['label'] === 'LBL_PROFILE'){
            singleItem['img_url'] = this.pictureUrl;
            //Add userId to default route
            if (singleItem['route'] === '#bwc/index.php?module=Users&action=DetailView&record=') {
                singleItem['route'] += this.userId;
            }
        }
        return singleItem;
    },
    //TODO: Remove once bwc is completely pruned out of the product
    bwcProfileEntered: function() {
        //Refetch latest user data (since bwc updated avatar); reset
        var self = this;
        app.user.load(function() {
            self.setCurrentUserData();
        });
    },
    //This will get called when avatar is removed from bwc User profile edit (SP-1949)
    //TODO: Remove once bwc is completely pruned out of the product
    bwcAvatarRemoved: function() {
        app.user.set("picture", '');//so `this.pictureUrl` is falsy and default avatar kicks in on .hbs template
        this.setCurrentUserData();
    },
    /**
     * Sets the current user's information like full name, user name, avatar, etc.
     * @protected
     */
    setCurrentUserData: function() {
        this.fullName = app.user.get("full_name");
        this.userName = app.user.get("user_name");
        this.userId = app.user.get('id');
        var picture = app.user.get("picture");

        this.pictureUrl = picture ? app.api.buildFileURL({
            module: "Users",
            id: app.user.get("id"),
            field: "picture"
        }, {
            cleanCache: true
        }) : '';

        this.render();
    },
    _dispose: function() {
        if (app.user) app.user.off(null, null, this);
        app.view.View.prototype._dispose.call(this);
    }
})
