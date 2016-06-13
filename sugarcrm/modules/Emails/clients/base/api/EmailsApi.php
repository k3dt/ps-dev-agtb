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

require_once 'clients/base/api/ModuleApi.php';

class EmailsApi extends ModuleApi
{
    /**
     * Wildcard state value.
     *
     * @var string
     */
    const STATE_ANY = '*';

    /**
     * The valid transitions for an Emails record's state.
     *
     * @var array
     */
    private $validStateTransitions = array(
        'create' => array(
            array(
                'from' => self::STATE_ANY,
                'to' => array(
                    Email::EMAIL_STATE_READY,
                    Email::EMAIL_STATE_DRAFT,
                    Email::EMAIL_STATE_SCHEDULED,
                    Email::EMAIL_STATE_ARCHIVED,
                ),
            ),
        ),
        'update' => array(
            array(
                'from' => Email::EMAIL_STATE_DRAFT,
                'to' => array(
                    Email::EMAIL_STATE_DRAFT,
                    // Schedule the the draft to be sent.
                    Email::EMAIL_STATE_SCHEDULED,
                    // The draft is ready to be sent.
                    Email::EMAIL_STATE_READY,
                ),
            ),
            array(
                'from' => Email::EMAIL_STATE_SCHEDULED,
                'to' => array(
                    // Allows for the scheduled date to be modified.
                    Email::EMAIL_STATE_SCHEDULED,
                    // Cancel a scheduled email.
                    Email::EMAIL_STATE_ARCHIVED,
                    // Send the scheduled email immediately.
                    Email::EMAIL_STATE_READY,
                ),
            ),
            array(
                'from' => Email::EMAIL_STATE_ARCHIVED,
                'to' => array(
                    // Allows for changing teams or the assigned user, etc.
                    Email::EMAIL_STATE_ARCHIVED,
                ),
            ),
        ),
    );

    /**
     * Instance of the EmailRecipientsService used by the findRecipients API
     *
     * @var EmailRecipientsService
     */
    private $emailRecipientsService;

    /**
     * The fields `type` and `status` are disabled on create and update. The field `id` is disabled on create.
     *
     * All sender links are disabled on update, as the sender cannot be changed. For emails in the "Draft," "Ready," or
     * "Scheduled" state, the sender is always the current user. For emails in the "Archived" state, the sender is
     * immutable.
     *
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->disabledCreateFields = array_merge($this->disabledCreateFields, array('id', 'type', 'status'));
        $this->disabledUpdateFields = array_merge(
            $this->disabledUpdateFields,
            array('type', 'status'),
            VardefManager::getLinkFieldsForCollection('Emails', BeanFactory::getObjectName('Emails'), 'from')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function registerApiRest()
    {
        return array(
            'create' => array(
                'reqType' => 'POST',
                'path' => array('Emails'),
                'pathVars' => array('module'),
                'method' => 'createRecord',
                'shortHelp' => 'This method creates a new Emails record',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_record_post_help.html',
            ),
            'update' => array(
                'reqType' => 'PUT',
                'path' => array('Emails', '?'),
                'pathVars' => array('module', 'record'),
                'method' => 'updateRecord',
                'shortHelp' => 'This method updates an Emails record',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_record_put_help.html',
            ),
            'validateEmailAddresses' => array(
                'reqType' => 'POST',
                'path' => array('Emails', 'address', 'validate'),
                'pathVars' => array(''),
                'method' => 'validateEmailAddresses',
                'shortHelp' => 'Validate One Or More Email Address',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_address_validate_post_help.html',
            ),
            'findRecipients' => array(
                'reqType' => 'GET',
                'path' => array('Emails', 'recipients', 'find'),
                'pathVars' => array(''),
                'method' => 'findRecipients',
                'shortHelp' => 'Search For Email Recipients',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_recipients_find_get_help.html',
            ),
        );
    }

    /**
     * Prevents the creation of a bean when the state transition is invalid. Sends the email when the state is "Ready."
     *
     * The current user is always used as the sender for emails in the "Draft" or "Ready" states.
     *
     * {@inheritdoc}
     */
    public function createRecord(ServiceBase $api, $args)
    {
        $this->requireArgs($args, array('state'));

        if (!$this->isValidStateTransition('create', static::STATE_ANY, $args['state'])) {
            $message = "State transition to {$args['state']} is invalid for creating an email";
            throw new SugarApiExceptionInvalidParameter($message);
        }

        $isReady = false;

        if ($args['state'] === Email::EMAIL_STATE_READY) {
            $isReady = true;
            $args['state'] = Email::EMAIL_STATE_DRAFT;
        }

        if ($args['state'] === Email::EMAIL_STATE_DRAFT) {
            $fromLinks = VardefManager::getLinkFieldsForCollection(
                'Emails',
                BeanFactory::getObjectName('Emails'),
                'from'
            );

            // Drop any submitted senders.
            foreach ($fromLinks as $link) {
                unset($args[$link]);
            }

            // Add the current user as the sender.
            $args['users_from'] = array(
                'add' => array($GLOBALS['current_user']->id),
            );
        }

        $result = parent::createRecord($api, $args);

        if ($isReady) {
            $loadArgs = array('module' => 'Emails', 'record' => $result['id']);
            $email = $this->loadBean($api, $loadArgs, 'save', array('source' => 'module_api'));

            try {
                $this->sendEmail($email);
                $result = $this->formatBeanAfterSave($api, $args, $email);
            } catch (Exception $e) {
                $email->delete();
                throw $e;
            }
        }

        return $result;
    }

    /**
     * Prevents the update of a bean when the state transition is invalid. Sends the email when the state is "Ready."
     *
     * {@inheritdoc}
     */
    public function updateRecord(ServiceBase $api, $args)
    {
        $api->action = 'view';
        $this->requireArgs($args, array('module', 'record'));

        $bean = $this->loadBean($api, $args, 'save', array('source' => 'module_api'));
        $api->action = 'save';
        $isReady = false;

        if (isset($args['state'])) {
            if (!$this->isValidStateTransition('update', $bean->state, $args['state'])) {
                $message = "State transition from {$bean->state} to {$args['state']} is invalid for an email";
                throw new SugarApiExceptionInvalidParameter($message);
            }

            if ($args['state'] === Email::EMAIL_STATE_READY) {
                $isReady = true;
                unset($args['state']);
            }
        }

        $result = parent::updateRecord($api, $args);

        if ($isReady) {
            $email = $this->loadBean($api, $args, 'save', array('source' => 'module_api'));
            $this->sendEmail($email);
            $result = $this->formatBeanAfterSave($api, $args, $email);
        }

        return $result;
    }

    /**
     * Validates email addresses. The return value is an array of key-value pairs where the keys are the email
     * addresses and the values are booleans indicating whether or not the email address is valid.
     *
     * @param $api
     * @param $args
     * @return array
     * @throws SugarApiException
     */
    public function validateEmailAddresses($api, $args)
    {
        $validatedEmailAddresses = array();
        unset($args['__sugar_url']);
        if (!is_array($args)) {
            throw new SugarApiExceptionInvalidParameter('Invalid argument: cannot validate');
        }
        if (empty($args)) {
            throw new SugarApiExceptionMissingParameter('Missing email address(es) to validate');
        }
        $emailAddresses = $args;
        foreach ($emailAddresses as $emailAddress) {
            $validatedEmailAddresses[$emailAddress] = SugarEmailAddress::isValidEmail($emailAddress);
        }
        return $validatedEmailAddresses;
    }

    /**
     * Finds recipients that match the search term.
     *
     * Arguments:
     *    q           - search string
     *    module_list -  one of the keys from $modules
     *    order_by    -  columns to sort by (one or more of $sortableColumns) with direction
     *                   ex.: name:asc,id:desc (will sort by last_name ASC and then id DESC)
     *    offset      -  offset of first record to return
     *    max_num     -  maximum records to return
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function findRecipients($api, $args)
    {
        if (ini_get('max_execution_time') > 0 && ini_get('max_execution_time') < 300) {
            ini_set('max_execution_time', 300);
        }
        $term = (isset($args['q'])) ? trim($args['q']) : "";
        $offset = 0;
        $limit = (!empty($args['max_num'])) ? (int)$args['max_num'] : 20;
        $orderBy = array();

        if (!empty($args['offset'])) {
            if ($args['offset'] === 'end') {
                $offset = 'end';
            } else {
                $offset = (int)$args['offset'];
            }
        }

        $modules = array(
            'users' => 'users',
            'accounts' => 'accounts',
            'contacts' => 'contacts',
            'leads' => 'leads',
            'prospects' => 'prospects',
            'all' => 'LBL_DROPDOWN_LIST_ALL',
        );
        $module = $modules['all'];

        if (!empty($args['module_list'])) {
            $moduleList = strtolower($args['module_list']);

            if (array_key_exists($moduleList, $modules)) {
                $module = $modules[$moduleList];
            }
        }

        if (!empty($args['order_by'])) {
            $orderBys = explode(',', $args['order_by']);

            foreach ($orderBys as $sortBy) {
                $column = $sortBy;
                $direction = 'ASC';

                if (strpos($sortBy, ':')) {
                    // it has a :, it's specifying ASC / DESC
                    list($column, $direction) = explode(':', $sortBy);

                    if (strtolower($direction) == 'desc') {
                        $direction = 'DESC';
                    } else {
                        $direction = 'ASC';
                    }
                }

                // only add column once to the order-by clause
                if (empty($orderBy[$column])) {
                    $orderBy[$column] = $direction;
                }
            }
        }

        $records = array();
        $nextOffset = -1;

        if ($offset !== 'end') {
            $emailRecipientsService = $this->getEmailRecipientsService();
            $records = $emailRecipientsService->find($term, $module, $orderBy, $limit + 1, $offset);
            $totalRecords = count($records);
            if ($totalRecords > $limit) {
                // means there are more records in DB than limit specified
                $nextOffset = $offset + $limit;
                array_pop($records);
            }
        }

        return array(
            'next_offset' => $nextOffset,
            'records' => $records,
        );
    }

    /**
     * Retrieve an instance of the EmailRecipientsService
     *
     * @return EmailRecipientsService
     */
    protected function getEmailRecipientsService()
    {
        if (!($this->emailRecipientsService instanceof EmailRecipientsService)) {
            $this->emailRecipientsService = new EmailRecipientsService;
        }

        return $this->emailRecipientsService;
    }

    /**
     * Prevents existing Notes records from being linked as attachments.
     *
     * {@inheritdoc}
     */
    protected function linkRelatedRecords(
        ServiceBase $service,
        SugarBean $bean,
        array $ids,
        $securityTypeLocal = 'view',
        $securityTypeRemote = 'view'
    ) {
        unset($ids['attachments']);
        parent::linkRelatedRecords($service, $bean, $ids, $securityTypeLocal, $securityTypeRemote);
    }

    /**
     * The sender cannot be removed.
     *
     * {@inheritdoc}
     */
    protected function unlinkRelatedRecords(ServiceBase $service, SugarBean $bean, array $ids)
    {
        $links = VardefManager::getLinkFieldsForCollection($bean->module_dir, $bean->object_name, 'from');

        foreach ($links as $linkName) {
            unset($ids[$linkName]);
        }

        parent::unlinkRelatedRecords($service, $bean, $ids);
    }

    /**
     * Prepares attachments for being related. This includes patching the related record arguments for attachments to
     * contain the data necessary for creating the requisite Notes records, as well as placing the file.
     *
     * Creating records for the links from the from, to, cc, and bcc collection fields is not supported. Only existing
     * records can be added for these links, with the exception of email_addresses_from, email_addresses_to,
     * email_addresses_cc, and email_addresses_bcc.
     *
     * {@inheritdoc}
     */
    protected function createRelatedRecords(ServiceBase $service, SugarBean $bean, array $data)
    {
        $relate = array();
        $skip = array();
        $doNotSkip = array(
            'email_addresses_from',
            'email_addresses_to',
            'email_addresses_cc',
            'email_addresses_bcc',
        );

        foreach (array('from', 'to', 'cc', 'bcc') as $field) {
            $links = VardefManager::getLinkFieldsForCollection($bean->module_dir, $bean->object_name, $field);

            foreach ($links as $linkName) {
                if (!in_array($linkName, $doNotSkip)) {
                    $skip[] = $linkName;
                }
            }
        }

        foreach ($data as $linkName => $records) {
            switch ($linkName) {
                case 'attachments':
                    $relate[$linkName] = array();

                    foreach ($records as $record) {
                        $sourceFile = $this->getAttachmentSource($record);

                        if (!empty($sourceFile)) {
                            unset($record['_file']);
                            $destinationFile = $this->setupAttachmentNoteRecord($bean, $record);

                            $uploaded = (!empty($record['file_source']) &&
                                $record['file_source'] === Email::EMAIL_ATTACHMENT_UPLOADED);

                            if ($this->moveOrCopyAttachment($sourceFile, $destinationFile, $uploaded)) {
                                $relate[$linkName][] = $record;
                            }
                        }
                    }

                    break;
                case in_array($linkName, $skip):
                    // Creating records over these links is not supported.
                    break;
                default:
                    $relate[$linkName] = $records;
            }
        }

        parent::createRelatedRecords($service, $bean, $relate);
    }

    /**
     * Is the supplied state transition valid?
     *
     * @param string $operation
     * @param string $fromState
     * @param string $toState
     * @return boolean
     */
    protected function isValidStateTransition($operation, $fromState, $toState)
    {
        $transitions = $this->validStateTransitions[$operation];

        foreach ($transitions as $transition) {
            if (in_array($transition['from'], array(self::STATE_ANY, $fromState)) &&
                in_array($toState, $transition['to'])
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the qualified upload source file if the attachment record is valid or null.
     *
     * An attachment record is valid if an attachment file is specified and it exists in the upload or upload/tmp
     * directory.
     *
     * @param array $record
     * @return null|string
     */
    protected function getAttachmentSource(array $record)
    {
        if (!empty($record['_file'])) {
            $guid = preg_replace('/[^a-z0-9\-]/', '', $record['_file']);

            foreach (array('', 'tmp/') as $loc) {
                $source = "upload://{$loc}{$guid}";

                if (file_exists($source)) {
                    return $source;
                }
            }
        }

        return null;
    }

    /**
     * Specs out a new Notes object in the array format that {@link ModuleApi::createBean()} expects.
     *
     * @param SugarBean $bean
     * @param array $record The data for the Notes record. This method generates the ID.
     * @return string The location of the file for the subsequent move or copy.
     */
    protected function setupAttachmentNoteRecord(SugarBean $bean, array &$record)
    {
        $record['id'] = create_guid();
        $record['email_id'] = $bean->id;
        $record['email_type'] = $bean->module_dir;

        return "upload://{$record['id']}";
    }

    /**
     * Puts the file in the correct place to be used as an attachment.
     *
     * Moves the file if it was uploaded. Otherwise, to avoid duplication of read-only attachment files, this method
     * first tries to hard link the file and copies the file to the destination if hard linking fails.
     *
     * @param string $source
     * @param string $destination
     * @param bool $uploaded
     * @return bool
     */
    protected function moveOrCopyAttachment($source, $destination, $uploaded = false)
    {
        $source = UploadFile::realpath($source);
        $destination = UploadFile::realpath($destination);

        if ($uploaded) {
            $result = rename($source, $destination);
        } elseif (link($source, $destination)) {
            $result = true;
        } else {
            $result = copy($source, $destination);
        }

        if (!$result) {
            $GLOBALS['log']->error("Failed to link/copy file from {$source} to {$destination}");
        }

        return $result;
    }

    /**
     * Send the email.
     *
     * The system configuration is used if no configuration is specified on the email. An error will occur if the
     * application is not configured correctly to send email.
     *
     * @param SugarBean $email
     * @throws SugarApiExceptionError
     */
    protected function sendEmail(SugarBean $email)
    {
        try {
            $config = null;

            if (empty($email->outbound_email_id)) {
                $config = OutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS['current_user']);
                $email->outbound_email_id = $config->getConfigId();
            } else {
                $config = OutboundEmailConfigurationPeer::getMailConfigurationFromId(
                    $GLOBALS['current_user'],
                    $email->outbound_email_id
                );
            }

            if (empty($config)) {
                throw new MailerException(
                    'Could not find a configuration for sending email',
                    MailerException::InvalidConfiguration
                );
            }

            $email->sendEmail($config);
        } catch (MailerException $e) {
            //FIXME: Each MailerException code maps to a different SugarApiException.
            throw new SugarApiExceptionError($e->getUserFriendlyMessage());
        } catch (Exception $e) {
            throw new SugarApiExceptionError('Failed to send the email: ' . $e->getMessage());
        }
    }

    /**
     * EmailsApi needs an extended version of {@link RelateRecordApi} that is specific to Emails.
     *
     * @return EmailsRelateRecordApi
     */
    protected function getRelateRecordApi()
    {
        if (!$this->relateRecordApi) {
            require_once 'modules/Emails/clients/base/api/EmailsRelateRecordApi.php';
            $this->relateRecordApi = new EmailsRelateRecordApi();
        }

        return $this->relateRecordApi;
    }
}