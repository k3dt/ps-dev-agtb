<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler;

use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\MultiFieldProperty;
use Sugarcrm\Sugarcrm\Elasticsearch\Exception\MappingException;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields;

/**
 *
 * Generic Mapping Handler using multi fields
 *
 */
class MultiFieldHandler extends AbstractHandler implements
    AnalysisHandlerInterface,
    MappingHandlerInterface,
    SearchFieldsHandlerInterface
{
    /**
     * Mappings for sugar types using multi field definition
     * @var array
     */
    protected $typesMultiField = array(
        'varchar' => array(
            'gs_string',
            'gs_string_wildcard',
        ),
        'name' => array(
            'gs_string',
            'gs_string_wildcard',
        ),
        'text' => array(
            'gs_string',
            'gs_text_wildcard',
        ),
        'datetime' => array(
            'gs_datetime',
        ),
        'date' => array(
            'gs_date',
        ),
        'int' => array(
            'gs_integer',
            'gs_string',
            'gs_string_wildcard',
        ),
        'phone' => array(
            'not_analyzed',
            'gs_phone_wildcard',
        ),
        'url' => array(
            'gs_url',
            'gs_url_wildcard',
        ),
        'id' => array(
            'not_analyzed',
        ),
        'exact' => array(
            'gs_string_exact',
        ),
        'longtext' => array(
            'gs_string',
            'gs_text_wildcard',
        ),
        'htmleditable_tinymce' => array(
            'gs_html_default',
        ),
        'enum' => array(
            'not_analyzed',
        ),
        'assigned_user_name' => array(
            'not_analyzed',
        )
    );

    /**
     * Multi field definitions
     * @var array
     */
    protected $multiFieldDefs = array(

        /*
         * This is a special analyzer to be able to use fields with
         * not_analyzed values only. This is part of the multi field
         * definition as every multi field is not_analyzed by default.
         */
        'not_analyzed' => array(),

        /*
         * Default string analyzer with full word matching base ond
         * the standard analyzer. This will generate hits on the full
         * words tokenized by the standard analyzer.
         */
        'gs_string' => array(
            'type' => 'string',
            'index' => 'analyzed',
            'index_analyzer' => 'gs_analyzer_string',
            'search_analyzer' => 'gs_analyzer_string',
            'store' => false,
        ),

        /*
         * String analyzer using ngrams for wildcard matching. The
         * weighting of the hits on this mapping are less than full
         * matches using the default string mapping.
         */
        'gs_string_wildcard' => array(
            'type' => 'string',
            'index' => 'analyzed',
            'index_analyzer' => 'gs_analyzer_string_ngram',
            'search_analyzer' => 'gs_analyzer_string',
            'store' => false,
        ),

        /*
         * Wildcard analyzer for text fields. Because they can become
         * big we use a text_ngram definition.
         */
        'gs_text_wildcard' => array(
            'type' => 'string',
            'index' => 'analyzed',
            'index_analyzer' => 'gs_analyzer_text_ngram',
            'search_analyzer' => 'gs_analyzer_string',
            'store' => false,
        ),

        /*
         * Date field mapping. Date fields are not searchable but are
         * needed to be returned as part of the dataset and to be able
         * to perform facets on. Note that the index cannot be 'no' to
         * return the fields of 'gs_datetime' type in the facets.
         */
        'gs_datetime' => array(
            'type' => 'date',
            'format' => 'YYYY-MM-dd HH:mm:ss',
            'store' => false,
        ),
        'gs_date' => array(
            'type' => 'date',
            'format' => 'YYYY-MM-dd',
            'store' => false,
        ),

        /*
         * Integer mapping.
         */
        'gs_integer' => array(
            'type' => 'integer',
            'index' => 'no',
            'store' => false,
        ),

        /*
         * Phone mapping. The analyzer supports partial matches using
         * ngrams and transforms every phone number in pure numbers
         * only to be able to search for different formats and still
         * get hits. For example the data source for +32 (475)61.64.28
         * will be stored and analyzed as 32475616428 including ngrams
         * based on this result. When phone number fields are included
         * in the search matching will happen when searching for:
         *      +32 475 61.64.28
         *      (32)475-61-64-28
         *      ...
         */
        'gs_phone_wildcard' => array(
            'type' => 'string',
            'index' => 'analyzed',
            'index_analyzer' => 'gs_analyzer_phone_ngram',
            'search_analyzer' => 'gs_analyzer_phone',
            'store' => false,
        ),

        /*
         * URL analyzer
         */
        'gs_url' => array(
            'type' => 'string',
            'index' => 'analyzed',
            'index_analyzer' => 'gs_analyzer_url',
            'search_analyzer' => 'gs_analyzer_url',
            'store' => false,
        ),

        /*
         * Wildcard matching for URLs.
         */
        'gs_url_wildcard' => array(
            'type' => 'string',
            'index' => 'analyzed',
            'index_analyzer' => 'gs_analyzer_url_ngram',
            'search_analyzer' => 'gs_analyzer_url',
            'store' => false,
        ),

        /*
         * String analyzer with full word matching base ond
         * the whitespace analyzer. This will generate hits on the full
         * words tokenized by the whitespace analyzer.
         */
        'gs_string_exact' => array(
            'type' => 'string',
            'index' => 'analyzed',
            'index_analyzer' => 'gs_analyzer_string_exact',
            'search_analyzer' => 'gs_analyzer_string_exact',
            'store' => false,
        ),

        /*
         * Analyzer for html
         */
        'gs_html_default' => array(
            'type' => 'string',
            'index' => 'analyzed',
            'index_analyzer' => 'gs_analyzer_html_default',
            'search_analyzer' => 'gs_analyzer_html_default',
            'store' => false,
        ),
    );

    /**
     * Weighted boost definition
     * @var array
     */
    protected $weightedBoost = array(
        'gs_string_wildcard' => 0.45,
        'gs_text_wildcard' => 0.35,
        'gs_phone_wildcard' => 0.20,
        'gs_url_wildcard' => 0.35,
    );

    /**
     * Highlighter field definitions
     * @var array
     */
    protected $highlighterFields = array(
        '*.gs_string' => array(),
        '*.gs_string_exact' => array(),
        '*.gs_string_wildcard' => array(),
        '*.gs_text_wildcard' => array(),
        '*.gs_phone_wildcard' => array(
            'number_of_frags' => 0,
        ),
        '*.gs_url' => array(
            'number_of_frags' => 0,
        ),
        '*.gs_url_wildcard' => array(
            'number_of_frags' => 0,
        ),
    );

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->provider->addSupportedTypes(array_keys($this->typesMultiField));
        $this->provider->addWeightedBoosts($this->weightedBoost);
        $this->provider->addHighlighterFields($this->highlighterFields);
    }

    /**
     * {@inheritdoc}
     */
    public function buildAnalysis(AnalysisBuilder $analysisBuilder)
    {
        $analysisBuilder

            // ngram filter using 1/15
            ->addFilter(
                'gs_filter_ngram_1_15',
                'nGram',
                array('min_gram' => 1, 'max_gram' => 15)
            )

            // ngram filter using 2_15
            ->addFilter(
                'gs_filter_ngram_2_15',
                'nGram',
                array('min_gram' => 2, 'max_gram' => 15)
            )

            // ngram filter using 3_15
            ->addFilter(
                'gs_filter_ngram_3_15',
                'nGram',
                array('min_gram' => 3, 'max_gram' => 15)
            )

            // char filter keeping only numeric values and spaces
            ->addCharFilter(
                'gs_char_num_pattern',
                'pattern_replace',
                array('pattern' => '[^\\d\\s]+', 'replacement' => '')
            )

            // base analyzer using standard tokenizer
            ->addCustomAnalyzer(
                'gs_analyzer_string',
                'standard',
                array('lowercase')
            )

            // base ngram analyzer
            ->addCustomAnalyzer(
                'gs_analyzer_string_ngram',
                'standard',
                array('lowercase', 'gs_filter_ngram_1_15')
            )

            // phone analyzer
            ->addCustomAnalyzer(
                'gs_analyzer_phone',
                'whitespace',
                array(),
                array('gs_char_num_pattern')
            )

            // phone ngram analyzer
            ->addCustomAnalyzer(
                'gs_analyzer_phone_ngram',
                'whitespace',
                array('gs_filter_ngram_3_15'),
                array('gs_char_num_pattern')
            )

            // analyzer for text fields with lower tokens
            ->addCustomAnalyzer(
                'gs_analyzer_text_ngram',
                'standard',
                array('lowercase', 'gs_filter_ngram_2_15')
            )

            // url analyzer
            ->addCustomAnalyzer(
                'gs_analyzer_url',
                'uax_url_email',
                array('lowercase')
            )

            // url ngram analyzer
            ->addCustomAnalyzer(
                'gs_analyzer_url_ngram',
                'uax_url_email',
                array('lowercase', 'gs_filter_ngram_2_15')
            )

            // String Analyzer using whitespace tokenizer for exact matching
            ->addCustomAnalyzer(
                'gs_analyzer_string_exact',
                'whitespace',
                array('lowercase')
            )

            // html analyzer
            ->addCustomAnalyzer(
                'gs_analyzer_html_default',
                'standard',
                array('lowercase'),
                array('html_strip')
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildMapping(Mapping $mapping, $field, array $defs)
    {
        // Skip field if no multi field mapping has been defined or no type available
        if (!isset($defs['type']) || !isset($this->typesMultiField[$defs['type']])) {
            return;
        }

        $isCrossModuleDefined = $this->isCrossModuleDefined($defs);
        foreach ($this->typesMultiField[$defs['type']] as $multiField) {
            if ($multiField === 'not_analyzed') {
                $mapping->addNotAnalyzedField($field, false, $isCrossModuleDefined);
            } else {
                $multiFieldProperty = $this->getMultiFieldProperty($multiField);
                $mapping->addMultiField(
                    $field,
                    $multiField,
                    $multiFieldProperty,
                    $this->crossModuleEnabled,
                    $isCrossModuleDefined
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchFields(SearchFields $sf, $module, $field, array $defs)
    {
        // Skip field if no multi field mapping has been defined or no type available
        if (!isset($defs['type']) || !isset($this->typesMultiField[$defs['type']])) {
            return;
        }

        // Add fields which are based on strings
        foreach ($this->getStringFieldsForType($defs['type']) as $searchField) {
            if ($searchField === 'not_analyzed') {
                $path = array($field);
                $weightId = $field;
                // add explicit field to highlighter
                $this->addHighlighterField($module, $field, array('number_of_frags' => 0));
            } else {
                $path = array($field, $searchField);
                $weightId = $searchField;
            }
            $sf->addSearchField($module, $path, $defs, $weightId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedTypes()
    {
        return array_keys($this->typesMultiField);
    }

    /**
     * Add additional field to the highlighter
     * @param string $module Module name
     * @param string $field Field name
     * @param array $settings Highlighter settings
     */
    protected function addHighlighterField($module, $field, array $settings = array())
    {
        $highlightField = $module . Mapping::PREFIX_SEP . $field;
        $this->provider->addHighlighterFields(array($highlightField => $settings));
    }

    /**
     * Get search field list for given field type
     * @param unknown $type
     * @return multitype:unknown
     */
    protected function getStringFieldsForType($type)
    {
        $list = array();
        foreach ($this->typesMultiField[$type] as $multiFieldDef) {
            if ($this->isStringBased($multiFieldDef)) {
                $list[] = $multiFieldDef;
            }
        }
        return $list;
    }

    /**
     * Check if given multi field definition is string based
     * @param string $multiFieldDef Multi field definition name
     * @return boolean
     */
    protected function isStringBased($multiFieldDef)
    {
        // special case for not_analyzed fields
        if ($multiFieldDef === 'not_analyzed') {
            return true;
        }

        $defs = $this->multiFieldDefs[$multiFieldDef];
        if (isset($defs['type']) && $defs['type'] === 'string') {
            return true;
        }

        return false;
    }

    /**
     * Get multi field property object
     * @param string $name Multi field property name
     * @throws MappingException
     * @return MultiFieldProperty
     */
    protected function getMultiFieldProperty($name)
    {
        if (!isset($this->multiFieldDefs[$name])) {
            throw new MappingException("Unknown multi field definition '{$name}'");
        }

        if (!isset($this->multiFieldDefs[$name]['type'])) {
            throw new MappingException("Multi field definition '{$name}' missing required type");
        }

        $multiField = new MultiFieldProperty();
        $multiField->setCrossModuleEnabled($this->crossModuleEnabled);
        $multiField->setType($this->multiFieldDefs[$name]['type']);
        $multiField->setMapping($this->multiFieldDefs[$name]);

        return $multiField;
    }

    /**
     * @var boolean the flag to enable or disable adding cross_module fields
     */
    protected $crossModuleEnabled = false;

    /**
     * Set the flag crossModuleEnabled.
     * @param boolean $value TRUE or FALSE
     */
    public function setCrossModuleEnabled($value)
    {
        $this->crossModuleEnabled = $value;
    }
}
