<?php

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
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

require_once('modules/Import/sources/ExternalSourceAdapter.php');

class ExternalSourceGoogleAdapter extends ExternalSourceAdapter
{

    /**
     * @var string The name of the EAPM object.
     */
    private $_eapmName = 'Google';

    /**
     * @var int Total record count of rows that will be imported
     */
    private $_totalRecordCount = -1;

    /**
     * @var int The offset start index
     */
    private $_offsetStart;

    /**
     * @var The record set loaded from the external source
     */
    private $_recordSet = array();

    protected $_localeSettings = array('importlocale_dateformat' => '',
                                       'importlocale_timeformat' => '',
                                       'importlocale_timezone' => '',
                                       'importlocale_currency' => '',
                                       'importlocale_default_currency_significant_digits' => '',
                                       'importlocale_num_grp_sep' => '',
                                       'importlocale_dec_sep' => '',
                                       'importlocale_default_locale_name_format' => '');
    
    /**
     * Return a feed of google contacts using the EAPM and Connectors farmework.
     *
     * @throws Exception
     * @param  $startIndex
     * @param  $maxResults
     * @return array
     */
    public function loadExternalRecordSet($startIndex, $maxResults)
    {
         if ( !$eapmBean = EAPM::getLoginInfo($this->_eapmName,true) )
         {
            throw new Exception('Authentication error with Google Contacts EAPM.');
         }

        $api = ExternalAPIFactory::loadAPI($this->_eapmName);
        $api->loadEAPM($eapmBean);
        $conn = $api->getConnector();

        $rows = array();

        $feed = $conn->getList(array('maxResults' => $maxResults, 'startIndex' => $startIndex));
        if($feed !== FALSE)
        {
            $this->_totalRecordCount = $feed->totalResults->getText();
            $this->_offsetStart = $feed->startIndex->getText();

            foreach ($feed->entries as $entry)
            {
                $rows[] = $entry->toArray();
            }
        }
        else
        {
            throw new Exception('Unable to retrieve google contact feed.');
        }

        $this->_recordSet = $rows;
    }

    public function getTotalRecordCount()
    {
        return $this->_totalRecordCount;
    }

    public function setSourceName($sourceName = '')
    {
        $this->_sourcename = $this->_eapmName;
    }

    //Begin Implementation for SPL's Iterator interface
    public function current()
    {
        return current($this->_recordSet);
    }

    public function key()
    {
        return key($this->_recordSet);
    }
    
    public function rewind()
    {
        reset($this->_recordSet);
    }

    public function next()
    {
        next($this->_recordSet);
    }

    public function valid()
    {
        return (current($this->_recordSet) !== FALSE);
    }
}

