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
import {seedbed} from '@sugarcrm/seedbed';
import {BaseField} from './base-field';

/**
 * @class TextareaField
 * @extends BaseField
 */
export default class TextareaField extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: 'textarea'
            }
        });
    }

    public async setValue(val: any): Promise<void> {
        await this.driver.setValue(this.$('field.selector'), val);
    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await this.driver.getValue(this.$('field.selector'));

        return value.toString().trim();

    }
}


export const Edit = TextareaField;


export class Detail extends TextareaField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div'
            }
        });

    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await this.driver.getText(selector);

        return value.toString().trim();

    }
}

export class DetailAddress extends TextareaField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'span'
            }
        });

    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await this.driver.getText(selector);

        return value.toString().trim();

    }
}

export class List extends TextareaField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div'
            }
        });

    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await this.driver.getText(this.$('field.selector'));

        return value.toString().trim();

    }

}

export const Preview = Detail;

