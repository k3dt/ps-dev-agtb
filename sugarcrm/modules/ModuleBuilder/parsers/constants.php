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

define('MB_BASEMETADATALOCATION', 'base');
define('MB_CUSTOMMETADATALOCATION', 'custom');
define('MB_WORKINGMETADATALOCATION', 'working');
define('MB_HISTORYMETADATALOCATION', 'history');
define('MB_GRIDLAYOUTMETADATA', 'gridLayoutMetaData');
define('MB_LISTLAYOUTMETADATA', 'listLayoutMetaData');
define('MB_LISTVIEW', 'listview');
define('MB_SIDECARLISTVIEW', 'list');
define('MB_SEARCHVIEW', 'searchview');
define('MB_BASICSEARCH', 'basic_search' );
define('MB_ADVANCEDSEARCH', 'advanced_search' );
define('MB_DASHLET', 'dashlet');
define('MB_DASHLETSEARCH', 'dashletsearch');
define('MB_EDITVIEW', 'editview');
define('MB_DETAILVIEW', 'detailview');
define('MB_QUICKCREATE', 'quickcreate');
define('MB_POPUPLIST', 'popuplist');
define('MB_POPUPSEARCH', 'popupsearch');
define('MB_LABEL', 'label');
define('MB_ONETOONE', 'one-to-one');
define('MB_ONETOMANY', 'one-to-many');
define('MB_MANYTOONE', 'many-to-one');
define('MB_MANYTOMANY', 'many-to-many');
define('MB_MAXDBIDENTIFIERLENGTH', 30); // maximum length of any identifier in our supported databases
define('MB_EXPORTPREPEND', 'project_');
define('MB_VISIBILITY', 'visibility');
//BEGIN SUGARCRM flav=pro ONLY
define('MB_WIRELESSEDITVIEW', 'wirelesseditview');
define('MB_WIRELESSDETAILVIEW', 'wirelessdetailview');
define('MB_WIRELESSLISTVIEW', 'wirelesslistview');
define('MB_WIRELESSBASICSEARCH', 'wireless_basic_search' );
define('MB_WIRELESSADVANCEDSEARCH', 'wireless_advanced_search' );
define('MB_WIRELESS', 'mobile');
//END SUGARCRM flav=pro ONLY
//BEGIN SUGARCRM flav=ent ONLY
define('MB_PORTALEDITVIEW', 'portaleditview');
define('MB_PORTALDETAILVIEW', 'portaldetailview');
define('MB_PORTALLISTVIEW', 'portallistview');
define('MB_PORTALSEARCHVIEW', 'portalsearchview');
define('MB_PORTAL', 'portal');
//END SUGARCRM flav=ent ONLY

class MBConstants
{
    public static $EMPTY = array ( 'name' => '(empty)' , 'label' => '(empty)' ) ;
    public static $FILLER = array ( 'name' => '(filler)' , 'label' => 'LBL_FILLER' ) ; // would prefer to have label => translate('LBL_FILLER') but can't be done in a static, and don't want to require instantiating a new object to get these constants
}
