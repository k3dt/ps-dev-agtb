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
 * Represents a RLI secion of Opportunity page layout.
 *
 * @class RecordLayout
 * @extends BaseView
 */
export default class RliListItemView extends BaseView {

    constructor(options) {

        super(options);

        const index = options.index;

        this.selectors = this.mergeSelectors({
            $: `tr:nth-child(${index})`,
            buttons: {
                addRLI: '.rowaction.btn.addBtn',
                removeRLI:'.rowaction.btn.deleteBtn'
            }
        });
    }

    public async pressButton(itemName) {
        await this.driver.click(this.$(`buttons.${itemName}`));
    }

}