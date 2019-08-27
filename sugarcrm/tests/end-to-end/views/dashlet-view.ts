/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

import BaseView from './base-view';

/**
 * Represents Dashboard view.
 *
 * @class DashletView
 * @extends BaseView
 */
export default class DashletView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.dashlet-cell',
            header: '.dashlet-header',
            buttons: {
                cog: '.btn.btn-invisible.dropdown-toggle',
            },
            menuItems: {
                $: '.dropdown-menu',
                edit: 'a[name="edit_button"]',
                refresh: 'a[name="refresh_button"]',
                remove: 'a[name="remove_button"]',
            },
            content: 'dashlet-content'
        });
    }

    /**
     * Perform standard dashlet operations such as edit, refresh or remove
     * Note: Those operations are only available to Admin
     *
     * @param {string} action to perform
     * @returns {Promise<void>}
     */
    public async performAction(action) {
        await this.driver.click(this.$(`buttons.cog`));
        await this.driver.click(this.$(`menuItems.` + action));
    }

    /**
     *  Expand dashlet's actions (aka '+') dropdown
     */
    public async expandPlusDropdown() {
        let selector = this.$('header.plusButton');
        await this.driver.click(selector);
    }

    /**
     * Select action from dashlet's actions (aka '+') dropdown
     *
     * @param {string} buttonName action to select
     */
    public async clickButton(buttonName: string) {
        await this.expandPlusDropdown();
        let selector = this.$(`header.menuItems.${buttonName}`);
        await this.driver.click(selector);
    }

    /**
     * Navigate to specified tab
     *
     * @param {string} index
     */
    public async navigateToTab(index: string) {
        // Only click tab if it is not already active
        let isSelected = await this.driver.isElementExist(this.$('tabs.activeTab', {index}));
        if (!isSelected) {
            let selector = this.$('tabs.tab', {index});
            await this.driver.click(selector);
        }
    }

    /**
     * Toggle between 'today' or 'future' filters in the dashlet's toolbar
     *
     * @param {string} filterName
     */
    public async setFilter(filterName: string) {
        let selector = this.$('filter', {filterName});
        await this.driver.click(selector);
    }

    /**
     * Toggle between 'My Activities' and 'Team Activities' in the dashlet's toolbar
     *
     * @param {string} visibilityName
     */
    public async setVisibility(visibilityName: string) {
        let selector = this.$('visibility', {visibilityName});
        await this.driver.click(selector);
    }

    /**
     * Get record count displayed over the tab name in various dashlets like
     * Planned Activities or Active Tasks dashlet
     *
     * @param {string} index tab index
     * @return {string} number of records displayed under the tab name in the dashlet
     */
    public async getNumRecordsInTab(index: string): Promise<string> {
        let selector = this.$('tabs.record_count', {index});
        return this.driver.getText(selector);
    }
}
