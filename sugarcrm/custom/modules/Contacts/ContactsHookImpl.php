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

        $result = array_merge($result, $this->getCountriesFromMultiEnum($bean->geo_mobility_country_1_c, $bean->geo_mobility_region_1_c));
        $result = array_merge($result, $this->getCountriesFromMultiEnum($bean->geo_mobility_country_2_c, $bean->geo_mobility_region_2_c));
        $result = array_merge($result, $this->getCountriesFromMultiEnum($bean->geo_mobility_country_3_c, $bean->geo_mobility_region_3_c));
        $result = array_merge($result, $this->getCountriesFromMultiEnum($bean->geo_mobility_country_4_c, $bean->geo_mobility_region_4_c));
        $result = array_merge($result, $this->getCountriesFromMultiEnum($bean->geo_mobility_country_5_c, $bean->geo_mobility_region_5_c));
        $result = array_merge($result, $this->getCountriesFromMultiEnum($bean->geo_mobility_country_6_c, $bean->geo_mobility_region_6_c));
        $result = array_unique($result);
        $result = $this->arrayfilter($result);

        if(count($result)) {
            $bean->gtb_country_match_c = '^'.implode('^,^', $result).'^';
        }
    }

    protected function getCountriesFromMultiEnum($country_field, $region_field)
    {
        // According to AGTB-62 we assume that Candidate->geo_mobility_country_1_c field will be ALWAYS there
        // ...and there will be ALWAYS dependent dropdown visibility_grid with region->countries hierarcy
        if(     empty($GLOBALS['dictionary']['Contact']['fields']['geo_mobility_country_1_c']['visibility_grid']['values'])
            ||  !is_array($GLOBALS['dictionary']['Contact']['fields']['geo_mobility_country_1_c']['visibility_grid']['values']))
        {
            $GLOBALS['log']->error('Issue in Candidates Logic Hook: field geo_mobility_country_1_c is missing visibility_grid (dependency dropdown hierarcy) to work properly for values All and Worldwide');
            return [];
        }
        $grid = $GLOBALS['dictionary']['Contact']['fields']['geo_mobility_country_1_c']['visibility_grid']['values'];
        $result = explode('^,^', trim($country_field, '^')) ?? [];
        if(in_array('All', $result)) {
            if ($region_field == 'Worldwide') {
                foreach($grid as $key => $region_countries) {
                    $result = array_merge($result, $region_countries);
                }
            } elseif(in_array($region_field, array_keys($grid))) {
                $result = array_merge($result, $grid[$region_field]);
            }
            $result = $this->arrayfilter($result, ['All']);
        }
        return $result;
    }

    private function arrayfilter($input_arr, $filter_out_values = [''])
    {
        $result = [];
        if(is_array($input_arr)) {
            foreach ($input_arr as $key => $value) {
                if(!in_array($value, $filter_out_values, true)) {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
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
