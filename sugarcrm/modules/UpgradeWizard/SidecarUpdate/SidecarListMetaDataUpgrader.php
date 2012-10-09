<?php
// This will need to be pathed properly when packaged
require_once 'SidecarAbstractMetaDataUpgrader.php';

class SidecarListMetaDataUpgrader extends SidecarAbstractMetaDataUpgrader
{
    /**
     * The actual legacy defs converter. For list it is simply taking the old 
     * def array, looping over it, lowercasing the field names, adding that to
     * each iteration and saving that into a 'fields' array inside of the panels
     * array.
     */
    public function convertLegacyViewDefsToSidecar() {
        $newdefs = array();
        foreach ($this->legacyViewdefs as $field => $def) {
            $defs = array();
            $defs['name'] = strtolower($field);
            $defs['default'] = true;
            $defs['enabled'] = true;
            $defs = array_merge($defs, $def);
            
            $newdefs[] = $defs;
        }
        
        // This is the structure of the sidecar list meta
        $module = $this->getNormalizedModuleName();
        
        // Clean up client to mobile for wireless clients
        $client = $this->client == 'wireless' ? 'mobile' : $this->client;
        $this->sidecarViewdefs[$module][$client]['view']['list'] = array(
            'panels' => array(
                array(
                    'label' => 'default',
                    'fields' => $newdefs,
                ),
            ),
        );
    }
}