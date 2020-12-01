<?php

class ContactsHookImpl
{

    public function before_save($bean, $event, $arguments)
    {
        $this->calcMatchFields($bean, $event, $arguments);
    }

    protected function calcMatchFields($bean, $event, $arguments)
    {
        $this->calcCountryMatch($bean, $event, $arguments);
        $this->calcFunctionMatch($bean, $event, $arguments);
    }

    protected function calcCountryMatch($bean, $event, $arguments)
    {
        $result = array();

        if(strlen($bean->primary_address_country)) {
            $result[] = $bean->primary_address_country;
        }

        $result = array_merge($result, explode('^,^', trim($bean->geo_mobility_country_1_c, '^')));
        $result = array_merge($result, explode('^,^', trim($bean->geo_mobility_country_2_c, '^')));
        $result = array_merge($result, explode('^,^', trim($bean->geo_mobility_country_3_c, '^')));
        $result = array_merge($result, explode('^,^', trim($bean->geo_mobility_country_4_c, '^')));
        $result = array_merge($result, explode('^,^', trim($bean->geo_mobility_country_5_c, '^')));
        $result = array_merge($result, explode('^,^', trim($bean->geo_mobility_country_6_c, '^')));
        $result = array_unique($result);

        if(count($result)) {
            $bean->gtb_country_match_c = '^'.implode('^,^', $result).'^';
        }
    }

    protected function calcFunctionMatch($bean, $event, $arguments)
    {
        $result = array();

        if(strlen($bean->function_c)) {
            $result[] = $bean->function_c;
        }

        $result = array_merge($result, explode('^,^', trim($bean->functional_mobility_c, '^')));
        $result = array_unique($result);

        if(count($result)) {
            $bean->gtb_function_match_c = '^'.implode('^,^', $result).'^';
        }
    }
}
