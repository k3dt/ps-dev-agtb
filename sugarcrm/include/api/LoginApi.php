<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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
 ********************************************************************************/

class LoginApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'login' => array(
                'reqType' => 'POST',
                'path' => array('login'),
                'pathVars' => array(''),
                'method' => 'login',
                'shortHelp' => 'Current login function',
                'longHelp' => 'include/api/html/login_help.html',
                'noLoginRequired' => true,
            ),
            'logout' => array(
                'reqType' => 'POST',
                'path' => array('logout'),
                'pathVars' => array(''),
                'method' => 'logout',
                'shortHelp' => 'Current logout function.',
                'longHelp' => 'include/api/html/logout_help.html',
                'noLoginRequired' => true,
            ),
        );
    }

    public function login($api, $args) {
        
        $this->requireArgs($args,array('username','password'));
        
        if ( !isset($args['type']) || $args['type'] == 'text' ) {
            $encryption = 'PLAIN';
        } else {
            $encryption = 'MD5';
        }

        // Default type for Sugar users (not Portal)
        $userType = 'User';
        if ( isset($args['userType']) ) {
            $userType = $args['userType'];
        }

        $api->security = SugarSecurityFactory::loadClassFromType($userType);
        
        if ( $api->security == null ) {
            throw new SugarApiExceptionError('Could not find a security model for users of type: '.$userType);
        }
        
        if ( ! $api->security->loginUserPass($args['username'],$args['password'],$encryption) ) {
            // Login failed
            $data = array('success'=>false);
        } else {
            global $current_user;
            $data = array('token'=>$api->security->sessionId,
                          'success'=>true,
                'current_user'=>array('id'=>$current_user->id,
                    'full_name'=>$current_user->full_name,
                    'user_name'=>$current_user->user_name,
                    'timezone'=>$current_user->getPreference('timezone'),
                    'datepref'=>$current_user->getPreference('datef'),
                    'timepref'=>$current_user->getPreference('timef'),
                    )
            );
        }
        
        return $data;
    }

    public function logout($api, $args) {
        // In the future it should destroy the oauth tokens associated to this connection
        // For now, we just nuke the session.
        session_regenerate_id(true);

        
        return array('success'=>true);

    }
}
