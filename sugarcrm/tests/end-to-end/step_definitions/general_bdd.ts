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
'use strict';

import {givenStepsHelper, whenStepsHelper, stepsHelper, Given} from '@sugarcrm/seedbed';
import {TableDefinition} from 'cucumber';
import ModuleMenuCmp from '../components/module-menu-cmp';
import ListView from '../views/list-view';
import RecordView from '../views/record-view';
import {Utils, When, Then, seedbed} from '@sugarcrm/seedbed';
import BaseView from '../views/base-view';
import * as _ from 'lodash';
import AlertCmp from '../components/alert-cmp';
import RecordLayout from '../layouts/record-layout';

Given(/^I am logged in$/,
    async function () {
        await useDefaultAcct('default', 'admin', 'asdf');
        await launchApp('launch', '');
        await whenStepsHelper.setUrlHashAndLogin('about');
    }, {waitForApp: true}
);

const useDefaultAcct = async function (isDefaultAccount: string, username: string, password: string): Promise<void> {
    await givenStepsHelper.useAccount(isDefaultAccount, username, password);
};

const launchApp = async function (launch: string, schemesList: string): Promise<void> {
    await givenStepsHelper.launchOrUpdate(launch, schemesList);
};

When(/^I update (\w+) \*(\w+) with the following values:$/,
    async function (module: string, name: string, table: TableDefinition) {
        // TODO: In the future we should check the current route and if we are already on the correct module/record
        await chooseModule(module);
        let view = await seedbed.components[`${module}List`].ListView;
        let record = await seedbed.cachedRecords.get(name);
        await chooseRecord({id: record.id}, view);
        let rec_view = await seedbed.components[`${name}Record`];
        await recordViewHeaderButtonClicks('Edit', rec_view);
        await recordViewHeaderButtonClicks('show more', rec_view);
        await provideRecordViewInput(rec_view.RecordView, table);
        await recordViewHeaderButtonClicks('Save', rec_view);
        await closeAlert();
    }, {waitForApp: true}
);

/**
 *  Copy (or cancel copying of) specified record in the record view
 *
 *  @example
 *      When I copy *Pr_1 record in Prospects record view with the following header values:
 *          | *    | first_name            | last_name             |
 *          | Pr_2 | Prospect<changeIndex> | Prospect<changeIndex> |
 *
 */
When(/^I (copy|cancel copy of) \*(\w+) record in (\w+) record view with the following header values:$/,
    async function (action: string, name: string, module: string, table: TableDefinition) {
        // TODO: In the future we should check the current route and if we are already on the correct module/record
        await chooseModule(module);
        let view = await seedbed.components[`${module}List`].ListView;
        let record = await seedbed.cachedRecords.get(name);

        // Navigate to record view of specified record
        await chooseRecord({id: record.id}, view);
        let rec_view = await seedbed.components[`${name}Record`];
        let drawer_view = await seedbed.components[`${module}Drawer`];

        // Click Copy button under Actions dropdown
        await recordViewActionsMenuItemClick('copy', rec_view);

        // Expand record view
        await recordViewHeaderButtonClicks('show more', drawer_view);

        // Provide record input
        // TODO: currently only Header changes are allowed.
        // TODO: AT-238 is filed to address this limitation
        await provideRecordViewInput(drawer_view.HeaderView, table);

        // Proceed with cancel or save
        switch (action) {
            case 'copy':
                await recordViewHeaderButtonClicks('Save', rec_view);
                await closeAlert();
                break;
            case 'cancel copy of':
                await recordViewHeaderButtonClicks('Cancel', rec_view);
                break;
            default:
                throw new Error(`Invalid action ${action} selected!`);
        }
    }, {waitForApp: true}
);


// TODO check if we're already on desired rec. view instead of navigating to the list view.
Then(/^(\w+) \*(\w+) should have the following values:$/,
    async function (module, name: string, table: TableDefinition) {
        let record = await seedbed.cachedRecords.get(name);
        let rec_view = await seedbed.components[`${name}Record`];

        await goToUrl(module + '/' + record.id);
        await this.driver.waitForApp();

        await recordViewHeaderButtonClicks('show more', rec_view);
        await this.driver.waitForApp();
        if (module === 'Quotes') {
            let rec = await seedbed.components[`${name}Record`].RecordView;
            await rec.expandQuotePanel('Billing_and_Shipping');
            await rec.expandQuotePanel('Quote_Settings');
            await rec.expandQuotePanel('Show_More');
            await this.driver.waitForApp();
        }
        await this.driver.waitForApp();
        await checkValues(rec_view, table);
    }, {waitForApp: true}
);

Then(/^I verify and close alert$/,
    async function (table: TableDefinition) {
        await verifyAlertProperties(table);
        await closeAlert();
    }, {waitForApp: true}
);

/**
 *  Edit (or cancel editing of) the specified record in the list view
 *
 *  @example
 *  When I edit *Pr_1 record in Prospects list view with the following values:
 *      | fieldName  | value      |
 *      | first_name | Prospect1  |
 *      | last_name  | Prospect1  |
 */
When(/^I (edit|cancel editing of) \*(\w+) record in (\w+) list view with the following values:$/,
    async function (action: string, name: string, module, table: TableDefinition) {

        const editButton = 'edit';
        const cancelButton = 'cancel';
        const saveButton = 'save';

        let list_view = await seedbed.components[`${module}List`].ListView;
        let record = await seedbed.cachedRecords.get(name);
        let listItem = list_view.getListItem({id: record.id});

        // Navigate to the list view
        await chooseModule(module);

        // Bring record to inline edit mode in the list view
        let isVisible = await listItem.isVisible(editButton);

        if (isVisible) {
            await listViewItemButtonClicks(editButton, listItem);
        } else {
            await listItem.openDropdown();
            await this.driver.waitForApp();
            await listViewItemButtonClicks(editButton, listItem);
            await this.driver.waitForApp();
        }

        // Provide inline input in the list view
        await provideListViewInput(listItem, table);

        switch (action) {

            case 'edit':
                await listItem.clickListButton(saveButton);
                await this.driver.waitForApp();

                // Close Confirmation alert
                await closeAlert();
                break;

            case 'cancel editing of':
                await listItem.clickListButton(cancelButton);
                break;

            default:
                throw new Error(`Invalid action ${action} selected!`);
        }
    }, {waitForApp: true}
);

/**
 *  Delete (or Cancel deletion of) specified record in the list or record view
 *
 *  @example
 *  When I cancel deletion of *Pr_1 record in Prospects record view
 */
When(/^I (delete|cancel deletion of) \*(\w+) record in (\w+) (list|record) view$/,
    async function (action: string, name: string, module: string, view: string) {

        const deleteButton = 'delete';
        const cancelButton = 'cancel';

        let list_view = await seedbed.components[`${module}List`].ListView;
        let record = await seedbed.cachedRecords.get(name);
        let listItem = list_view.getListItem({id: record.id});

        // Navigate to the list view
        await chooseModule(module);

        // Delete record in list view
        if (view === 'list') {

            // Bring record to inline edit mode in the list view
            let isVisible = await listItem.isVisible(deleteButton);

            if (isVisible) {
                await listViewItemButtonClicks(deleteButton, listItem);
            } else {
                await listItem.openDropdown();
                await listViewItemButtonClicks(deleteButton, listItem);
            }
            // Delete record in record view
        } else if (view === 'record') {

            await chooseRecord({id: record.id}, list_view);
            let rec_view = await seedbed.components[`${name}Record`];
            await recordViewActionsMenuItemClick('Delete', rec_view);
        } else {

            throw new Error(`Invalid view selected: ${view} !`);
        }

        switch (action) {

            case 'delete':
                // Proceed with deletion request
                await closeWarning('Confirm');
                // Close Confirmation alert
                await closeAlert();
                break;

            case 'cancel deletion of':
                await closeWarning('Cancel');
                await this.driver.waitForApp();
                break;

            default:
                throw new Error(`Invalid action ${action} selected!`);
        }
    }, {waitForApp: true}
);

/**
 *  Verify field values of specified record in the list view or preview
 *
 *  @example
 *  Then Prospects *Pr_1 should have the following values in the list view:
 *      | fieldName  | value               |
 *      | name       | Prospect1 Prospect1 |
 *      | title      | Software Engineer   |
 */
Then(/^(\w+) \*(\w+) should have the following values in the (list view|preview):$/,
    async function (module: string, name: string, view: string, table: TableDefinition) {

        let record = await seedbed.cachedRecords.get(name);
        let list_view = await seedbed.components[`${module}List`].ListView;
        let listItem = list_view.getListItem({id: record.id});

        // Navigate to the list view
        await chooseModule(module);

        if (view === 'list view') {

            let fildsData: any = table.hashes();
            let errors = await listItem.checkFields(fildsData);

            let message = '';
            _.each(errors, (item) => {
                message += item;
            });

            if (message) {
                throw new Error(message);
            }
        } else if (view === 'preview') {

            let preview = await seedbed.components[`${name}Preview`];
            await listItem.clickPreviewButton();
            await preview.showMore('show more');
            await checkValues(preview.PreviewView, table);

        }
    }, {waitForApp: true}
);


export const chooseModule = async function (itemName) {
    await seedbed.client.driver.waitForApp();

    let moduleMenuCmp = seedbed.components['moduleMenu'] as ModuleMenuCmp;
    let isVisible = await moduleMenuCmp.isVisible(itemName);

    if (isVisible) {
        await moduleMenuCmp.clickItem(itemName);
        await seedbed.client.driver.waitForApp();
    } else {
        await moduleMenuCmp.showAllModules();
        isVisible = await moduleMenuCmp.isVisible(itemName);
        if (isVisible) {
            await moduleMenuCmp.clickItem(itemName, true);
            await seedbed.client.driver.waitForApp();
        } else {
            await goToUrl(itemName);
        }
    }
    await seedbed.client.driver.pause(2000);
};

export const chooseRecord = async function (record: { id: string }, view: ListView) {
    let listItem = view.getListItem({id: record.id});
    await listItem.clickListItem();
    await seedbed.client.driver.waitForApp();
};

export const toggleRecord = async function (record: { id: string }, view: ListView) {
    let listItem = view.getListItem({id: record.id});
    await listItem.clickItem('checkbox');
    await seedbed.client.driver.waitForApp();
};

/**
 * Click button on record header bar
 *
 * @param {string} btnName
 * @param layout
 * @returns {Promise<any>}
 */
export const recordViewHeaderButtonClicks = async function (btnName: string, layout: any) {
    if (btnName.toLowerCase() === 'show more') {
        return layout.showMore(btnName);
    }
    await layout.HeaderView.clickButton(btnName.toLowerCase());
    return seedbed.client.driver.waitForApp();
};

/**
 * Open Actions menu and click action button on the list view such as 'Edit' or 'Delete'
 *
 * @param {string} btnName button name
 * @param listItem specified record
 * @returns {Promise<void>}
 */
export const listViewItemButtonClicks = async function (btnName: string, listItem: any) {
    await listItem.clickListButton(btnName);
    await seedbed.client.driver.waitForApp();
};

/**
 * Select specified action in the record view actions dropdown
 *
 * @param {string} actionName
 * @param {RecordLayout} layout
 * @returns {Promise<WaitForResult>}
 */
export const recordViewActionsMenuItemClick = async function (actionName: string, layout: RecordLayout) {
    await layout.HeaderView.clickButton('actions');
    await layout.HeaderView.clickButton(actionName);
    return seedbed.client.driver.waitForApp();
};

/**
 * Enter field values in the record view
 *
 * @param {RecordView} view
 * @param {TableDefinition} data
 * @returns {Promise<void>}
 */
export const provideRecordViewInput = async function (view: RecordView, data: TableDefinition): Promise<void> {
    if (data.hashes.length > 1) {
        throw new Error('One line data table entry is expected');
    }

    let inputData = stepsHelper.getArrayOfHashmaps(data)[0];
    // check for * marked column and cache the record and view if needed
    let uidInfo = Utils.computeRecordUID(inputData);

    seedbed.cucumber.scenario.recordsInfo[uidInfo.uid] = {
        uid: uidInfo.uid,
        originInput: JSON.parse(JSON.stringify(inputData)),
        input: inputData,
        module: view.module,
    };

    await view.setFieldsValue(inputData);
};

/**
 * Enter field values when in-line editing record in the list view
 *
 * @param listItem
 * @param {TableDefinition} data
 * @returns {Promise<void>}
 */
const provideListViewInput = async function (listItem, data: TableDefinition): Promise<void> {

    // Provide inline input in the list view
    let row: any;
    for (row of data.hashes()) {
        let field = await listItem.getField(row.fieldName);
        await field.setValue(row.value);
    }
};

export const checkValues = async function (view: BaseView, data: TableDefinition) {
    const attrRefRegex = RegExp(/\{\*([a-zA-Z](?:\w|\S)*)\.((?:\w|\s)*)}/g);

    /**
     * Replaces references to dynamic values with their value from the
     * cached API response for the specified record.
     *
     * @example "{*Case_1.case_number}" is replaced with "237".
     * | fieldName | value                                |
     * | name      | [CASE:{*Case_1.case_number}] My Case |
     *
     * @param {string} value
     * @return {{}}
     */
    function getReplacementsForAttributeReferences(value: string) {
        let replacements = {};
        let match;
        let recordIdOfReference;
        let apiResponseForRecord;

        // Find the substitutions for every match captured.
        while ((match = attrRefRegex.exec(value)) !== null) {
            recordIdOfReference = seedbed.cachedRecords.get(match[1]).id;
            apiResponseForRecord = seedbed.api.created.find((rec) => {
                return recordIdOfReference === rec.id;
            });

            if (apiResponseForRecord) {
                replacements[match[0]] = apiResponseForRecord[match[2]];
            }
        }
        return replacements;
    }

    // Substitute the references in the values for all fields where one or
    // more references are found.
    const fieldsData: any = _.map(data.hashes() || [], (field) => {
        const replacements = getReplacementsForAttributeReferences(field.value);

        _.each(replacements, (value: string, key: string) => {
            field.value = field.value.replace(RegExp(_.escapeRegExp(key), 'g'), value);
        });

        return field;
    });

    let errors = await view.checkFields(fieldsData);
    let message = '';
    _.each(errors, (item) => {
        message += item;
    });

    if (message) {
        throw new Error(message);
    }
};

/**
 * Close alert dialog
 *
 * @returns {Promise<void>}
 */
export const closeAlert = async function () {
    let alert = new AlertCmp({});
    await alert.close();
};

/**
 * Confirm or Cancel warning dialog
 *
 * @param actionName
 * @returns {Promise<void>}
 */
export const closeWarning = async function (actionName) {
    let alert = new AlertCmp({type: 'warning'});
    await alert.clickButton(actionName);
};

/**
 * Verify type of alert and message displayed in the alert dialog
 *
 * @param {TableDefinition} data
 * @returns {Promise<void>}
 */
export const verifyAlertProperties = async function (data: TableDefinition) {

    let errors = [];
    let alert = new AlertCmp({});
    let actualAlertMessage = await alert.getText();
    let actualAlertType = await alert.getType();

    let expectedData = data.hashes()[0];

    let expectedAlertMessage = expectedData.message;
    let expectedAlertType = expectedData.type;

    if (actualAlertMessage !== expectedAlertMessage) {
        errors.push(
            [
                `Expected alert message: ${expectedAlertMessage}`,
                `\tActual alert message: ${actualAlertMessage}`,
                `\n`,
            ].join('\n')
        )
    }

    if (actualAlertType !== expectedAlertType) {
        errors.push(
            [
                `Expected alert type: ${expectedAlertType}`,
                `\tActual alert type: ${actualAlertType}`,
                `\n`,
            ].join('\n')
        )
    }
    let message = '';
    _.each(errors, (item) => {
        message += item;
    });

    if (message) {
        throw new Error(message);
    }
};

export const goToUrl = async function (urlHash): Promise<void> {
    await seedbed.client.driver.setUrlHash(urlHash);
};

export const parseInputArray = async function (arg: string): Promise<any[]> {

    // trim spaces and square brackets
    arg = arg.replace(/[\[\]]/g, '').trim();

    let records = [];
    if (arg.startsWith('*')) {

        let sRecord = arg.split(',');
        for (let i = 0; i < sRecord.length; i++) {

            let record = seedbed.cachedRecords.get(sRecord[i].trim().replace('*', ''));

            if (!record) {
                throw new Error(`Record with the following ID '${sRecord[i].trim()}' doesn't exist`);
            }
            records.push(record);
        }
        return records;
    } else {
        throw new Error(`Record '${arg}' doesn't exist`);
    }
};

/**
 * Determine current module.
 *
 * Warning: This method does not differentiate between list or record view within the modules
 *
 * @returns {Promise<string>}
 */
export const getCurrentModule = async function (): Promise<string> {
    // Get Field Label
    let argumentsArray = [];
    let currentModule = await  seedbed.client.driver.execSync('getCurrentModule', argumentsArray);
    return currentModule.value;
}

/**
 * Toggle record(s) specified by record ID in SIDECAR list view
 *
 * @param {string} inputIDs string of comma-separated records IDs
 * @param {ListView} listView module's list view
 * @returns {Promise<number>} number of selected records
 */
export const toggleSpecifiedRecords = async function (inputIDs: string, listView: ListView): Promise<number> {

    let recordIds = null;
    recordIds = await parseInputArray(inputIDs);

    // Toggle specific record(s)
    if (Array.isArray(recordIds)) {
        for (let i = 0; i < recordIds.length; i++) {
            await toggleRecord({id: recordIds[i].id}, listView);
        }
    } else {
        await toggleRecord({id: recordIds.id}, listView);
    }

    return recordIds.length;
};
