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

namespace Sugarcrm\Sugarcrm\Notification;

/**
 * Class CarrierRegistry
 * @package Notification
 */
class CarrierRegistry
{

    /**
     * Path to file in which store cached dictionary array
     */
    const CACHE_FILE = 'Notification/carrierRegistry.php';

    /**
     * Variable name in which store cached dictionary array
     */
    const CACHE_VARIABLE = 'carrierRegistry';

    /**
     * Full path to CarrierInterface with nameSpace
     */
    const CARRIER_INTERFACE = 'Sugarcrm\\Sugarcrm\\Notification\\Carrier\\CarrierInterface';

    /**
     * Returns object of CarrierRegistry, customized if it's present
     *
     * @return CarrierRegistry
     */
    public static function getInstance()
    {
        $class = \SugarAutoLoader::customClass('Sugarcrm\Sugarcrm\Notification\CarrierRegistry');

        return new $class();
    }

    /**
     * Function return Carrier modules(retrieve from cache)
     *
     * @return array
     */
    public function getCarriers()
    {
        return array_keys($this->getDictionary());
    }

    /**
     * Get Carrier by module name
     *
     * @param string $moduleName
     * @return CarrierInterface|null
     */
    public function getCarrier($moduleName)
    {
        $carriers = $this->getDictionary();

        if (isset($carriers[$moduleName])) {
            \SugarAutoLoader::load($carriers[$moduleName]['path']);
            $class = $carriers[$moduleName]['class'];

            return new $class();
        } else {
            return null;
        }
    }

    /**
     * Build dictionary array with carrier class names and paths
     *
     *  array(
     *      'moduleName' => array(
     *          'class' => 'className',
     *          'path' => 'pathToClass'
     *      )
     *  );
     *
     * @return array
     */
    protected function scan()
    {
        $dictionary = array();
        foreach ($GLOBALS['moduleList'] as $module) {
            $path = 'modules/' . $module . '/Carrier.php';
            if (!\SugarAutoLoader::fileExists($path)) {
                continue;
            }
            \SugarAutoLoader::load($path);
            $class = $module . 'Carrier';

            if (!$this->isCarrierClass($class)) {
                continue;
            }

            $customPath = \SugarAutoLoader::existingCustomOne($path);
            \SugarAutoLoader::load($customPath);
            $customClass = \SugarAutoLoader::customClass($class);
            if ($this->isCarrierClass($customClass)) {
                $class = $customClass;
                $path = $customPath;
            }

            $dictionary[$module] = array(
                'path' => $path,
                'class' => $class
            );
        }

        return $dictionary;
    }

    /**
     * Is class implements CarrierInterface
     *
     * @param string $class
     * @return bool
     */
    protected function isCarrierClass($class)
    {
        return class_exists($class) && in_array(self::CARRIER_INTERFACE, class_implements($class));
    }

    /**
     * Retrieving array(dictionary array with carrier class names and path to it)
     *
     * Retrieving array(dictionary array with carrier class names and path to it)
     * from cache file if it not exists rebuild cache
     *
     * @return array
     */
    protected function getDictionary()
    {
        $data = $this->getCache();
        if (is_null($data)) {
            $data = $this->scan();
            $this->setCache($data);
        }

        return $data;
    }

    /**
     * Retrieving array(dictionary array with carrier class names and path to it) from cache file if it exists
     *
     * @return array|null
     */
    protected function getCache()
    {
        $path = sugar_cached(self::CACHE_FILE);
        if (\SugarAutoLoader::fileExists($path)) {
            include($path);
        }

        if (isset(${self::CACHE_VARIABLE})) {
            return ${self::CACHE_VARIABLE};
        } else {
            return null;
        }
    }

    /**
     * Saving array(dictionary array with carrier class names and path to it) to cache file
     *
     * @param array data
     */
    protected function setCache($data)
    {
        create_cache_directory(self::CACHE_FILE);
        write_array_to_file(self::CACHE_VARIABLE, $data, sugar_cached(self::CACHE_FILE));
    }

}
