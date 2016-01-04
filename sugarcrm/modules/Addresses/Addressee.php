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

require_once 'include/SugarObjects/templates/person/Person.php';

class Addressee extends Person {
    public $new_schema = true;
    public $module_dir = 'Addresses';
    public $object_name = 'Addressee';
    public $object_names = 'Addresses';
    public $table_name = 'addresses';
}
