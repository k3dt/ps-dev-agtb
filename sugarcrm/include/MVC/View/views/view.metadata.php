<?php

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/DetailView/DetailView2.php');
<<<<<<< HEAD
require_once('dBug.php');
=======
>>>>>>> cottoncandy

class ViewMetadata extends SugarView{
	var $type ='detail';
	var $dv;
<<<<<<< HEAD



=======
	
	
 	
>>>>>>> cottoncandy
 	function displayCheckBoxes($name,$values, $selected =array(), $attr=''){
 		echo "<div $attr style='overflow:auto;float:left;width:200px;height:200px' >";
		foreach($values as $value){
		 	$checked = in_array($value, $selected)? " checked=checked ": " ";
			echo "<div style='padding:2px'><input type='checkbox' name='$name' value='$value' $checked> $value</div>";
		}
		echo "</div>";
 	}
<<<<<<< HEAD

=======
 	
>>>>>>> cottoncandy
 	function displaySelect($name,$values, $selected ='', $attr=''){
 		echo "<select name='$name' $attr>";
		foreach($values as $value){
		 	$checked = $value == $selected? " selected=selected ": " ";
			echo "<option value='$value' $checked> $value</option>";
		}
		echo "</select>";
 	}
<<<<<<< HEAD



=======
 	
 	
 	
>>>>>>> cottoncandy
 	 function displayTextBoxes($values, $attr=''){
 		echo "<div $attr style='overflow:auto;float:left;width:400px;height:200px' >";
		foreach($values as $value){
			$postvalue = !empty($_POST[$value])? $_POST[$value]: '';
			echo "<div style='padding:2px;width:150px;float:left'>$value</div>  <input type='text' name='$value' value='$postvalue'> ";
		}
		echo "</div>";
 	}
<<<<<<< HEAD


 	function printValue($value, $depth=0){
// 		echo "<pre>";
// 		print_r($value);
// 		echo "</pre>";
        new dBug($value, "array");
 	}

=======
 	
 	
 	
 	function printValue($value, $depth=0){
 		echo "<pre>";
 		print_r($value);
 		echo "</pre>";
 		
 	}
 	
>>>>>>> cottoncandy
 	function display(){
 		$do = !empty($_REQUEST['do'])?$_REQUEST['do']:'';
 		echo "<form method='post'>";
 		echo "<div><h2>I want to learn about ";
<<<<<<< HEAD

=======
 		
>>>>>>> cottoncandy
 		$this->displaySelect('do', array('Nothing', 'Modules','Fields', 'Field Attributes', 'Relationships'), $do, 'onchange="toggleLearn(this.value)"');
 		echo "<input type='submit' value='Learn' class='button'></h2></div>";
		$modules = !empty($_REQUEST['modules'])?$_REQUEST['modules']:array();
		if(empty($modules) && !empty($_REQUEST['module']) && $_REQUEST['module'] != 'Home'){
			$modules = array(	$_REQUEST['module']);
		}
 		$this->displayCheckBoxes('modules[]', VardefBrowser::getModules(), $modules, ' id="_modules" ');
 		$attributes = !empty($_REQUEST['attributes'])?$_REQUEST['attributes']:array();
 		$allAttributes = array_keys(VardefBrowser::findFieldAttributes());
 		sort($allAttributes);
 		$this->displayCheckBoxes('attributes[]', $allAttributes, $attributes, ' id="_attributes" ');
 		$this->displayTextBoxes($allAttributes, ' id="_fields" ');
 		echo "</form>";
 		 		echo <<<EOQ
 		<script>
 			function toggleLearn(value){
<<<<<<< HEAD
 				document.getElementById('_modules').style.display = 'None';
 				document.getElementById('_attributes').style.display = 'None';
 				document.getElementById('_fields').style.display = 'None';
 				if(value == 'Modules' || value == 'Relationships'){
 					document.getElementById('_modules').style.display = '';
=======
 				document.getElementById('_modules').style.display = 'None';	
 				document.getElementById('_attributes').style.display = 'None';	
 				document.getElementById('_fields').style.display = 'None';	
 				if(value == 'Modules' || value == 'Relationships'){
 					document.getElementById('_modules').style.display = '';	
>>>>>>> cottoncandy
 				}
 				if(value == 'Fields'){
 					document.getElementById('_modules').style.display = '';
 					document.getElementById('_fields').style.display = '';
<<<<<<< HEAD
 				}
 				if(value == 'Field Attributes'){
 					document.getElementById('_modules').style.display = '';
 					document.getElementById('_attributes').style.display = '';
 				}
 			}
 			toggleLearn('$do');

 		</script>

=======
 				}	
 				if(value == 'Field Attributes'){
 					document.getElementById('_modules').style.display = '';
 					document.getElementById('_attributes').style.display = '';
 				}	
 			}
 			toggleLearn('$do');
 			
 		</script>
 		
>>>>>>> cottoncandy
EOQ;
 		echo "<div width='100%'></div><div><div style='float:left'>";
 		switch ($do){
 			case 'Modules':
<<<<<<< HEAD
 				$this->printValue(VardefBrowser::findVardefs( $modules));
=======
 				$this->printValue(VardefBrowser::findVardefs( $modules));	
>>>>>>> cottoncandy
 				break;
 			case 'Field Attributes':
 				$this->printValue(VardefBrowser::findFieldAttributes($attributes, $modules));
 				break;
 			case 'Fields':
 				$searchFor = array();
 				foreach($allAttributes as $at){
 					if(!empty($_POST[$at])){
 						$searchFor[$at] = $_POST[$at];
<<<<<<< HEAD
 					}
 				}

=======
 					}	
 				}
 				
>>>>>>> cottoncandy
 				$this->printValue(VardefBrowser::findFieldsWithAttributes($searchFor, $modules));
 				break;
 			default:
 				echo <<<EOQ
 				<div style='border:1px solid;width:100%;text-align:center;-moz-border-radius: 5px;border-radius: 5px;'>
 					<h2 style='text-decoration: line-through'>All you ever wanted to know about Vardefs in 30 minutes</h2>
 					<h2 style='text-decoration: line-through'>All you ever wanted to know about Vardef Fields and Relationships in 30 minutes</h1>
 					<h2 style='text-decoration: line-through'>All you ever wanted to know about Vardef Fields in 30 minutes</h2>
 					<h2 >Something about Vardefs in 30 minutes</h2>
 				</div>
<<<<<<< HEAD

 				<div style='border:1px solid;width:100%;-moz-border-radius: 5px;border-radius: 5px;'>
 					<h4>What you need to know</h4>
 					<pre>
Vardefs are where we define information about the fields for a module.

It also specifies 75% of the information on relationships.

There are also special attributes that can enable additional functionality for a module.

It's broken down into:
	<b>fields:</b> The fields of a module (most of these are stored in the database)

	<b>indices:</b> The indicies on the database

	<b>relationships:</b> The relationships for this module

	<b>templates:</b> the functionality/fields this module inherits from SugarObjects(located in include/SugarObjects).
		In a vardef these are specified by the third argument in VardefManager::createVardef
		For Example - <b>VardefManager::createVardef('Contacts','Contact', array('default', 'assignable','team_security','person'));</b>
		would add the fields for team security to contacts and make it an object that can be assigned to users.
		The 'person' value would indicate that that Contacts subclasses Person and gains all the fields/attributes that 'Person'
		would get. Since person extends basic it would also gain all the fields/attributes of basic as well.


		The SugarObjects that a module can extend are <b>'basic', 'company', 'file', 'issue', 'person'</b>.
		These are the same objects you can build off of in ModuleBuilder.
		Adding a new SugarObject to include/SugarObjects/templates is the way
		to add modules to ModuleBuilder

		Besides extending base objects, a module can also implement functionality defined in SugarObjects.
		You can currenty implement <b>'assignable' and 'team_security'</b>.


	<b>attributes:</b>
		<b>[table] (string) (required)</b> The database table where this module stores it's data - any custom fields will be stored in a new table
			with '_cstm' appended to the table name. The field id_c in the custom table will be the same value as id in the primary table
			allowing us to join the two tables together.

		<b>[comment] (string) (optional)</b> is a description of the module

		<b>[unified_search] (bool)(optional)</b> is global search (the search in the upper right corner on the screen) available for this module

		<b>[unified_search_default_enabled] (bool)(optional)</b> is this module available by default in global search

		<b>[optimistic_locking] (bool) (optional)</b> optimistic locking is the concept that on save if the record modifiy time (date_modified)
			 is newer than the the modify time of the record when it was loaded to edit (this time is stored in the session).

		<b>[favorites] (bool) (optional)</b> should favorites be enabled for this module. Favorites are indicated by the stars next to a record
			on lists and deail views. It makes it easier for users to indicate what is important to them right now. It also allows them to filter
			by favorites.

		<b>[duplicate_merge] (bool) (optional)</b> is systematic merging allowed between records of this module or not. This option is available from
			the menu on list views. A user needs to select 2 records on the list view using the checkboxes, and then they can select merge from the actions
			menu.

		<b>[audited] (bool) (optional)</b> auditing allows for the tracking of any changes to specified fields. In order to enable auditing you need to enable
			it at both the module level and the field level. It will create an audit table for the module with the '_audit' appended to the table name.

		<b>[custom_fields] (bool) (automatic) </b> if the module has custom fields this will be set to true











					</pre>
 				</div>

 				<div>

 				</div>

EOQ;


 		}
 		echo "</div><div style='float:right'>Help Text</div></div>";


 		//$this->printValue(VardefBrowser::findFieldsWithAttributes(array('type'=>'id'), $modules));





=======
 				
 				<div style='border:1px solid;width:100%;-moz-border-radius: 5px;border-radius: 5px;'>
 					<h4>What you need to know</h4>
 					<pre>
Vardefs are where we define information about the fields for a module. 
 					
It also specifies 75% of the information on relationships. 
 					
There are also special attributes that can enable additional functionality for a module. 
 					
It's broken down into:
	<b>fields:</b> The fields of a module (most of these are stored in the database)
	
	<b>indices:</b> The indicies on the database
	
	<b>relationships:</b> The relationships for this module
	
	<b>templates:</b> the functionality/fields this module inherits from SugarObjects(located in include/SugarObjects). 
		In a vardef these are specified by the third argument in VardefManager::createVardef
		For Example - <b>VardefManager::createVardef('Contacts','Contact', array('default', 'assignable','team_security','person'));</b>
		would add the fields for team security to contacts and make it an object that can be assigned to users.
		The 'person' value would indicate that that Contacts subclasses Person and gains all the fields/attributes that 'Person' 
		would get. Since person extends basic it would also gain all the fields/attributes of basic as well.
					  
					 
		The SugarObjects that a module can extend are <b>'basic', 'company', 'file', 'issue', 'person'</b>. 
		These are the same objects you can build off of in ModuleBuilder. 
		Adding a new SugarObject to include/SugarObjects/templates is the way 
		to add modules to ModuleBuilder
					 
		Besides extending base objects, a module can also implement functionality defined in SugarObjects. 
		You can currenty implement <b>'assignable' and 'team_security'</b>. 
		
		
	<b>attributes:</b>
		<b>[table] (string) (required)</b> The database table where this module stores it's data - any custom fields will be stored in a new table 
			with '_cstm' appended to the table name. The field id_c in the custom table will be the same value as id in the primary table
			allowing us to join the two tables together. 
		
		<b>[comment] (string) (optional)</b> is a description of the module
		
		<b>[unified_search] (bool)(optional)</b> is global search (the search in the upper right corner on the screen) available for this module
		
		<b>[unified_search_default_enabled] (bool)(optional)</b> is this module available by default in global search
		
		<b>[optimistic_locking] (bool) (optional)</b> optimistic locking is the concept that on save if the record modifiy time (date_modified)
			 is newer than the the modify time of the record when it was loaded to edit (this time is stored in the session). 
		
		<b>[favorites] (bool) (optional)</b> should favorites be enabled for this module. Favorites are indicated by the stars next to a record 
			on lists and deail views. It makes it easier for users to indicate what is important to them right now. It also allows them to filter
			by favorites.  
			
		<b>[duplicate_merge] (bool) (optional)</b> is systematic merging allowed between records of this module or not. This option is available from 
			the menu on list views. A user needs to select 2 records on the list view using the checkboxes, and then they can select merge from the actions
			menu.
			
		<b>[audited] (bool) (optional)</b> auditing allows for the tracking of any changes to specified fields. In order to enable auditing you need to enable
			it at both the module level and the field level. It will create an audit table for the module with the '_audit' appended to the table name.
			
		<b>[custom_fields] (bool) (automatic) </b> if the module has custom fields this will be set to true
		
			
		
		
		
					 
					 

					  
					
					
					</pre>
 				</div>
 				
 				<div>
 				
 				</div>
 				
EOQ;
 					
 			
 		}
 		echo "</div><div style='float:right'>Help Text</div></div>";
 		
 		
 		//$this->printValue(VardefBrowser::findFieldsWithAttributes(array('type'=>'id'), $modules));
 		
 	
 		
 		
 		
>>>>>>> cottoncandy
 	}

}

class VardefBrowser{

	function __construct(){
<<<<<<< HEAD

	}

=======
		
	}
	
>>>>>>> cottoncandy
	static function getModules(){
		$modules = array();
		foreach($GLOBALS['beanList'] as $module=>$object){
			if($object == 'aCase')$object = 'Case';
			VardefManager::loadVardef($module, $object);
			if(empty($GLOBALS['dictionary'][$object]['fields'] ))continue;
			$modules[] = $module;
		}
		sort($modules);
		return $modules;
<<<<<<< HEAD


	}

=======
	
		
	}
	
>>>>>>> cottoncandy
	static function findFieldsWithAttributes($attributes, $modules=null){
		$fields = array();
		if(empty($modules))$modules = VardefBrowser::getModules();
		foreach($modules as $module){
			if(!empty($GLOBALS['beanList'][$module])){
				$object = $GLOBALS['beanList'][$module];
				if($object == 'aCase')$object = 'Case';
				VardefManager::loadVardef($module, $object);
				if(empty($GLOBALS['dictionary'][$object]['fields'] ))continue;
				foreach($GLOBALS['dictionary'][$object]['fields'] as $name=>$def){
					$match = true;
					foreach($attributes as $k=>$v){
						$alt = false;
						if($k == 'type'){
<<<<<<< HEAD
							$alt = 'dbType';
=======
							$alt = 'dbType';	
>>>>>>> cottoncandy
						}
						if($v == 'true' && !empty($def[$k])){
							continue;
						}
						if((empty($def[$k]) || $def[$k] != $v) && (empty($alt) || empty($def[$alt]) || $def[$alt] != $v )){
<<<<<<< HEAD
							$match = false;
=======
							$match = false;	
>>>>>>> cottoncandy
						}
					}
					if($match){
						$fields[$module][$object][$name] = $def;
					}
<<<<<<< HEAD

				}

			}
		}
		return $fields;
	}

=======
					
				}
				
			}	
		}
		return $fields;			
	}
	
>>>>>>> cottoncandy
		static function findVardefs($modules=null){
			$defs = array();
			if(empty($modules))$modules = VardefBrowser::getModules();
			foreach($modules as $module){
				if(!empty($GLOBALS['beanList'][$module])){
					$object = $GLOBALS['beanList'][$module];
					if($object == 'aCase')$object = 'Case';
					VardefManager::loadVardef($module, $object);
					if(empty($GLOBALS['dictionary'][$object]['fields'] ))continue;
					$defs[$module][$object] = $GLOBALS['dictionary'][$object];
				}
			}
			return $defs;
		}
<<<<<<< HEAD


=======
	
	
>>>>>>> cottoncandy
		static function findFieldAttributes($attributes=array(), $modules=null, $byModule=false, $byType=false){
		$fields = array();
		if(empty($modules))$modules = VardefBrowser::getModules();
		foreach($modules as $module){
			if(!empty($GLOBALS['beanList'][$module])){
				$object = $GLOBALS['beanList'][$module];
				if($object == 'aCase')$object = 'Case';
				VardefManager::loadVardef($module, $object);
				if(empty($GLOBALS['dictionary'][$object]['fields'] ))continue;
				foreach($GLOBALS['dictionary'][$object]['fields'] as $name=>$def){
					$fieldAttributes = (!empty($attributes))? $attributes:array_keys($def);
					foreach($fieldAttributes as $k){
						if(isset($def[$k])){
							$v  = var_export($def[$k], true);
							$key = is_array($def[$k])?null:$def[$k];
							if($k == 'type'){
								if(isset($def['dbType'])){
<<<<<<< HEAD
									$v = var_export($def['dbType'], true);
								}
=======
									$v = var_export($def['dbType'], true);	
								}	
>>>>>>> cottoncandy
							}
							if($byModule){
								$fields[$module][$object][$def['type']][$k][$key] = $v;
							}else{
								if($byType){
<<<<<<< HEAD
									$fields[$def['type']][$k][$key] = $v;
=======
									$fields[$def['type']][$k][$key] = $v;	
>>>>>>> cottoncandy
								}else{
									if(!is_array($def[$k])){
										if(isset($fields[$k][$key])){
											$fields[$k][$key]['refs']++;
										}else{
<<<<<<< HEAD
											$fields[$k][$key] = array('attribute'=>$v, 'refs'=>1);
										}
									}else{
										$fields[$k]['_array'][] = $def[$k];
=======
											$fields[$k][$key] = array('attribute'=>$v, 'refs'=>1);		
										}
									}else{
										$fields[$k]['_array'][] = $def[$k];	
>>>>>>> cottoncandy

									}
								}
							}
<<<<<<< HEAD


						}

					}



				}

			}
		}
		return $fields;
	}


=======
							
							
						}
						
					}
					
					
				
				}
				
			}	
		}
		return $fields;			
	}
	
	
>>>>>>> cottoncandy

}
