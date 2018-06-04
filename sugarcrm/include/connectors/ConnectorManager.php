<?php
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
/**
 * Connector Manager
 * Manages connector caching
 */
class ConnectorManager
{
    /**
     * gets list of all connectors
     * @return mixed
     */
    protected function getConnectorList()
    {
        return ConnectorUtils::getConnectors();
    }

    /**
     * gets connectors meta
     * @return array
     */
    public function buildConnectorsMeta()
    {
        require_once('include/connectors/utils/ConnectorUtils.php');

        $allConnectors = $this->getConnectorList();
        $connectors = array();
        // get general connector info
        foreach ($allConnectors as $name => $connector) {
            $instance = ConnectorFactory::getInstance($connector['id']);
            $connectorInfo = array(
                'testing_enabled' => false,
                'test_passed' => false,
                'eapm_bean' => false,
                'field_mapping' => array(),
                'id' => $connector['id']
            );

            if (!empty($connector['name'])) {
                $connectorInfo['name'] = $connector['name'];
            }

            $source = $this->getSourceForConnector($connector);
            if (isset($source)) {
                if ($source->hasTestingEnabled()) {
                    $connectorInfo['testing_enabled'] = true;
                    try {
                        $testpassed = $source->test();
                    } catch (Exception $e) {
                        $GLOBALS['log']->error($name.' testing enabled but test throwing php errors');
                    }
                    if (isset($testpassed)) {
                        $connectorInfo['test_passed'] = $testpassed;
                    }
                }
            }

            if (method_exists($instance, 'getMapping')) {
                $connectorInfo['field_mapping'] = $instance->getMapping();
            }

            $connectors[$name] = $connectorInfo;
        }

        $connectorsHash = $this->hash($connectors);
        $connectors['_hash'] = $connectorsHash;

        $this->putConnectorCache($connectors);

        return $connectors;
    }

    /**
     * returns md5 of serialized input
     * @param mixed $in
     * @return string
     */
    public function hash($in)
    {
        return md5(serialize($in));
    }

    /**
     * gets connector meta and mixes in user auth info
     * @param array $connectors
     * @return array
     */
    public function getUserConnectors()
    {
        // retrieve connector metadata from sugar_cache
        $connectors = sugar_cache_retrieve('connector_metadata');
        if (empty($connectors)) {
            $connectors = $this->buildConnectorsMeta();
        }

        // mix in user specific data
        foreach ($connectors as $name => $connector) {
            if (is_array($connectors[$name])) {
                $eapmBean = $this->getEAPMForConnector($connector);
                $connectors[$name]['eapm_bean'] = !empty($eapmBean->id);
            }
        }

        $hash = $this->hash($connectors);

        return array(
            'connectors' => $connectors,
            '_hash' => $hash,
        );
    }

    /**
     * puts connector data in cache
     * @param array $data
     */
    public function putConnectorCache($data)
    {
        // store to sugar_cache
        sugar_cache_put('connector_metadata', $data);

        if (!empty($data['_hash'])) {
            $this->addToHash('connectors', $data['_hash']);
        }

    }

    /**
     * adds current connector hash to cache
     * @param string $key
     * @param string $hash
     */
    protected function addToHash($key, $hash)
    {
        $hashes = sugar_cache_retrieve('connector_hashes');
        if (empty($hashes)) {
            $hashes = array();
        }

        $hashes[$key] = $hash;
        sugar_cache_put('connector_hashes', $hashes);
    }

    /**
     * gets current connectors hash from cache
     * @param string $key key of hash to retrieve
     * @return bool
     */
    protected function getFromHashCache($key)
    {
        $hashes = sugar_cache_retrieve('connector_hashes');
        if (empty($hashes)) {
            $hashes = array();
        }

        return !empty($hashes[$key]) ? $hashes[$key] : false;
    }

    /**
     * gets source for connector
     * @param string $connector connector name
     * @return null|source
     */
    public function getSourceForConnector($connector)
    {
        if (isset($connector['id'])) {
            return SourceFactory::getSource($connector['id']);
        } else {
            return null;
        }
    }

    /**
     * checks if hash is valid
     * @param string $hash
     * @return bool
     */
    public function isHashValid($hash)
    {
        $userConnectors = $this->getUserConnectors();
        return $hash === $userConnectors['_hash'];
    }

    /**
     * gets EAPM bean for connector per current user
     * @param array $connector
     * @return null|object|SugarBean
     */
    public function getEAPMForConnector($connector)
    {
        if (isset($connector['name'])) {
            return EAPM::getLoginInfo($connector['name']);
        } else {
            return null;
        }
    }
}
