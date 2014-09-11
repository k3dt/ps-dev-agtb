<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

class FieldTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        SugarBean::clearLoadedDef('Contact');
        parent::tearDown();
    }

    public function testGetJoinRecursion()
    {
        $contact = BeanFactory::getBean('Contacts');

        // create field definition which refers itself as id_name and doesn't have link attribute
        $contact->field_defs['account_name']['id_name'] = 'account_name';
        $contact->field_defs['account_name']['link'] = null;

        $query = new SugarQuery();
        $query->from($contact);
        $field = new SugarQuery_Builder_Field('account_name', $query);
        $alias = $field->getJoin();

        $this->assertFalse($alias, 'Field with invalid vardefs should not produce JOIN');
    }
}
