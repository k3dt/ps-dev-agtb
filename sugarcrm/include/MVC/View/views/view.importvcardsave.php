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
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: view.importvcardsave.php 46120 2009-04-14 06:09:16Z martinhu $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
require_once('include/vCard.php');

class ViewImportvcardsave extends SugarView
{
    var $type = 'save';

    public function __construct()
    {
        parent::SugarView();
    }

    /**
     * @see SugarView::display()
     */
    public function display()
    {
        $redirect = "index.php?action=Importvcard&module={$_REQUEST['module']}";

        if (!empty($_FILES['vcard']) && $_FILES['vcard']['error'] == 0) {
            $vcard = new vCard();
            $record = $vcard->importVCard($_FILES['vcard']['tmp_name'], $_REQUEST['module']);

            if (empty($record)) {
                SugarApplication::redirect($redirect . '&error=vcardErrorRequired');
            }

            SugarApplication::redirect("index.php?action=DetailView&module={$_REQUEST['module']}&record=$record");
        } else {
            switch ($_FILES['vcard']['error'])
            {
                case UPLOAD_ERR_FORM_SIZE:
                    $redirect .= "&error=vcardErrorFilesize";
                break;
                default:
                    $redirect .= "&error=vcardErrorDefault";
                    $GLOBALS['log']->error('Upload error code: ' . $_FILES['vcard']['error'] . '. Please refer to the error codes http://php.net/manual/en/features.file-upload.errors.php');
                break;
            }

            SugarApplication::redirect($redirect);
        }
    }
}
?>
