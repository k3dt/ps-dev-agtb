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
 * Represents PreviewHeader view.
 *
 * @class PreviewHeaderView
 * @extends BaseView
 */
export default class PreviewHeaderView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.preview-headerbar',
            buttons: {
                edit: '.preview-edit-btn',
                cancel: 'a[name="cancel_button"]',
                save: 'a[name="save_button"]',
            }
        });

    }
}
