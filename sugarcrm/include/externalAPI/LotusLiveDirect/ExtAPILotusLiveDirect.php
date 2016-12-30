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


class ExtAPILotusLiveDirect extends OAuthPluginBase implements WebMeeting,WebDocument {

    protected $dateFormat = 'm/d/Y H:i:s';

    public $authMethod = 'oauth';
    public $supportedModules = array('Meetings','Notes', 'Documents');
    public $supportMeetingPassword = false;
    public $docSearch = true;
    public $restrictUploadsByExtension = false;
    public $connector = "ext_eapm_lotuslive";

    protected $oauthReq = "/manage/oauth/getRequestToken";
    protected $oauthAuth = '/manage/oauth/authorizeToken';
    protected $oauthAccess = '/manage/oauth/getAccessToken';
    protected $oauthParams = array(
        'signatureMethod' => 'PLAINTEXT',
// Test
//        'consumerKey' => "test_app",
//        'consumerSecret' => "87323at4aj6y8e9a0pa92w",
// Stage
//        'consumerKey' => "95d6df6a53ef6ae65a9ec14dc8716d25",
//        'consumerSecret' => "7e38abfb6b7bd7ae9250d61af33ed438",
// Production
        'consumerKey' => "9399cf0ce6e4ca4d30d56a76b21da89",
        'consumerSecret' => "7704b27829c5715445e14637415b67c1",
    );
    protected $url = 'https://apps.lotuslive.com/'; // 'https://collabserv.com/';

    public $canInvite = false;
    public $sendsInvites = false;
    public $needsUrl = false;
    // public $sharingOptions = array('private'=>'LBL_SHARE_PRIVATE','company'=>'LBL_SHARE_COMPANY','public'=>'LBL_SHARE_PUBLIC');

    function __construct() {
        $this->oauthReq = $this->url.'manage/oauth/getRequestToken';
        $this->oauthAuth = $this->url.'manage/oauth/authorizeToken';
        $this->oauthAccess = $this->url.'manage/oauth/getAccessToken';
        parent::__construct();
    }

    public function loadEAPM($eapmBean)
    {
        parent::loadEAPM($eapmBean);

        if($eapmBean->url) {
            $this->url = $eapmBean->url;
        }

        if ( !empty($eapmBean->api_data) ) {
            $this->api_data = json_decode(base64_decode($eapmBean->api_data), true);
            if ( isset($this->api_data['subscriberID']) ) {
                $this->meetingID = $this->api_data['meetingID'];
                $this->hostURL = $this->api_data['hostURL'];
                $this->joinURL = $this->api_data['joinURL'];
                $this->subscriberID = $this->api_data['subscriberID'];
            }
        }
    }

    public function quickCheckLogin() {
        $reply = parent::quickCheckLogin();
        $GLOBALS['log']->debug(__FILE__.'('.__LINE__.'): Parent Reply: '.print_r($reply,true));
        if ( $reply['success'] ) {
            $reply = $this->makeRequest('/lotuslive-shindig-server/social/rest/people/@me/@self');
            $GLOBALS['log']->debug(__FILE__.'('.__LINE__.'): LL Reply: '.print_r($reply,true));
            if ( $reply['success'] == true ) {
                if ( !empty($reply['responseJSON']['entry']['objectId']) ) {
                    $GLOBALS['log']->debug(__FILE__.'('.__LINE__.'): Has objectId: '.print_r($reply['responseJSON']['entry']['objectId'],true));
                    return $reply;
                } else {
                    $GLOBALS['log']->debug(__FILE__.'('.__LINE__.'): No objectId: '.print_r($reply['responseJSON']['entry']['objectId'],true));
                    $reply['success'] = false;
                    $reply['errorMessage'] = translate('LBL_ERR_NO_RESPONSE', 'EAPM')." #QK1";
                }
            }
        }
        $GLOBALS['log']->debug(__FILE__.'('.__LINE__.'): Bad reply: '.print_r($reply,true));

        return $reply;
    }

    public function checkLogin($eapmBean = null)
    {
        $reply = parent::checkLogin($eapmBean);
        if ( $reply['success'] != true ) {
            // $GLOBALS['log']->debug(__FILE__.'('.__LINE__.'): Bad reply: '.print_r($reply,true));
            return $reply;
        }
        try {
            // get meeting details
            $reply = $this->makeRequest('/meetings/api/getMeetingDetails');
            if ( $reply['success'] == true ) {
                if ( $reply['responseJSON']['status'] != 'ok') {
                    $reply['success'] = false;
                    $reply['errorMessage'] = $reply['responseJSON']['details'];
                    return $reply;
                }
                $this->api_data = array(
                    'meetingID'=>$reply['responseJSON']['details']['meetingID'],
                    'hostURL'=>$reply['responseJSON']['details']['hostURL'],
                    'joinURL'=>$reply['responseJSON']['details']['joinURL'],
                );
            } else {
                // $GLOBALS['log']->debug(__FILE__.'('.__LINE__.'): Bad reply: '.print_r($reply,true));
                return $reply;
            }
            // get user details
            $reply = $this->makeRequest('/lotuslive-shindig-server/social/rest/people/@me/@self');
            if ( $reply['success'] == true ) {
                $this->api_data['subscriberId'] = $reply['responseJSON']['entry']['objectId'];
            } else {
                // $GLOBALS['log']->debug(__FILE__.'('.__LINE__.'): Bad reply: '.print_r($reply,true));
                return $reply;
            }
        } catch(Exception $e) {
            $reply['success'] = FALSE;
            $reply['errorMessage'] = $e->getMessage();
            // $GLOBALS['log']->debug(__FILE__.'('.__LINE__.'): Bad reply: '.print_r($reply,true));
            return $reply;
        }

        $this->eapmBean->api_data = base64_encode(json_encode($this->api_data));

        // $GLOBALS['log']->debug(__FILE__.'('.__LINE__.'): Good reply: '.print_r($reply,true));
        return $reply;
    }

    /**
     * Create a new Lotus meeting.
     * @param string $name
     * @param string $startdate
     * @param string $duration
     * @param string $password
     * return: boolean
     */
    function scheduleMeeting($bean) {
        global $current_user;
        $bean->join_url = $this->api_data['joinURL'].'&TagCode=SugarCRM&TagID='.$bean->id;
        $bean->host_url = $this->api_data['hostURL'].'?TagCode=SugarCRM&TagID='.$bean->id;
        $bean->creator = $this->account_name;
        return array('success'=>TRUE);
    }

    /**
     * Edit an existing Lotus meeting
     * @param string $name
     * @param string $startdate
     * @param string $duration
     * @param string $password
     * return: boolean
     */
    function editMeeting($bean) {
        return $this->scheduleMeeting($bean);
    }

    /**
     * Delete an existing Lotus meeting.
     * @param string $meeting - The Lotus meeting key.
     * return: boolean
     */
    function unscheduleMeeting($bean) {
        // There is nothing to do here.
        return array('success'=>TRUE);
    }

    /**
     * NOT SUPPORTED BY LOTUS
     * Invite $attendee to the meeting with key $session.
     * @param string $meeting - The Lotus session key.
     * @param array $attendee - An array with entries for 'name' and 'email'
     * return: boolean.
     */
    function inviteAttendee($bean, $attendee, $sendInvites = false) {
        // There is nothing to do here, this is not supported by Lotus Live
        return array('success'=>TRUE);
    }

    /**
     * NOT SUPPORTED BY LOTUS
     * Uninvite the attendee with ID $attendeeID from the meeting.
     * Note: attendee ID is returned as part of the response to
     * inviteAtendee().  The attendee ID refers to a specific person
     * and a specific meeting.
     * @param array $attendeeID - Lotus attendee ID.
     * return: boolean.
     */
    function uninviteAttendee($bean,$attendeeID) {
        // There is nothing to do here, this is not supported by Lotus Live
        return array('success'=>TRUE);
    }

    /**
     * List all meetings created by this object's Lotus user.
     */
    function listMyMeetings() {
        // There is nothing to do here, this is not supported by Lotus Live
        return array('success'=>TRUE);
    }

    /**
     * Get detailed information about the meeting
     * with key $meeting.
     * @param string meeting- The Lotus meeting key.
     * return: The XML response from the Lotus server.
     */
    function getMeetingDetails($bean) {
        // TODO: Implement this, get the meeting information from the provided tags.
        return array('success'=>TRUE);
    }

    /**
     * Get HTTP client for communication with Lotus
     *
     * Creates and setup the http client object, including authorization data if needed
     *
     * @return Zend_Http_Client
     */
    protected function getClient()
    {
        $client = $this->getOauth($this)->getClient();
        return $client;
    }

    public function uploadDoc($bean, $fileToUpload, $docName, $mimeType)
    {
        $client = $this->getClient();
        $url = $this->url."files/basic/cmis/repository/p!{$this->api_data['subscriberId']}/folderc/snx:files!{$this->api_data['subscriberId']}";
        $GLOBALS['log']->debug("LOTUS REQUEST: $url");
        $rawResponse = $client->setUri($url)
            ->setRawData(file_get_contents($fileToUpload), $mimeType?$mimeType:"application/octet-stream")
            ->setHeaders("slug", $docName)
            ->request("POST");
        $reply = array('rawResponse' => $rawResponse->getBody());
//        $GLOBALS['log']->debug("REQUEST: ".var_export($client->getLastRequest(), true));
//        $GLOBALS['log']->debug("RESPONSE: ".var_export($rawResponse, true));
        if(!$rawResponse->isSuccessful() || empty($reply['rawResponse'])) {
            $reply['success'] = false;
            // FIXME: Translate
            $reply['errorMessage'] = $GLOBALS['app_strings']['ERR_BAD_RESPONSE_FROM_SERVER'].': '.$rawResponse->getMessage();
            return;
        }

        $xml = new DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->strictErrorChecking = false;
        $xml->loadXML($reply['rawResponse']);
        if ( !is_object($xml) ) {
            $reply['success'] = false;
            // FIXME: Translate
            $reply['errorMessage'] = $GLOBALS['app_strings']['ERR_BAD_RESPONSE_FROM_SERVER'].': '.print_r(libxml_get_errors(),true);
            return;
        }

        $xp = new DOMXPath($xml);
        $url = $xp->query('//atom:entry/atom:link[attribute::rel="alternate"]');
        $directUrl = $xp->query('//atom:entry/atom:link[attribute::rel="edit-media"]');
        $id = $xp->query('//atom:entry/cmisra:pathSegment');

        if ( !is_object($url) || !is_object($directUrl) || !is_object($id) ) {
            $reply['success'] = false;
            // FIXME: Translate
            $reply['errorMessage'] = $GLOBALS['app_strings']['ERR_BAD_RESPONSE_FROM_SERVER'];
            return;
        }
        $bean->doc_url = $url->item(0)->getAttribute("href");
        $bean->doc_direct_url = $directUrl->item(0)->getAttribute("href");
        $bean->doc_id = $id->item(0)->textContent;

        // Refresh the document cache
        $this->loadDocCache(true);

        return array('success'=>TRUE);
    }

    public function deleteDoc($document)
    {
        $client = $this->getClient();
        $url = $this->url."files/basic/cmis/repository/p!{$this->api_data['subscriberId']}/object/snx:file!{$document->doc_id}";
        $GLOBALS['log']->debug("LOTUS REQUEST: $url");
        $rawResponse = $client->setUri($url)
            ->request("DELETE");
        $reply = array('rawResponse' => $rawResponse->getBody());
        $GLOBALS['log']->debug("REQUEST: ".var_export($client->getLastRequest(), true));
        $GLOBALS['log']->debug("RESPONSE: ".var_export($rawResponse, true));

        // Refresh the document cache
        $this->loadDocCache(true);

        return array('success'=>TRUE);
    }

    public function downloadDoc($documentId, $documentFormat){}
    public function shareDoc($documentId, $emails){}

    public function loadDocCache($forceReload = false) {
        global $db, $current_user;

        create_cache_directory('/include/externalAPI/');
        $cacheFileBase = 'cache/include/externalAPI/docCache_'.$current_user->id.'_LotusLiveDirect';
        if ( !$forceReload && file_exists($cacheFileBase.'.php') ) {
            // File exists
            include_once($cacheFileBase.'.php');
            if ( abs(time()-$docCache['loadTime']) < 3600 ) {
                // And was last updated an hour or less ago
                return $docCache['results'];
            }
        }

        $reply = $this->makeRequest('/files/basic/cmis/repository/p!'.$this->api_data['subscriberId'].'/folderc/snx:files!'.$this->api_data['subscriberId'].'?maxItems=50','GET',false);

        $xml = new DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->strictErrorChecking = false;
        $xml->loadXML($reply['rawResponse']);
        if ( !is_object($xml) ) {
            $reply['success'] = false;
            // FIXME: Translate
            $reply['errorMessage'] = $GLOBALS['app_strings']['ERR_BAD_RESPONSE_FROM_SERVER'].': '.print_r(libxml_get_errors(),true);
            return;
        }

        $xp = new DOMXPath($xml);

        $results = array();

        $fileNodes = $xp->query('//atom:feed/atom:entry');
        foreach ( $fileNodes as $fileNode ) {
            $result = array();

            $idTmp = $xp->query('.//atom:id',$fileNode);
            list($dontcare,$result['id']) = explode("!",$idTmp->item(0)->textContent);

            $nameTmp = $xp->query('.//atom:title',$fileNode);
            $result['name'] = $nameTmp->item(0)->textContent;

            $timeTmp = $xp->query('.//atom:updated',$fileNode);
            $timeTmp2 = $timeTmp->item(0)->textContent;
            $result['date_modified'] = preg_replace('/^([^T]*)T([^.]*)\....Z$/','\1 \2',$timeTmp2);

            $result['url'] = $this->url.'files/filer2/home.do#files.do?subContent=fileDetails.do?fileId='.$result['id'];

            $results[] = $result;
        }


        $docCache['loadTime'] = time();
        $docCache['results'] = $results;
        $fd = fopen($cacheFileBase.'_tmp.php','w');
        fwrite($fd,'<'."?php\n// This file was auto generated by ".basename(__FILE__)." do not overwrite.\n\n".'$docCache = '.var_export($docCache,true).";\n");
        fclose($fd);
        rename($cacheFileBase.'_tmp.php',$cacheFileBase.'.php');

        return $results;
    }
    public function searchDoc($keywords,$flushDocCache=false){
        $docList = $this->loadDocCache($flushDocCache);

        $results = array();

        $searchLen = strlen($keywords);

        if(!empty($keywords)){
            foreach ( $docList as $doc ) {
                if ( stristr($doc['name'],$keywords) !== FALSE ) {
                    // It matches
                    $results[] = $doc;

                    if ( count($results) > 15 ) {
                        // Only return the first 15 results
                        break;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Make request to a service
     * @param unknown_type $url
     * @param unknown_type $method
     * @param unknown_type $json
     */
    protected function makeRequest($urlReq, $method = 'GET', $json = true)
    {
        $client = $this->getClient();
        $url = rtrim($this->url,"/")."/".ltrim($urlReq, "/");
        $GLOBALS['log']->debug("REQUEST: $url");
        $rawResponse = $client->setUri($url)->request($method);
        $reply = array('rawResponse' => $rawResponse->getBody());
        $GLOBALS['log']->debug("RESPONSE: ".var_export($rawResponse, true));
        if($json) {
            $response = json_decode($reply['rawResponse'],true);
            $GLOBALS['log']->debug("RESPONSE-JSON: ".var_export($response, true));
            if ( empty($rawResponse) || !is_array($response) ) {
                $reply['success'] = FALSE;
                // FIXME: Translate
                $reply['errorMessage'] = $GLOBALS['app_strings']['ERR_BAD_RESPONSE_FROM_SERVER'];
            } else {
                $reply['responseJSON'] = $response;
                $reply['success'] = TRUE;
            }
        } else {
            $reply['success'] = true;
        }

        return $reply;
    }

}