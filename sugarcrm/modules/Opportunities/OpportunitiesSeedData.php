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
 ********************************************************************************/

/**
 * OpportunitiesSeedData.php
 *
 * This is a class used for creating OpportunitiesSeedData.  We moved this code out from install/populateSeedData.php so
 * that we may better control and test creating default Opportunities.
 *
 */

class OpportunitiesSeedData {

    static private $_ranges;
    /**
     * populateSeedData
     *
     * This is a static function to create Opportunities.
     *
     * @static
     * @param $records Integer value indicating the number of Opportunities to create
     * @param $app_list_strings Array of application language strings
     * @param $accounts Array of Account instances to randomly build data against
     //BEGIN SUGARCRM flav=pro ONLY
     * @param $timeperiods Array of Timeperiods to create timeperiod seed data off of
     * @param $users Array of User instances to randomly build data against
     //END SUGARCRM flav=pro ONLY
     * @return array Array of Opportunities created
     */
    public static function populateSeedData($records, $app_list_strings, $accounts
    //BEGIN SUGARCRM flav=pro ONLY
        , $users
    //END SUGARCRM flav=pro ONLY
    )
    {
        if(empty($accounts) || empty($app_list_strings) || (!is_int($records) || $records < 1)
    //BEGIN SUGARCRM flav=pro ONLY
           || empty($users)
    //END SUGARCRM flav=pro ONLY
    
        )
        {
            return array();
        }
    
        $opp_ids = array();
        $timedate = TimeDate::getInstance();
    
        // get the additional currencies from the table
        /* @var $currency Currency */
        $currency = SugarCurrency::getCurrencyByISO('EUR');
        
        while($records-- > 0)
        {
            $key = array_rand($accounts);
            $account = $accounts[$key];
    
            /* @var $opp Opportunity */
            $opp = BeanFactory::getBean('Opportunities');
    
            //Create new opportunities
            //BEGIN SUGARCRM flav=pro ONLY
            $opp->team_id = $account->team_id;
            $opp->team_set_id = $account->team_set_id;
            //END SUGARCRM flav=pro ONLY
    
            $opp->assigned_user_id = $account->assigned_user_id;
            $opp->assigned_user_name = $account->assigned_user_name;
    
            // figure out which one to use
            $seed = rand(1, 15);
            if($seed%5 == 0) {
                $opp->currency_id = $currency->id;
                $opp->base_rate = $currency->conversion_rate;
            } else {
                // use the base rate
                $opp->currency_id = '-99';
                $opp->base_rate = 1;
            }
    
            $opp->name = substr($account->name." - 1000 units", 0, 50);
            $opp->lead_source = array_rand($app_list_strings['lead_source_dom']);
            $opp->sales_stage = array_rand($app_list_strings['sales_stage_dom']);
            $opp->sales_status = 'New';
    
            // If the deal is already done, make the date closed occur in the past.
            $opp->date_closed = ($opp->sales_stage == Opportunity::STAGE_CLOSED_WON || $opp->sales_stage == Opportunity::STAGE_CLOSED_WON)
                ? self::createPastDate()
                : self::createDate();
            $opp->date_closed_timestamp = $timedate->fromDbDate($opp->date_closed)->getTimestamp();
            $opp->opportunity_type = array_rand($app_list_strings['opportunity_type_dom']);
            $amount = array("10000", "25000", "50000", "75000");
            $key = array_rand($amount);
            $opp->amount = $amount[$key];
            $probability = array("10", "40", "70", "90");
            $key = array_rand($probability);
            $opp->probability = $probability[$key];
    
            //BEGIN SUGARCRM flav=pro ONLY
            //Setup forecast seed data
            $opp->best_case = $opp->amount;
            $opp->worst_case = $opp->amount;
            $opp->commit_stage = $opp->probability >= 70 ? 'include' : 'exclude';
    
            $opp->id = create_guid();
            $opp->new_with_id = true;
    
            // we need to save the opp before we create the rlis
            $opp->save();
    
            //END SUGARCRM flav=pro ONLY
            //BEGIN SUGARCRM flav=pro && flav!=ent ONLY
            $rlis_to_create = 1;
            //end SUGARCRM flav=pro && flav!=ent ONLY
            //BEGIN SUGARCRM flav=ent ONLY
            $rlis_to_create = rand(3, 5);
            //END SUGARCRM flav=ent ONLY
            //BEGIN SUGARCRM flav=pro ONLY
            $rlis_created = 0;
    
            $opp_best_case = 0;
            $opp_worst_case = 0;
            $opp_amount = 0;
    
            while($rlis_created < $rlis_to_create) {
                //BEGIN SUGARCRM flav=pro && flav!=ent ONLY
                $amount = $opp->amount;
                $rand_best_worst = rand(1000,5000);
                //end SUGARCRM flav=pro && flav!=ent ONLY
                //BEGIN SUGARCRM flav=ent ONLY
                $amount = rand(10000, 75000);
                $rand_best_worst = rand(1000, 9000);
                //END SUGARCRM flav=ent ONLY

                /* @var $rli RevenueLineItem */
                $rli = BeanFactory::getBean('RevenueLineItems');
                
                $rli->name = $opp->name;
                $rli->best_case = $amount+$rand_best_worst;
                $rli->likely_case = $amount;
                $rli->worst_case = $amount-$rand_best_worst;
                $rli->list_price = $amount;
                $rli->discount_price = $amount;
                $rli->cost_price = $amount/2;
                $rli->quantity = rand(1, 10);
                $rli->currency_id = $opp->currency_id;
                $rli->base_rate = $opp->base_rate;
                $rli->probability = $opp->probability;
                $rli->date_closed = $opp->date_closed;
                $rli->date_closed_timestamp = $opp->date_closed_timestamp;
                $rli->assigned_user_id = $opp->assigned_user_id;
                $rli->opportunity_id = $opp->id;
                $rli->account_id = $account->id;
                $rli->commit_stage = $opp->commit_stage;
                $rli->sales_stage = $opp->sales_stage;
                $rli->save();
    
                $opp_amount += $amount;
                $opp_best_case += $amount+$rand_best_worst;
                $opp_worst_case += $amount-$rand_best_worst;
    
                $rlis_created++;
            }
    
            $opp->amount = $opp_amount;
            $opp->best_case = $opp_best_case;
            $opp->worst_case = $opp_worst_case;
    
            //END SUGARCRM flav=pro ONLY
    
            // set the acccount on the opps, just for saving to the worksheet table
            $opp->account_id = $account->id;
            $opp->account_name = $account->name;
    
            // save the opp again
            $opp->save();
    
            //BEGIN SUGARCRM flav=pro ONLY
            // save a draft worksheet for the new forecasts stuff
            /* @var $worksheet ForecastWorksheet */
            $worksheet = BeanFactory::getBean('ForecastWorksheets');
            $worksheet->saveRelatedOpportunity($opp);
            //BEGIN SUGARCRM flav=ent ONLY
            $worksheet->saveOpportunityProducts($opp);
            //END SUGARCRM flav=ent ONLY
    
            // Create a linking table entry to assign an account to the opportunity.
            $opp->set_relationship('accounts_opportunities', array('opportunity_id'=>$opp->id ,'account_id'=> $account->id), false);
            $opp_ids[] = $opp->id;
        }
    
        return $opp_ids;
    }

    /**
     * @static creates range of probability for the months
     * @param int $total_months - total count of months
     * @return mixed
     */
    private static function getRanges($total_months = 12)
    {
        if ( self::$_ranges === null )
        {
            self::$_ranges = array();
            for ($i = $total_months; $i >= 0; $i--)
            {
                // define priority for month,
                self::$_ranges[$total_months-$i] = ( $total_months-$i > 6 )
                    ? self::$_ranges[$total_months-$i] = pow(6, 2) + $i
                    :  self::$_ranges[$total_months-$i] = pow($i, 2) + 1;
                // increase probability for current quarters
                self::$_ranges[$total_months-$i] = $total_months-$i == 0 ? self::$_ranges[$total_months-$i]*2.5 : self::$_ranges[$total_months-$i];
                self::$_ranges[$total_months-$i] = $total_months-$i == 1 ? self::$_ranges[$total_months-$i]*2 : self::$_ranges[$total_months-$i];
                self::$_ranges[$total_months-$i] = $total_months-$i == 2 ? self::$_ranges[$total_months-$i]*1.5 : self::$_ranges[$total_months-$i];
            }
        }
        return self::$_ranges;
    }

    /**
     * @static return month delta as random value using range of probability, 0 - current month, 1 next/previos month...
     * @param int $total_months - total count of months
     * @return int
     */
    public static function getMonthDeltaFromRange($total_months = 12)
    {
        $ranges = self::getRanges($total_months);
        asort($ranges,SORT_NUMERIC );
        $x = mt_rand (1, array_sum($ranges) );
        foreach ($ranges as $key => $y)
        {
            $x -= $y;
            if ( $x <= 0 )
            {
                break;
            }
        }
        return $key;
    }

    /**
     * @static generates date
     * @param null $monthDelta - offset from current date in months to create date, 0 - current month, 1 - next month
     * @return string
     */
    public static function createDate($monthDelta = null)
    {
        global $timedate;
        $monthDelta = $monthDelta === null ? self::getMonthDeltaFromRange() : $monthDelta;

        $now = $timedate->getNow(true);
        $now->modify("+$monthDelta month");
        // random day from now to end of month
        $now->setTime(0,0,0);
        $day = mt_rand($now->day, $now->days_in_month);
        return $timedate->asDbDate($now->get_day_begin($day));
    }

    /**
     * @static generate past date
     * @param null $monthDelta - offset from current date in months to create past date, 0 - current month, 1 - previous month
     * @return string
     */
    public static function createPastDate($monthDelta = null)
    {
        global $timedate;
        $monthDelta = $monthDelta === null ? self::getMonthDeltaFromRange() : $monthDelta;

        $now = $timedate->getNow(true);
        $now->modify("-$monthDelta month");

        if ( $monthDelta == 0 && $now->day == 1 ) {
            $now->modify("-1 day");
            $day = $now->day;
        }
        else
        {
            // random day from start of month to now
            $day =  mt_rand(1, $now->day);
        }
        $now->setTime(0,0,0); // always default it to midnight
        return $timedate->asDbDate($now->get_day_begin($day));
    }
}
