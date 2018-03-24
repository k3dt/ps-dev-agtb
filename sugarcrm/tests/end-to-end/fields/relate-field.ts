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

import BaseField from './text-field';
import {seedbed} from '@sugarcrm/seedbed';

/**
 * @class RelateField
 * @extends BaseField
 */

export class Detail extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'a.ellipsis_inline'
            }
        });
    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await this.driver.getText(selector);

        return value.toString().trim();

    }
}

export class List extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: '.ellipsis_inline a'
            }
        });

    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await this.driver.getText(selector);

        return value.toString().trim();

    }

}


export class Edit extends BaseField {

    private itemSelector: string;
    private inputSelector: string;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: '.select2-container.select2',
            }
        });

        this.itemSelector = '.select2-result-label=';
        this.inputSelector = '.select2-input.select2-focused';

    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await this.driver.getText(selector);

        return value.toString().trim();

    }

    public async setValue(val: any): Promise<void> {

        await this.driver.click(this.$('field.selector'));
        await this.driver.setValue(this.inputSelector, val);

        // TODO remove this pause later!!!, waitForApp should handle this case for select2 control
        await this.driver.pause(4000);
        await this.driver.waitForApp();
        await this.driver.click(`${this.itemSelector}${val}`);
    }

}

export const Preview = Detail;
