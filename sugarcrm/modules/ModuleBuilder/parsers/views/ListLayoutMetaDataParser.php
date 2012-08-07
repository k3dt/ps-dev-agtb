<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * $Id: additionalDetails.php 13782 2006-06-06 17:58:55Z majed $
 *********************************************************************************/

require_once 'modules/ModuleBuilder/parsers/views/AbstractMetaDataParser.php' ;
require_once 'modules/ModuleBuilder/parsers/views/MetaDataParserInterface.php' ;

class ListLayoutMetaDataParser extends AbstractMetaDataParser implements MetaDataParserInterface
{

    // Columns is used by the view to construct the listview - each column is built by calling the named function
    public $columns = array ( 'LBL_DEFAULT' => 'getDefaultFields' , 'LBL_AVAILABLE' => 'getAdditionalFields' , 'LBL_HIDDEN' => 'getAvailableFields' ) ;
    protected $labelIdentifier = 'label' ; // labels in the listviewdefs.php are tagged 'label' =>
    protected $allowParent = false;
    protected $requestRejectTypes = array('html'=>'html', 'text'=>'text', 'encrypt'=>'encrypt');
    protected $allowedViews = array(
        MB_LISTVIEW,
        MB_DASHLET,
        MB_DASHLETSEARCH,
        MB_POPUPLIST,
        MB_POPUPSEARCH,
        //BEGIN SUGARCRM flav=pro || flav=sales ONLY
    	MB_WIRELESSLISTVIEW,
    	//END SUGARCRM flav=pro || flav=sales ONLY
        //BEGIN SUGARCRM flav=ent ONLY
        MB_PORTALLISTVIEW,
        //END SUGARCRM flav=ent ONLY
    );

    /*
     * Simple function for array_udiff_assoc function call in getAvailableFields()
     */
    static function getArrayDiff ($one , $two)
    {
        $retArray = array();
        foreach($one as $key => $value)
        {
            if (!isset($two[$key]))
            {
                $retArray[$key] = $value;
            }
        }
        return $retArray;
    }

    /*
     * Constructor
     * @param string $view           The view type, that is, editview, searchview etc
     * @param string $moduleName     The name of the module to which this listview belongs
     * @param string $packageName    If not empty, the name of the package to which this listview belongs
     * @param string $client         The client making the request for this parser
     */
    function __construct ($view , $moduleName , $packageName = '', $client = '')
    {
        $GLOBALS [ 'log' ]->debug ( get_class ( $this ) . ": __construct()" ) ;

        // Simple validation
        if (!in_array($view, $this->allowedViews))
        {
            sugar_die ( "ListLayoutMetaDataParser: View $view is not supported" ) ;
        }
        
        if (empty ( $packageName ))
        {
            require_once 'modules/ModuleBuilder/parsers/views/DeployedMetaDataImplementation.php' ;
            $this->implementation = new DeployedMetaDataImplementation ( $view, $moduleName, $client ) ;
        } else
        {
            require_once 'modules/ModuleBuilder/parsers/views/UndeployedMetaDataImplementation.php' ;
            $this->implementation = new UndeployedMetaDataImplementation ( $view, $moduleName, $packageName, $client ) ;
        }
        $this->view = $view;

        $this->_fielddefs = $this->implementation->getFielddefs();
        //$this->_paneldefs = $this->implementation->getPanelDefs();
        $this->_standardizeFieldLabels( $this->_fielddefs );
        $this->_viewdefs = array_change_key_case ( $this->implementation->getViewdefs () ) ; // force to lower case so don't have problems with case mismatches later

    }

    /*
     * Deploy the layout
     * @param boolean $populate If true (default), then update the layout first with new layout information from the $_REQUEST array
     */
    function handleSave($populate = true) {
        if ($populate) {
            $this->_populateFromRequest();
        }
        $this->implementation->deploy($this->_viewdefs); // force the field names back to upper case so the list view will work correctly
    }

    function getLayout ()
    {
        return $this->_viewdefs ;
    }

    /**
     * Return a list of the default fields for a listview
     * @return array    List of default fields as an array, where key = value = <field name>
     */
    function getDefaultFields ()
    {
        $defaultFields = array ( ) ;
        foreach ( $this->_viewdefs as $key => $def )
        {
            // add in the default fields from the listviewdefs but hide fields disabled in the listviewdefs.
            if (! empty ( $def [ 'default' ] ) && (!isset($def['enabled']) || $def['enabled'] != false)
            	&& (!isset($def [ 'studio' ]) || ($def [ 'studio' ] !== false && $def [ 'studio' ] != "false")))
            {
                if (isset($this->_fielddefs [ $key ] )) {
					$defaultFields [ $key ] = self::_trimFieldDefs ( $this->_fielddefs [ $key ] ) ;
					if (!empty($def['label']))
					   $defaultFields [ $key ]['label'] = $def['label'];
                }
				else {
					$defaultFields [ $key ] = $def;
				}
            }
        }

        return $defaultFields ;
    }

    /**
     * Returns additional fields available for users to create fields
     * @return array    List of additional fields as an array, where key = value = <field name>
     */
    function getAdditionalFields ()
    {
        $additionalFields = array ( ) ;
        foreach ( $this->_viewdefs as $key => $def )
        {
            //#25322
            if(strtolower ( $key ) == 'email_opt_out'){
                continue;
            }

            if (empty ( $def [ 'default' ] ))
            {
                if (isset($this->_fielddefs [ $key ] ))
                    $additionalFields [ $key ] = self::_trimFieldDefs ( $this->_fielddefs [ $key ] ) ;
                else
                    $additionalFields [ $key ] = $def;
            }
        }
        return $additionalFields ;
    }

        /**
     * Returns unused fields that are available for use in either default or additional list views
     * @return array    List of available fields as an array, where key = value = <field name>
     */
    function getAvailableFields ()
    {
        $availableFields = array ( ) ;
        // Select available fields from the field definitions - don't need to worry about checking if ok to include as the Implementation has done that already in its constructor
        foreach ( $this->_fielddefs as $key => $def )
        {
            if ($this->isValidField($key, $def) && !isset($this->_viewdefs[$key]))
        	    $availableFields [ $key ] = self::_trimFieldDefs( $this->_fielddefs [ $key ] ) ;
        }
    	$origDefs = $this->getOriginalViewDefs();
        foreach($origDefs as $key => $def)
        {
        	if (!isset($this->_viewdefs[$key]) ||
        		(isset($this->_viewdefs[$key]['enabled']) && $this->_viewdefs[$key]['enabled'] == false))
        	$availableFields [ $key ] = $def;
        }

        return $availableFields;
    }

    public function isValidField($key, $def)
    {
        if (isset($def['studio']))
        {
            if (is_array($def['studio']))
            {
                // Bug 54507 - Need to set the view properly for portal
                // Portal editor requests vary in that the requested view is ListView
                // but what we really need is portallistview, which is in $this->view
                // All other instances of ListLayoutMetaDataParser set $this->view
                // to $_REQUEST['view']
                $view = $this->view;
                
            	// fix for removing email1 field from studio popup searchview - bug 42902
                if($view == 'popupsearch' && $key == 'email1')
                {	
            		return false;
            	} //end bug 42902

                // Bug 54507 Return explicit setting of a fields view setting if there is one
                if (!empty($view) && isset($def['studio'][$view])) 
                {
                    return $def['studio'][$view] !== false && (string)$def['studio'][$view] != 'false' && (string)$def['studio'][$view] != 'hidden';
                }
                
                if (isset($def['studio']['listview']))
                {
					return $def['studio']['listview'] !== false && (string)$def['studio']['listview'] != 'false' && (string)$def['studio']['listview'] != 'hidden';
                }
                // End Bug 54507
                
                if (isset($def ['studio']['visible']))
                {
                    return $def['studio']['visible'];
                }
            } else if(is_string($def['studio'])) {
            	return $def['studio'] != 'false' && $def['studio'] != 'hidden';
            } else if(is_bool($def['studio'])) {
                return $def['studio'];
            }

        }
        
    	//Bug 32520. We need to dissalow currency_id fields on list views. 
    	//This should be removed once array based studio definitions are in.
    	if (isset($def['type']) && $def['type'] == "id" && $def['name'] == 'currency_id')
        {
    	   return false;
        }
    	
    	//Check fields types
    	if (isset($def['dbType']) && $def['dbType'] == "id")
    	{
            return false;
    	}
    	
    	if (isset($def['type']))
        {
            if ($def['type'] == 'html' || ($def['type'] == 'parent' && !$this->allowParent) 
             || $def['type'] == "id" || $def['type'] == "link" || $def['type'] == 'image')
                return false;
        }

    	//hide currency_id, deleted, and _name fields by key-name
        if(strcmp ( $key, 'deleted' ) == 0 ) {
            return false;
        }

        //if all the tests failed, the field is probably ok
        return true;
    }

    protected function _populateFromRequest() {
        $GLOBALS [ 'log' ]->debug ( get_class ( $this ) . "->populateFromRequest() - fielddefs = ".print_r($this->_fielddefs, true));
        // Transfer across any reserved fields, that is, any where studio !== true, which are not editable but must be preserved
        $newViewdefs = array ( ) ;

        $originalViewDefs = $this->getOriginalViewDefs();

    	foreach ( $this->_viewdefs as $key => $def )
        {
            //If the field is on the layout, but studio disabled, put it back on the layout at the front
        	if (isset ($def['studio']) && (
        		(is_array($def['studio']) && isset($def['studio']['listview']) &&
            		($def['studio']['listview'] === false || strtolower($def['studio']['listview']) == 'false'
            		|| strtolower($def['studio']['listview']) == 'required')
            	)
         		|| (!is_array($def['studio']) &&
         			($def [ 'studio' ] === false || strtolower($def['studio']) == 'false' || strtolower($def['studio']) == 'required'))
         		))
         	{
                $newViewdefs [ $key ] = $def ;
         	}
        }
        // only take items from group_0 for searchviews (basic_search or advanced_search) and subpanels (which both are missing the Available column) - take group_0, _1 and _2 for all other list views
        $lastGroup = (isset ( $this->columns [ 'LBL_AVAILABLE' ] )) ? 2 : 1 ;

        for ( $i = 0 ; isset ( $_POST [ 'group_' . $i ] ) && $i < $lastGroup ; $i ++ )
        {
            foreach ( $_POST [ 'group_' . $i ] as $fieldname )
            {
                $fieldname = strtolower ( $fieldname ) ;
                //Check if the field was previously on the layout
                if (isset ($this->_viewdefs[$fieldname])) {
                	$newViewdefs [ $fieldname ] = $this->_viewdefs[$fieldname];
                   // print_r($this->_viewdefs[ $fieldname ]);
				}
                //Next check if the original view def contained it
                else if (isset($originalViewDefs[ $fieldname ]))
                {
                	$newViewdefs [ $fieldname ] =  $originalViewDefs[ $fieldname ];
                }
                //create a definition from the fielddefs
                else
                {
	                // if we don't have a valid fieldname then just ignore it and move on...
					if ( ! isset ( $this->_fielddefs [ $fieldname ] ) )
						continue ;

	                $newViewdefs [ $fieldname ] = $this->_trimFieldDefs($this->_fielddefs [ $fieldname ]) ;

                    // fixing bug #25640: Value of "Relate" custom field is not displayed as a link in list view
                    // we should set additional params such as 'link' and 'id' to be stored in custom listviewdefs.php
                    if (isset($this->_fielddefs[$fieldname]['type']) && $this->_fielddefs[$fieldname]['type'] == 'relate')
                    {
                        $newViewdefs[$fieldname]['id'] = strtoupper($this->_fielddefs[$fieldname]['id_name']);
                        $newViewdefs[$fieldname]['link'] = true;
                    }
                    // sorting fields of certain types will cause a database engine problems
	                if ( isset($this->_fielddefs[$fieldname]['type']) &&
	                		isset ( $this->requestRejectTypes [ $this->_fielddefs [ $fieldname ] [ 'type' ] ] ))
	                {
	                    $newViewdefs [ $fieldname ] [ 'sortable' ] = false ;
	                }

	                // Bug 23728 - Make adding a currency type field default to setting the 'currency_format' to true
	                if (isset ( $this->_fielddefs [ $fieldname ] [ 'type' ]) && $this->_fielddefs [ $fieldname ] [ 'type' ] == 'currency')
	                {
	                    $newViewdefs [ $fieldname ] [ 'currency_format' ] = true;
	                }
                }
                if (isset($newViewdefs [ $fieldname ]['enabled']))
                		$newViewdefs [ $fieldname ]['enabled'] = true;

                if (isset ( $_REQUEST [ strtolower ( $fieldname ) . 'width' ] ))
                {
                    $width = substr ( $_REQUEST [ $fieldname . 'width' ], 6, 3 ) ;
                    if (strpos ( $width, "%" ) != false)
                    {
                        $width = substr ( $width, 0, 2 ) ;
                    }
					if (!($width < 101 && $width > 0))
                    {
                        $width = 10;
                    }
                    $newViewdefs [ $fieldname ] [ 'width' ] = $width."%" ;
                } else if (isset ( $this->_viewdefs [ $fieldname ] [ 'width' ] ))
                {
                    $newViewdefs [ $fieldname ] [ 'width' ] = $this->_viewdefs [ $fieldname ] [ 'width' ] ;
                }
                else {
                	$newViewdefs [ $fieldname ] [ 'width' ] = "10%";
                }

                $newViewdefs [ $fieldname ] [ 'default' ] = ($i == 0) ;
            }
        }
        $this->_viewdefs = $newViewdefs ;
    }

    /*
     * Remove all instances of a field from the layout
     * @param string $fieldName Name of the field to remove
     * @return boolean True if the field was removed; false otherwise
     */
    function removeField ($fieldName)
    {
        if (isset ( $this->_viewdefs [ $fieldName ] ))
        {
            unset( $this->_viewdefs [ $fieldName ] )  ;
            return true ;
        }
        return false ;
    }

    function getOriginalViewDefs() {
    	$defs = $this->implementation->getOriginalViewdefs ();
    	$out = array();
    	foreach ($defs as $field => $def)
    	{
    		$out[strtolower($field)] = $def;
    	}

    	return $out;
    }

    /**
     * Checks to see if a field name is in any of the panels
     * @param string $name
     * @param array  $src
     * @return bool
     */
    public function panelHasField($name, $src = null) {
        $field = $this->panelGetField($name, $src);
        return !empty($field);
    }

    /**
     * Scans the panels/fields to see if the panel list already has a field and,
     * if it does, returns that field with its position in the panels list
     *
     * @param $name
     * @return array
     */
    public function panelGetField($name, $src = null) {
        // If there was a passed source, use that for the panel search
        $panels = $src !== null && is_array($src) ? $src : $this->_paneldefs;
        foreach ($panels as $panelix => $def) {
            if (isset($def['fields'])) {
                foreach ($def['fields'] as $fieldix => $field) {
                    if (isset($field['name']) && $field['name'] == $name) {
                        return array('field' => $field, 'panelix' => $panelix, 'fieldix' => $fieldix);
                    }
                }
            }
        }

        return array();
    }

   static function _trimFieldDefs ( $def )
	{
		if ( isset ( $def [ 'vname' ] ) )
			$def [ 'label' ] = $def [ 'vname' ] ;
		return array_intersect_key ( $def , array ( 'type' => true, 'studio' => true , 'label' => true , 'width' => true , 'sortable' => true , 'related_fields' => true , 'default' => true , 'link' => true , 'align' => true , 'orderBy' => true ,'hideLabel' => true, 'customLable' => true , 'currency_format' => true ) ) ;
	}

}
