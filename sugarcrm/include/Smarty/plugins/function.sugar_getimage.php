<?php

/**
 * Smarty {sugar_getimage} function plugin
 *
 * Type:     function
 * Name:     sugar_getimage
 * Purpose:  Returns HTML image or sprite
 * 
 * @author Aamir Mansoor (amansoor@sugarcrm.com) 
 * @author Cam McKinnon (cmckinnon@sugarcrm.com)
 * @param array
 * @param Smarty
 */

function smarty_function_sugar_getimage($params, &$smarty) {

	// error checking for required parameters
	if(!isset($params['name'])) 
		$smarty->trigger_error($GLOBALS['app_strings']['ERR_MISSING_REQUIRED_FIELDS'] . 'name');

	// set defaults
	if(!isset($params['attr']))
		$params['attr'] = '';
	if(!isset($params['width']))
		$params['width'] = null;
	if(!isset($params['height']))
		$params['height'] = null;
	if(!isset($params['alt'])) 
		$params['alt'] = '';

	// deprecated ?
	if(!isset($params['ext']))
		$params['ext'] = null;

	return SugarThemeRegistry::current()->getImage($params['name'], $params['attr'], $params['width'], $params['height'], $params['ext'], $params['alt']);	
}
?>
