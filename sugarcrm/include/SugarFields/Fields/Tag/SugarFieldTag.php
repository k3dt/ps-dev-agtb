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
require_once 'include/SugarFields/Fields/Relatecollection/SugarFieldRelatecollection.php';

/**
 * The SugarFieldTag handles the tag field
 */
class SugarFieldTag extends SugarFieldRelatecollection
{
    /**
     * Override of parent apiSave to force the custom save to be run from API
     * @param SugarBean $bean
     * @param array     $params
     * @param string    $field
     * @param array     $properties
     */
    public function apiSave(SugarBean $bean, array $params, $field, $properties)
    {
        // Exit save if nothing is to be saved, or if bean is being saved as part
        // of a dupe check, to avoid adding relationship rows twice.
        if (!is_array($params[$field]) || isset($this->options['find_duplicates'])) {
            return;
        }

        // get and load the relationship
        // then figure out the tags which have been added / deleted by comparing between
        // original tags and the submitted tags
        $relField = $properties['link'];

        if ($bean->load_relationship($relField)) {

            // if an empty array is passed delete all tags associated with the bean and exit
            // Be careful not to delete all when empty array and "add" passed in
            if (empty($params[$field]) &&
                (!isset($params[$field . '_type']) || ($params[$field . '_type'] === 'replace'))) {
                $bean->$relField->delete($bean->id);
                return;
            }

            // Loop through submitted Tags to make collection of  tag beans (either new or retrieved)
            $relBeans = array();
            foreach ($params[$field] as $key => $record) {
                // Collect all tag beans
                $relBeans[] = $this->getTagBean($record);
            }

            // get current tag beans on the record
            $currRelBeans = $bean->$relField->getBeans();

            // get the submitted values of the tags
            $changedTags = $this->getChangedTags($params, $field);

            // get list of original tags
            $originalTags = $this->getOriginalTags($currRelBeans);

            // Grab the changes from old to new
            list($addedTags, $removedTags) = $this->getChangedValues($originalTags, $changedTags);

            // Handle delete of tags
            // For mass append tag_type will be 'add' and hence delete can be skipped
            if (!isset($params[$field . '_type']) || ($params[$field . '_type'] === 'replace')) {
                $this->removeTagsFromBean($bean, $currRelBeans, $relField, $removedTags);
            }

            // Handle adding new tags
            $this->addTagsToBean($bean, $relBeans, $relField, $addedTags);

        } else {
            $GLOBALS['log']->fatal("Failed to load relationship $relField on {$bean->module_dir}");
        }

    }

    /**
     * Retrieve a tagBean or Create a new one
     * @param Array containing tag id and tag name with the keys (id, name)
     * @return SugarBean
     */
    protected function getTagBean($record)
    {
        // We'll need this no matter what
        $tagBean = BeanFactory::getBean('Tags');

        if (!empty($record['id'])) {
            if ($tagBean->retrieve($record['id'])) {
                return $tagBean;
            }
        }

        // Normalize the tag name
        $tagName = trim($record['name']);

        // See if this tag exists already. If it does send back the bean for it
        $q = $this->getSugarQuery();
        $q->select(array('id', 'name'));
        $q->from($tagBean);
        // Get the tag from the lowercase version of the name
        $q->where()->equals('name_lower', strtolower($tagName));
        $result = $q->execute();

        // If there is a result for this tag name, send back the bean for it
        if (!empty($result[0]['id'])) {
            if ($tagBean->retrieve($result[0]['id'])) {
                return $tagBean;
            }
        }

        // Create a new record and send back THAT bean
        $tagBean->fromArray(array('name' => $tagName));
        $tagBean->save();
        return $tagBean;
    }

    /**
     * {@inheritDoc}
     */
    public function apiFormatField(&$data, $bean, $args, $fieldName, $properties)
    {
        if (isset($args['rc_beans'])) {
            if (!empty($args['rc_beans'][$fieldName][$bean->id])) {
                $data[$fieldName] = $args['rc_beans'][$fieldName][$bean->id];
            } else {
                $data[$fieldName] = array();
            }
        } else {
            $relField = $properties['link'];
            $tags = array();

            if ($bean->load_relationship($relField)) {
                $currRelBeans = $bean->$relField->getBeans();

                if (!empty($currRelBeans)) {
                    // Placeholder for sorting the tags array by name
                    $names = array();

                    foreach ($currRelBeans as $tagId => $tagRecord) {
                        // Build a sort array for sorting the tag list
                        $names[$tagId] = $tagRecord->name;

                        // Build a tag list, using the sort tag name value as the tag
                        $tags[] = array('id' => $tagId, 'name' => $names[$tagId]);
                    }

                    // Sort the tags array in alphabetical order
                    array_multisort($names, SORT_ASC, $tags);
                }
            }

            $data[$fieldName] = $tags;
        }
    }


    /**
     * Gets an array of added and removed tags for a bean
     *
     * @param Array $first The initial array of values
     * @param Array $second The changed array of values
     * @return Array of added and removed tags
     */
    public function getChangedValues(Array $initial, Array $changed)
    {
        // Handle comparison on the keys
        $iKeys = array_keys($initial);
        $cKeys = array_keys($changed);
        // Added are what is in $changed that are not in $initial
        $a = array_diff($cKeys, $iKeys);
        // Removed are what is in $initial but not $changed
        $r = array_diff($iKeys, $cKeys);
        $added = $removed = array();
        foreach ($a as $add) {
            $added[$add] = $changed[$add];
        }
        foreach ($r as $rem) {
            $removed[$rem] = $initial[$rem];
        }
        return array($added, $removed);
    }

    /**
     * Gets an array of changed tags in the format tagname => tagname
     *
     * @param Array of Submitted Values
     * @param String - current field name (which would be "tag")
     * @return Array of Changed Tag Names
     */
    public function getChangedTags($params, $field)
    {
        $changedTags = array();
        if (!empty($params[$field])) {
            $submittedTags = $params[$field];
            foreach ($submittedTags as $submittedTag) {
                $changedTags[strtolower(trim($submittedTag['name']))] = trim($submittedTag['name']);
            }
        }
        return $changedTags;
    }

    /**
     * Gets an Array of original tags in the format tagname => tagname
     *
     * @param Array of Original Tag Beans on the Record
     * @return Array of Original Tag Names
     */
    public function getOriginalTags($currRelBeans)
    {
        $originalTags = array();
        if (!empty($currRelBeans)) {
            foreach ($currRelBeans as $tagId => $tagRecord) {
                $originalTags[strtolower($tagRecord->name)] = $tagRecord->name;
            }
        }
        return $originalTags;
    }

    /**
     * Remove Tags from the Bean
     *
     * @param SugarBean - The Bean from which the Tags need to be disassociated
     * @param Array of Current Tag Beans on the Record
     * @param String - relationship field
     * @param Array of Removed Tag Names
     * @return Void
     */
    public function removeTagsFromBean($bean, $currRelBeans, $relField, $removedTags)
    {
        foreach ($currRelBeans as $currRelBean) {
            if (isset($currRelBean->name_lower) && isset($removedTags[$currRelBean->name_lower])) {
                if (!$bean->$relField->delete($bean->id, $currRelBean->id)) {
                    // Log to fatal
                    $GLOBALS['log']->fatal("Failed to delete tag {$currRelBean->name} from {$bean->module_dir}");
                }
            }
        }
    }

    /**
     * Add Tags to the Bean
     *
     * @param SugarBean - The Bean to which the Tags need to be associated
     * @param Array of Added Tag Beans on the Record
     * @param String - relationship field
     * @param Array of Added Tag Names
     * @return Void
     */
    public function addTagsToBean($bean, $relBeans, $relField, $addedTags)
    {
        foreach ($relBeans as $relBean) {
            if (isset($addedTags[$relBean->name_lower])) {
                if (!$bean->$relField->add($relBean)) {
                    // Log to fatal
                    $GLOBALS['log']->fatal("Failed to add tag {$relBean->name} as a relate to {$bean->module_dir}");
                }
            }
        }
    }

    /**
     * Handles export field sanitizing for field type
     *
     * @param $value string value to be sanitized
     * @param $vardef array representing the vardef definition
     * @param $focus SugarBean object
     * @param $row Array of a row of data to be exported
     * @param $options Array of additional information including Tags
     * @return string sanitized value
     */
    public function exportSanitize($value, $vardef, $focus, $row = array())
    {
        if (!isset($row['id'])) {
            return trim($value);
        }

        if (isset($this->options[$row['id']])) {
            return $this->options[$row['id']];
        }

        $exportString = '';
        if (isset($this->options['relTags'][$row["id"]])) {
            foreach ($this->options['relTags'][$row["id"]] as $tag) {
                if (isset($tag['name'])) {
                    $exportString .= trim($tag['name']) . ", ";
                }
            }
        }
        return rtrim($exportString, ", ");
    }

    /**
     * Reads a string of input from an import process and gets the tag values from
     * that string. The import string should look like Value1,Value2,Value3
     *
     * @param string $value The import row of data
     * @return array
     */
    public function getTagValuesFromImport($value)
    {
        if (empty($value)) {
            return array();
        }

        if (is_array($value)) {
            return $value;
        }

        return explode(',', trim($value));
    }

    /**
     * Gets the tags for a bean as an array of values
     *
     * @param SugarBean $bean The SugarBean that you are getting a value of
     * @param string $field The field to get a normal value from
     * @return Array
     */
    public function getTagValues(SugarBean $bean, $field)
    {
        return $this->getNormalizedFieldValues($bean, $field);
    }

    /**
     * Gets the field values for the tag field as a cleaned up list of values
     *
     * @param SugarBean $bean The bean to get the values from
     * @param string $fieldName The name of the field to get normalized values from
     * @return array
     */
    public function getNormalizedFieldValues($bean, $fieldName)
    {
        $return = array();
        if (isset($bean->field_defs[$fieldName]['link'])) {
            $relField = $bean->field_defs[$fieldName]['link'];
            if ($bean->load_relationship($relField)) {
                $currRelBeans = $bean->$relField->getBeans();
                $return = $this->getOriginalTags($currRelBeans);
            }
        }
        return $return;
    }

}
