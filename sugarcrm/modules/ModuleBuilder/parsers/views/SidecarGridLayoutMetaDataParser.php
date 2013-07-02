<?php
//FILE SUGARCRM flav=pro ONLY
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 *********************************************************************************/
require_once 'modules/ModuleBuilder/parsers/views/GridLayoutMetaDataParser.php';
require_once 'modules/ModuleBuilder/parsers/constants.php';
require_once 'include/MetaDataManager/MetaDataManager.php';

class SidecarGridLayoutMetaDataParser extends GridLayoutMetaDataParser {
    /**
     * Invalid field types for various sidecar clients. Format can be either
     * $client => array('type', 'type') or 
     * $client => array('edit' => array('type', 'type'), 'detail' => array('type', 'type'))
     * 
     * @var array
     * @protected
     */
    protected $invalidTypes = array(
        //BEGIN SUGARCRM flav=ent ONLY
        'portal' => array(
            // Record supports same fields as edit used to
            'record' => array('parent', 'parent_type', 'iframe', 'encrypt', 'relate', 'html','currency','currency_id'),
        ),
        //END SUGARCRM flav=ent ONLY
    );

    protected $extraPanelMeta = array();

    protected $maxSpan = 12;

    protected $defaultColumns = 2;
    
    /**
     * Array of named panels for record views. If a panel does not have a label
     * but does have a name and the name is an index of this array, the corresponding
     * label will be used. If a panel has a label the label will be used.
     * 
     * @var array
     */
    protected $panelLabels = array(
        'panel_header' => 'LBL_RECORD_HEADER',
        'panel_body' => 'LBL_RECORD_BODY',
        'panel_hidden' => 'LBL_SHOW_MORE',
    );
        
    /**
     * Checks for the existence of the view variable for portal metadata
     *
     * @param array $viewdefs The viewdef array
     * @param string $view The view to check for
     * @return bool
     */
    public function hasViewVariable($viewdefs, $view) {
        return $this->getNestedDefs($viewdefs, $view, true);
    }

    /**
     * Gets the viewdefs for portal from the entire viewdef array
     *
     * @param array $viewdefs The full viewdef collection below $viewdefs[$module]
     * @param string $view The view to fetch the defs for
     * @return array
     */
    public function getDefsFromArray($viewdefs, $view) {
        return $this->getNestedDefs($viewdefs, $view);
    }

    protected function getNestedDefs($viewdefs, $view, $validateOnly = false) {
        // Get the view variable, or in Sidecar's case, the path
        $var = MetaDataFiles::getViewDefVar($view);

        // Sidecar should always be an array of metadata path elements
        if (is_array($var)) {
            $levels = count($var); // For example, 3 - portal -> view -> edit
            $checks = 0;

            for ($i = 0; $i < $levels; $i++) {
                if (isset($viewdefs[$var[$i]])) {
                    $checks++;
                    $viewdefs = $viewdefs[$var[$i]];
                }
            }

            $valid = $checks == $levels;

            return $validateOnly ? $valid : $viewdefs;
        }

        return $validateOnly ? false : array();
    }

    /**
     * Gets panel defs from the viewdef array
     * @param array $viewdef The viewdef array
     * @return array
     */
    protected function getPanelsFromViewDef($viewdef) {
        $defs = $this->getDefsFromArray($viewdef, $this->_view);
        if (isset($defs['panels'])) {
            return $defs['panels'];
        }

        return array();
    }

    /**
     * Checks for necessary elements of the metadata array and fails the request
     * if not found
     *
     * @param array $viewdefs The view defs being requested
     * @return void
     */
    public function validateMetaData($viewdefs) {
        if (!isset($viewdefs['panels'])) {
            sugar_die(get_class($this) . ': missing panels section in layout definition (case sensitive)');
        }
    }
    
    /**
     * Validates a field
     * 
     * @param string $key The name of the field
     * @param array $def The defs for this field
     * @return bool
     */
    public function isValidField($key, $def) {
        if (!empty($this->client)) {
            $method = 'isValidField' . ucfirst(strtolower($this->client));
            if (method_exists($this, $method)) {
                return $this->$method($key, $def);
            }
        }
        
        return parent::isValidField($key, $def);
    }
    
    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * Validates portal only fields. Runs the field through a preliminary check
     * of view type and field type before passing the field on to the parent validator.
     * 
     * @param string $key The field
     * @param array $def Teh field def for this field
     * @return bool
     */
    public function isValidFieldPortal($key, $def) {
        if (isset($this->invalidTypes['portal'])) {
            $view = str_replace(array('portal', 'view'), '', $this->_view);
            
            if (isset($this->invalidTypes['portal'][$view])) {
                $blacklist = $this->invalidTypes['portal'][$view];
            } else {
                $blacklist = $this->invalidTypes['portal'];
            }
            
            if (!isset($def['type']) || in_array($def['type'], $blacklist)) {
                return false;
            }
        } 
        
        return parent::isValidField($key, $def);
    }
    //END SUGARCRM flav=ent ONLY
    
    /**
     * helper to pack a row with $cols members of [empty]
     * @param $row
     * @param $cols
     * @return void
     *
     */
    protected function _packRowWithEmpty(&$row, $cols)
    {
        for ($i=0; $i<$cols; $i++) {
            $row[] = $this->_addInternalCell(MBConstants::$EMPTY);
        }
    }

    /**
     * Helper to add a field (name) to the internal formatted row
     * used in case internal format goes to wanting arrays
     * @param $field
     * @return string value to add
     */
    protected function _addInternalCell($field) {
        // Handle combination data field defs
        if (!is_array($field)) {
            return $field;
        }
        
        if (isset($field['name'])) {
            return $field['name'];
        } 
        
        if (isset($field['type'])) {
            return $field['type'];
        }
        
        return $this->FILLER;
    }


    /*
     * helper methods for doing field comparisons
     */
    protected function isFiller($field)
    {
        if (is_array($field))  {
            return ($field == MBConstants::$FILLER);
        }

        return ($field == $this->FILLER['name']);
    }

    protected function isEmpty($field)
    {
        if (is_array($field))  {
            return ($field == MBConstants::$EMPTY);
        }

        return ($field == MBConstants::$EMPTY['name']);
    }

    /**
     * Manipulates the amount of space a field takes up in studio based on colspan
     * 
     * Returns an array of cells to be appended to the fieldlist. Default span 
     * units to 6 or half of a 12 unit space with 2 columns
     * 
     * @param string|array $field The field to calculate space for
     * @param integer $colspan The colspan for the field
     * @param integer $singleSpanUnit The size of a single field span
     */
    protected function _addCell($field, $colspan, $singleSpanUnit = 6)
    {
        // for fillers, if we ever have a 'filler' with colspan = n, just sub n 'fillers'
        if ($field === '')
        {
            return array_fill(0,$colspan,'');
        }

        // add the displayParam field if necessary
        if ($colspan > 1) {
            if (!is_array($field)) {
                $field = array('name' => $field);
            }

            $field['span'] = $colspan * $singleSpanUnit;
        }
        return array($field);
    }

    /**
     * here we convert from internal metadata format to file (canonical) metadata
     * @param $panels
     * @param $fielddefs
     * @return array - viewdefs in canonical file format
     */
    protected function _convertToCanonicalForm($panels , $fielddefs)
    {
        //$previousViewDef = $this->getFieldsFromLayout($this->implementation->getViewDefs());
        //$currentFields = $this->getFieldsFromLayout($panels);

        $canonicalPanels = array();

        // reset any span info already in the fields, we're going to figure it out again
        foreach ($this->_originalViewDef as $originalKey => $originalFieldDef ) {
            if (is_array($originalFieldDef)) {
                unset($this->_originalViewDef[$originalKey]['span']);
            }
        }

        foreach ($panels as $pName => $panel) {
            $fields = array();
            // get number of panel columns default to 2
            $panelColumns = 2;
            if (!empty($this->extraPanelMeta[$pName]['columns'])) {
                $panelColumns = $this->extraPanelMeta[$pName]['columns'];
            }
            $singleSpanUnit = $this->maxSpan/$panelColumns;
            foreach ($panel as $row) {
                $offset = 1; // reset
                $lastField = null; // holder for the field to put in
                foreach ($row as $cellIndex=>$cell) {
                    // empty => get rid of it, and assign to previous field as colspan
                    if ($this->isEmpty($cell)) {
                        $offset++; // count our columns
                        continue;
                    }
                    
                    $fieldCount = count($row);

                    // dump out the last field we stored and reset column count
                    // leading empty => should not occur, but assign to next field as colspan
                    if ($lastField !== null) {
                        $fields = array_merge($fields,$this->_addCell($lastField, $offset, $singleSpanUnit));
                        $offset = 1;
                    }

                    // filler => ''
                    if ($this->isFiller($cell)) {
                        // 58308 - Adjust displayColumns on the last field if it 
                        // is set and we are an end column
                        if ($panelColumns - $offset === 1) {
                            $lastRowIndex = count($fields) - 1;
                            if (!is_array($fields[$lastRowIndex])) {
                                $fields[$lastRowIndex] = array(
                                    'name' => $fields[$lastRowIndex]
                                );
                            }
                            $fields[$lastRowIndex]['span'] = $singleSpanUnit;
                        }

                        $lastField = array(
                            'span' => $singleSpanUnit,
                        );

                        if ($fieldCount === 1) {
                            $lastField = array(
                                'span' => $this->maxSpan,
                            );
                        }
                    }
                    else {
                        // field => add the field def.
                        $fieldName = is_array($cell) ? $cell['name'] : $cell;
                        if (isset($this->_originalViewDef[$fieldName]))  {
                            $source = $this->_originalViewDef[$fieldName];
                        }
                        elseif (isset($fielddefs[$fieldName])) {
                            $source = self::_trimFieldDefs($fielddefs[$fieldName]);
                        }
                        else {
                            $source = $cell;
                        }

                        $lastField = $this->getNewRowItem($source, $fielddefs[$fieldName]);
                    }

                }

                // dump out the last field we stored
                if ($lastField !== null) {
                    $fields = array_merge($fields,$this->_addCell($lastField,$offset,$singleSpanUnit));
                }

            }
            if (!empty($this->extraPanelMeta[$pName])) {
                // restore any extra panel meta
                $newPanel = $this->extraPanelMeta[$pName];
            }

            $newPanel['fields'] = $fields;
            $canonicalPanels[] = $newPanel;
        }
        return $canonicalPanels;
    }

    /**
     * here we convert from file (canonical) metadata => internal metadata format
     * @param $panels
     * @param $fielddefs
     * @return array $internalPanels
     */
    protected function _convertFromCanonicalForm($panels , $fielddefs)
    {
        // canonical form has format:
        // $panels[n]['label'] = label for panel n
        //           ['fields'] = array of fields


        // internally we want:
        // $panels[label for panel] = fields of panel in rows,cols format

        $internalPanels = array();
        foreach ($panels as $n => $panel) {
            // Handle panel labeling...
            $pLabel = $n;
            if (!empty($panel['label'])) {
                $pLabel = $panel['label'];
            } elseif (isset($panel['name'])) {
                if (isset($this->panelLabels[$panel['name']])) {
                    $pLabel = $this->panelLabels[$panel['name']];
                }
            }
            
            // Get panel column value
            $panelColumns = 2;
            if (!empty($panel['columns'])) {
                $panelColumns = $panel['columns'];
            }

            // panels now have meta at this level so we need to store that
            $panelMeta = $panel;
            unset($panelMeta['fields']);
            $this->extraPanelMeta[$pLabel] = $panelMeta;

            // going from a list of fields to putting them in rows,cols format.
            $internalFieldRows = array();
            $row = array();
            foreach ($panel['fields'] as $field) {

                // figure out the colspan of the current field
                if (is_array($field) && !empty($field['span'])) {
                    $colspan = floor($field['span']/$this->maxSpan*$panelColumns);
                } else {
                    // Simple aesthetics... make the name field a full span but 
                    // only if this is the header panel
                    if (isset($panel['name']) && $panel['name'] == 'panel_header' && $field == 'name') {
                        $colspan = $panelColumns;
                    } else {
                        $colspan = 1;
                    }
                }
                $colsTaken = 0;

                // figure out how much space is taken up already by other fields
                foreach($row as $rowField) {
                    if (is_array($rowField) && !empty($rowField['span'])) {
                        $colsTaken = $colsTaken + floor($rowField['span']/$this->maxSpan*$panelColumns);
                    } else {
                        $colsTaken = $colsTaken + 1;
                    }
                }

                $cols_left = $this->getMaxColumns() - $colsTaken;
                if ($cols_left < $colspan) {
                    // add $cols_left of (empty) to $row and put it in
                   $this->_packRowWithEmpty($row, $cols_left);
                   $internalFieldRows[] = $row;
                   $row = array();
                }

                /*
                if (empty($field)) {
                    $fieldToInsert = $this->FILLER;
                } elseif(is_array($field)) {
                    // Handle special fields like fieldset
                    if (isset($field['type'])) {
                        if ($field['type'] == 'fieldset' && isset($field['fields']) && isset($field['name'])) {
                            $fieldToInsert = $field['name'];
                        } elseif (!empty($field['readonly'])) {
                            // This handles non-field fields like favorite and follow
                            $fieldToInsert = $field['type'];
                        } elseif (isset($field['name'])) {
                            // This handles normal condition named fields
                            $fieldToInsert = $field['name'];
                        } else {
                            // This handles junk metadata
                            $fieldToInsert = $this->FILLER;
                        }
                    } else {
                        $fieldToInsert = empty($field['name']) ? $this->FILLER : $field['name'];
                    }
                } else {
                    $fieldToInsert = $field;
                }
                */
                $fieldToInsert = $this->getFieldToInsert($field);
                // add field to row + enough (empty) to make it to colspan
                $row[] = $this->_addInternalCell($fieldToInsert);
                $this->_packRowWithEmpty($row, $colspan-1);
            }

            // add the last incomplete row if necessary
            if (!empty($row)) {
                $cols_left = $this->getMaxColumns() - count($row);
                // add $cols_left of (empty) to $row and put it in
                $this->_packRowWithEmpty($row, $cols_left);
                $internalFieldRows[] = $row;
            }
            $internalPanels[$pLabel] = $internalFieldRows;
        }

        return $internalPanels;
    }


    /**
     * Returns a list of fields, generally from the original (not customized) viewdefs
     * @param $viewdef
     * @return array array of fields, indexed by field name
     */
    protected function getFieldsFromLayout($viewdef)
    {
        $panels = $this->getPanelsFromViewDef($viewdef);

        // not canonical form... try parent method
        if (!isset($panels[0]['fields'])) {
            return parent::getFieldsFromLayout($viewdef);
        }

        $out = array();
        foreach ($panels as $panel) {
            foreach($panel['fields'] as $field) {
                if (!is_array($field)) {
                    $name = $field;
                } elseif (!empty($field['name'])) {
                    $name = $field['name'];
                } else {
                    $name = '';
                }
                
                $out[$name] = $field;
            }
        }
        return $out;
    }

    /*
     * Remove a field from the layout
     * 
     * @param string $fieldName Name of the field to remove
     * @return boolean True if the field was removed; false otherwise
     */
    function removeField ($fieldName)
    {
        // Set the return result
        $result = false;
        
        // Get our original view defs
        $originalDefs = $this->getImplementation()->getOriginalViewdefs();
        
        // Get the panel label
        $nestedDefs = $this->getNestedDefs($originalDefs, $this->_view);
        $panelKey = key($nestedDefs['panels']);
        
        // Handle labels the same way that the converter does
        $label = $panelKey;
        if (isset($nestedDefs['panels'][$panelKey]['label'])) {
            $label = $nestedDefs['panels'][$panelKey]['label'];
        } elseif (isset($nestedDefs['panels'][$panelKey]['name'])) {
            if (isset($this->panelLabels[$nestedDefs['panels'][$panelKey]['name']])) {
                $label = $this->panelLabels[$nestedDefs['panels'][$panelKey]['name']];
            }
        }
        
        // Loop and find
        if (isset($this->_viewdefs['panels'][$label]) && is_array($this->_viewdefs['panels'][$label])) {
            foreach ($this->_viewdefs['panels'][$label] as $rowIndex => $row) {
                if (is_array($row)) {
                    foreach ($row as $fieldIndex => $field) {
                        if ($field == $fieldName) {
                            $this->_viewdefs['panels'][$label][$rowIndex][$fieldIndex] = MBConstants::$EMPTY['name'];
                            $result = true;
                            break 2;
                        }
                    }
                }
            }
            
            // Now check to see if any of our rows are totally empty, and if they
            // are, pluck them completely
            $newRows = array();
            foreach ($this->_viewdefs['panels'][$label] as $rowIndex => $row) {
                if (is_array($row)) {
                    $cols = count($row);
                    $empties = 0;
                    foreach ($row as $field) {
                        if ($field == MBConstants::$EMPTY['name']) {
                            $empties++;
                        }
                    }
                    
                    if ($empties == $cols) {
                        // All empties, remove it and keep looping
                        //unset($this->_viewdefs['panels'][$label][$rowIndex]);
                        continue;
                    }
                    
                    $newRows[] = $row;
                }
            }
            
            $this->_viewdefs['panels'][$label] = $newRows;
        }
        
        return $result;
    }

    /**
     * Clears mobile and portal metadata caches that have been created by the API
     * to allow immediate rendering of changes at the client
     */
    protected function _clearCaches() {
        if ($this->implementation->isDeployed()) {
            MetaDataFiles::clearModuleClientCache($this->_moduleName,'view');
            MetaDataManager::clearAPICache(false);
        }
    }
    
    /**
     * Removes a field from the available field array
     * 
     * @param array $availableFields The available fields array
     * @param string $field The field name to remove
     */
    protected function unsetAvailableField(&$availableFields, $field)
    {
        if (is_string($field)) {
            unset($availableFields[$field]);
        } elseif (is_array($field) && isset($field['name'])) {
            unset($availableFields[$field['name']]);
        }
    }
    
    /**
     * Gets valid field defs for a field name
     * 
     * @param  string $fieldname The fieldname to get the defs for
     * @return array
     */
    protected function getViewDefFromFieldname($fieldname)
    {
        if (is_array($fieldname)) {
            if (isset($fieldname['name'])) {
                $field = $fieldname['name'];
            } elseif(isset($fieldname['type'])) {
                $field = $fieldname['type'];
            }
            
            if (isset($field)) {
                return $this->getViewDefFromFieldname($field);
            }
            
            // This indicates an empty or non field field that is not in the 
            // right format. This should be handled by the code that calls this.
            return false;
        }
        
        return parent::getViewDefFromFieldname($fieldname);
    }
    
    protected function getFieldToInsert($field)
    {
        // Empty fields just need to be filler
        if (empty($field)) {
            return $this->FILLER;
        } 
        
        // Arrays need to be inspected a little closer
        if (is_array($field)) {
            // Handle special fields like fieldset
            if (isset($field['type'])) {
                if ($field['type'] == 'fieldset' && isset($field['fields']) && isset($field['name'])) {
                    return $field['name'];
                } 
                
                // This handles non-field fields like favorite and follow
                if (!empty($field['readonly'])) {
                    return $field['type'];
                }
            }
            
            // This handles normal condition named fields and filler fields
            return empty($field['name']) ? $this->FILLER : $field['name'];
        } 
        
        // Non empty non arrays just return the field
        return $field;
    }
}
