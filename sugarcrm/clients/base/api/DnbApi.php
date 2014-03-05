<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once('include/externalAPI/Dnb/ExtAPIDnb.php');

class DnbApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'dnbDirectGet' => array(
                'reqType' => 'GET',
                'path' => array('connector','dnb','?','?'),
                'pathVars' => array('connector','dnb','qtype','qparam'),
                'method' => 'dnbDirectGet',
                'shortHelp' => 'Invoke DNB API using GET',
                'noLoginRequired' => true,
                'longHelp' => 'include/api/help/dnb_get_help.html',
            ),
            'dnbDirectPost' => array(
                'reqType' => 'POST',
                'path' => array('connector','dnb','?'),
                'pathVars' => array('connector','dnb','qtype'),
                'method' => 'dnbDirectPost',
                'shortHelp' => 'Invoke DNB API using POST',
                'noLoginRequired' => true,
                'longHelp' => 'include/api/help/dnb_post_help.html',
            ),
        );
    }

    /**
     * gets dnb EAPM
     * @return array|bool|ExternalAPIBase
     */
    public function getEAPM()
    {
        $dnbEAPM = ExternalAPIFactory::loadAPI('Dnb',true);
        $dnbEAPM->getConnector();
        if(!$dnbEAPM->getConnectorParam('dnb_username') || 
           !$dnbEAPM->getConnectorParam('dnb_password') || 
           !$dnbEAPM->getConnectorParam('dnb_env')){
             return array('error' =>'ERROR_DNB_CONFIG');
        }
        return $dnbEAPM;
    }

    /**
     * Invokes D&B API using GET
     * @param $api
     * @param $args
     * @return mixed
     * @throws SugarApiExceptionRequestMethodFailure
     * @throws SugarApiExceptionMissingParameter
     */
    public function dnbDirectGet($api,$args)
    {
        //invoke dnb api based on query type and query parameter
        $extDnbApi = $this->getEAPM();
        if (is_array($extDnbApi) && isset($extDnbApi['error'])) {
            throw new SugarApiExceptionRequestMethodFailure(null, $args, null, 424, $extDnbApi['error']);
        }
        if ($extDnbApi === false) {
           throw new SugarApiExceptionRequestMethodFailure($GLOBALS['app_strings']['ERROR_UNABLE_TO_RETRIEVE_DATA'], $args);
        }
        $queryType = $args['qtype'];
        $queryParam = $args['qparam'];
        if (!$extDnbApi->isConnectorConfigured()) {
            return array('error' =>'ERROR_DNB_CONFIG');
        }
        $result = '';
        if ($queryType === 'search'){
            $result = $extDnbApi->dnbSearch($queryParam);
        } else if ($queryType === 'profile') {
            $result = $extDnbApi->dnbProfile($queryParam);
        } else if ($queryType ==='competitors') {
            $result = $extDnbApi->dnbCompetitors($queryParam);
        } else if ($queryType ==='industry') {
            $result = $extDnbApi->dnbIndustryInfo($queryParam);
        } else if ($queryType ==='financial') {
            $result = $extDnbApi->dnbFinancialInfo($queryParam);
        } else if ($queryType ==='familytree') {
            $result = $extDnbApi->dnbFamilyTree($queryParam);
        } else if ($queryType ==='firmographic') {
            $result = $extDnbApi->dnbStandardProfile($queryParam);
        } else if ($queryType ==='premfirmographic') {
            $result = $extDnbApi->dnbPremiumProfile($queryParam);
        } else if ($queryType ==='findIndustry') {
            $result = $extDnbApi->dnbIndustrySearch($queryParam);
        } else if ($queryType === 'findContacts') {
            $result = $extDnbApi->dnbFindContacts($queryParam);
        } else if ($queryType === 'refreshcheck') {
            $result = $extDnbApi->dnbRefreshCheck($queryParam);
        } else if ($queryType === 'litefirmographic') {
            $result = $extDnbApi->dnbLiteProfile($queryParam);
        } else if ($queryType === 'news') {
            $result = $extDnbApi->dnbNews($queryParam);
        }
        if (is_array($result) && isset($result['error'])) {
            throw new SugarApiExceptionRequestMethodFailure(null, $args, null, 424, $result['error']);
        }
        return $result;
    }

    /**
     * Invokes DNB Api using POST calls
     * @param $api
     * @param $args
     * @return mixed
     * @throws SugarApiExceptionRequestMethodFailure
     * @throws SugarApiExceptionMissingParameter
     */
    public function dnbDirectPost($api,$args)
    {
        //invoke dnb api based on query type and query data
        $extDnbApi = $this->getEAPM();
        if (is_array($extDnbApi) && isset($extDnbApi['error'])) {
            throw new SugarApiExceptionRequestMethodFailure(null, $args, null, 424, $extDnbApi['error']);
        }
        $queryType = $args['qtype'];
        $queryData = $args['qdata']; //data posted 
        $result = '';
        if ($queryType === 'cmRequest') {
            $result = $extDnbApi->dnbCMRrequest($queryData);
        } else if ($queryType === 'bal') {
            $result = $extDnbApi->dnbBALRequest($queryData);
        } else if ($queryType === 'contacts') {
            $result = $extDnbApi->dnbContactDetails($queryData);
        } else if ($queryType === 'indMap') {
            $result = $extDnbApi->dnbIndustryConversion($queryData);
        } else if ($queryType ==='industry') {
            $result = $extDnbApi->dnbIndustryInfoPost($queryData);
        } else if ($queryType ==='firmographic') {
            $result = $extDnbApi->dnbFirmographic($queryData);
        } else if ($queryType ==='findcontacts') {
            $result = $extDnbApi->dnbFindContactsPost($queryData);
        } else if($queryType === 'familytree') {
            $result = $extDnbApi->dnbFamilyTree($queryData);
        }
        if (is_array($result) && isset($result['error'])) {
            throw new SugarApiExceptionRequestMethodFailure(null, $args, null, 424, $result['error']);
        }
        return $result;
    }
}
