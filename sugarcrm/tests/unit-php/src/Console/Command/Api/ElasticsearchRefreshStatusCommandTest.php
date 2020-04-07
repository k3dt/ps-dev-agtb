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

namespace Sugarcrm\SugarcrmTestsUnit\Console\Command\Api;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\Command\Api\ElasticsearchRefreshStatusCommand
 */
class ElasticsearchRefreshStatusCommandTest extends AbstractApiCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->commandClass = 'Sugarcrm\Sugarcrm\Console\Command\Api\ElasticsearchRefreshStatusCommand';
        $this->apiClass = 'AdministrationApi';
        $this->apiMethod = 'elasticSearchRefreshStatus';
    }

    /**
     * {@inheritdoc}
     */
    public function providerTestExecuteCommand()
    {
        return array(
            array(
                array(
                    'shared' => '1s',
                    'accountsonly' => '5s',
                ),
                array(),
                'ElasticsearchRefreshStatusCommand_0.txt',
                0,
            ),
        );
    }
}
