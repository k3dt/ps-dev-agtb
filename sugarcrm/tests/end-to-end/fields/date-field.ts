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
 * @class DateField
 * @extends BaseField
 */
export default class DateField extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: 'input'
            }
        });
    }

    public async setValue(val: any): Promise<void> {
        await this.driver.setValue(this.$('field.selector'), val);
    }
}

export const Edit = DateField;

export class Detail extends DateField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div'
            }
        });

    }
}

export class List extends DateField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div'
            }
        });

    }

}

export const Preview = Detail;
