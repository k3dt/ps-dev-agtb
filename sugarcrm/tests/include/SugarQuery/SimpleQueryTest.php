<?php

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'include/database/DBManagerFactory.php';
require_once 'modules/Contacts/Contact.php';
require_once 'tests/include/database/TestBean.php';
require_once 'include/SugarQuery/SugarQuery.php';

class SimpleQueryTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DBManager
     */
    private $db;
    protected $created = array();

    protected $backupGlobals = false;

    protected $contacts = array();
    protected $accounts = array();
    protected $notes = array();

    public static function setupBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function setUp()
    {
        if (empty($this->db)) {
            $this->db = DBManagerFactory::getInstance();
        }
    }

    public function tearDown()
    {
        if (!empty($this->contacts)) {
            $contactList = array();
            foreach ($this->contacts as $contact) {
                $contactList[] = $contact->id;
            }
            $this->db->query(
                "DELETE FROM contacts WHERE id IN ('" . implode(
                    "','",
                    $contactList
                ) . "')"
            );
            $this->db->query(
                "DELETE FROM contacts_cstm WHERE id_c IN ('" . implode(
                    "','",
                    $contactList
                ) . "')"
            );
        }
        if (!empty($this->accounts)) {
            $accountList = array();
            foreach ($this->accounts as $account) {
                $accountList[] = $account->id;
            }
            $this->db->query(
                "DELETE FROM accounts WHERE id IN ('" . implode(
                    "','",
                    $accountList
                ) . "')"
            );
            $this->db->query(
                "DELETE FROM accounts_cstm WHERE id_c IN ('" . implode(
                    "','",
                    $accountList
                ) . "')"
            );
        }

        if (!empty($this->notes)) {
            $notesList = array();
            foreach ($this->notes as $note) {
                $notesList[] = $note->id;
            }
            $this->db->query(
                "DELETE FROM notes WHERE id IN ('" . implode(
                    "','",
                    $notesList
                ) . "')"
            );
            $this->db->query(
                "DELETE FROM notes_cstm WHERE id_c IN ('" . implode(
                    "','",
                    $notesList
                ) . "')"
            );
        }
    }

    public function testSelect()
    {
        // create a new contact
        $contact = BeanFactory::getBean('Contacts');
        $contact->first_name = 'Test';
        $contact->last_name = 'McTester';
        $contact->save();
        $this->contacts[] = $contact;
        $id = $contact->id;
        // don't need the contact bean anymore, get rid of it
        unset($contact);
        // get the new contact

        $sq = new SugarQuery();
        $sq->select(array("first_name", "last_name"));
        $sq->from(BeanFactory::getBean('Contacts'));
        $sq->where()->equals("id", $id);

        $result = $sq->execute();
        // only 1 record
        $this->assertEquals(
            'Test',
            $result[0]['first_name'],
            'The First Name Did Not Match'
        );
        $this->assertEquals(
            'McTester',
            $result[0]['last_name'],
            'The Last Name Did Not Match'
        );

        // delete contact verify I can't get it
        $contact = BeanFactory::getBean('Contacts', $id);
        $contact->mark_deleted($id);
        unset($contact);

        $result = $sq->execute();
        $this->assertTrue(
            empty($result),
            "Result Set was not empty, it contained: " . print_r($result, true)
        );

        // get deleted items
        $sq = new SugarQuery();
        $sq->select(array("first_name", "last_name"));
        $sq->from(
            BeanFactory::getBean('Contacts'),
            array('add_deleted' => false)
        );
        $sq->where()->equals("id", $id);

        $result = $sq->execute();

        $this->assertEquals(
            'Test',
            $result[0]['first_name'],
            'The First Name Did Not Match, the deleted record did not return'
        );
        $this->assertEquals(
            'McTester',
            $result[0]['last_name'],
            'The Last Name Did Not Match, the deleted record did not return'
        );

    }

    public function testSelectWithJoin()
    {
        // create a new contact
        $contact = BeanFactory::getBean('Contacts');
        $contact->first_name = 'Test';
        $contact->last_name = 'McTester';
        $contact->save();
        $contact_id = $contact->id;


        $account = BeanFactory::getBean('Accounts');
        $account->name = 'Awesome';
        $account->save();

        $account->load_relationship('contacts');
        $account->contacts->add($contact->id);

        $this->accounts[] = $account;
        $this->contacts[] = $contact;

        // don't need the contact bean anymore, get rid of it
        unset($contact);
        unset($account);
        // get the new contact

        $sq = new SugarQuery();
        $sq->from(BeanFactory::getBean('Contacts'));
        $accounts = $sq->join('accounts')->joinName();
        $sq->select(
            array("first_name", "last_name", array("$accounts.name", 'aname'))
        );

        $sq->where()->equals("id", $contact_id);

        $result = $sq->execute();
        // only 1 record
        $this->assertEquals(
            'Test',
            $result[0]['first_name'],
            'The First Name Did Not Match'
        );
        $this->assertEquals(
            'McTester',
            $result[0]['last_name'],
            'The Last Name Did Not Match'
        );
        $this->assertEquals(
            'Awesome',
            $result[0]['aname'],
            'The Account Name Did Not Match'
        );
    }

    public function testSelectWithJoinToSelf()
    {
        $account = BeanFactory::getBean('Accounts');
        $account->name = 'Awesome';
        $account->save();
        $account_id = $account->id;

        $account2 = BeanFactory::getBean('Accounts');
        $account2->name = 'Awesome 2';
        $account2->save();

        $account->load_relationship('members');
        $account->members->add($account2->id);

        $this->accounts[] = $account;
        $this->accounts[] = $account2;

        // don't need the accounts beans anymore, get rid of'em
        unset($account2);
        unset($account);

        // lets try a query
        $sq = new SugarQuery();
        $sq->select(array(array("accounts.name", 'aname')));
        $sq->from(BeanFactory::getBean('Accounts'));
        $sq->join('members');
        $sq->where()->equals("id", $account_id);

        $result = $sq->execute();
        // only 1 record
        $this->assertEquals(
            'Awesome',
            $result[0]['aname'],
            "Account doesn't match"
        );
    }

    public function testSelectManyToMany()
    {
        global $current_user;

        $current_user->load_relationship('email_addresses');

        $email_address = BeanFactory::getBean('EmailAddresses');
        $email_address->email_address = 'test@test.com';
        $email_address->deleted = 0;
        $email_address->save();

        $current_user->email_addresses->add(
            $email_address->id,
            array('deleted' => 0)
        );

        // lets try a query
        $sq = new SugarQuery();
        $sq->select(array(array("users.first_name", 'fname')));
        $sq->from(BeanFactory::getBean('Users'));
        $email_addresses = $sq->join('email_addresses')->joinName();
        $sq->where()->starts("$email_addresses.email_address", "test");
        $sq->where()->equals('users.id', $current_user->id);


        $result = $sq->execute();
        $this->assertEquals(
            $current_user->first_name,
            $result[0]['fname'],
            "Wrong Email Address Result Returned"
        );
    }

    public function testOrderByDerivedField()
    {
        $contact = BeanFactory::getBean('Contacts');
        $contact->first_name = 'Super';
        $contact->last_name = 'Awesome-Sauce';
        $contact->save();
        $this->contacts[] = $contact;
        $contact = BeanFactory::getBean('Contacts');
        $contact->first_name = 'Super';
        $contact->last_name = 'Bad-Sauce';
        $contact->save();
        $this->contacts[] = $contact;

        $sq = new SugarQuery();

        $sq->from(BeanFactory::getBean('Contacts'));
        $sq->where()->in('contacts.last_name', array('Awesome-Sauce', 'Bad-Sauce'));
        $sq->orderBy('full_name', 'DESC');

        $sql = $sq->compileSql();

        $this->assertContains("ORDER BY contacts.{$contact->field_defs['full_name']['sort_on']} DESC", $sql);

        $result = $sq->execute();

        $expected = array('Bad-Sauce', 'Awesome-Sauce');

        $lastNameResult = array_reduce(
            $result,
            function ($out, $val) {
                if (isset($val['last_name'])) {
                    $out[] = $val['last_name'];
                }
                return $out;
            }
        );

        $this->assertEquals($expected, $lastNameResult);

        $sq = new SugarQuery();
        $sq->select(array('last_name'));
        $sq->from(BeanFactory::getBean('Contacts'));
        $sq->where()->in('contacts.last_name', array('Awesome-Sauce', 'Bad-Sauce'));
        $sq->orderBy('full_name', 'ASC');

        $result = $sq->execute();

        $expected = array(
            array(
                'last_name' => 'Awesome-Sauce',
                'first_name' => 'Super',
                'salutation' => null,
                'title' => null,
            ),
            array(
                'last_name' => 'Bad-Sauce',
                'first_name' => 'Super',
                'salutation' => null,
                'title' => null,
            ),
        );

        $this->assertEquals($expected, $result);
    }

    public function testSelectOneToManyWithRole()
    {
        $account = BeanFactory::getBean('Accounts');
        $account->name = 'Test Account';
        $account->save();
        $account_id = $account->id;

        // create a new note
        $note = BeanFactory::getBean('Notes');
        $note->name = 'Test Note';
        $note->parent_type = 'Accounts';
        $note->parent_id = $account_id;
        $note->save();
        $note_id = $note->id;

        $this->accounts[] = $account;
        $this->notes[] = $note;

        // don't need the contact bean anymore, get rid of it
        unset($note);
        unset($account);
        // get the new contact
        $sq = new SugarQuery();
        $sq->from(BeanFactory::getBean('Notes'));
        $accounts = $sq->join('accounts')->joinName();
        $sq->select(array("$accounts.name", "$accounts.id"));
        $sq->where()->equals("id", $note_id);

        $result = $sq->execute();
        // only 1 record

        $this->assertEquals(
            'Test Account',
            $result[0]['name'],
            'The Name Did Not Match'
        );
        $this->assertEquals($result[0]['id'], $account_id, 'The ID Did Not Match');
    }
}
