<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once 'modules/SchedulersJobs/SchedulersJob.php';
require_once 'tests/SugarTestUserUtilities.php';
require_once 'tests/SugarTestAccountUtilities.php';

class SchedulersJobsTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $jobs = array();

    public function setUp()
    {
        $this->db = DBManagerFactory::getInstance();
    }

    public function tearDown()
    {
        if(!empty($this->jobs)) {
            $jobs = implode("','", $this->jobs);
            $this->db->query("DELETE FROM job_queue WHERE id IN ('$jobs')");
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $ids = SugarTestAccountUtilities::getCreatedAccountIds();
        if(!empty($ids)) {
            SugarTestAccountUtilities::removeAllCreatedAccounts();
        }
    }

    protected function createJob($data)
    {
        $job = new TestSchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        foreach($data as $key => $val) {
            $job->$key = $val;
        }
        $job->save();
        $this->jobs[] = $job->id;
        return $job;
    }

    public function testJobCreate()
    {
        $job = $this->createJob(array("name" => "TestCreate"));
        $job->status = SchedulersJob::JOB_STATUS_DONE;
        $job->save();
        $this->assertNotEmpty($job->id);
        $job->retrieve($job->id);
        $this->assertEquals("TestCreate", $job->name, "Wrong name");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
    }

    public function testJobSuccess()
    {
        $job = $this->createJob(array("name" => "Test Success"));
        $job->succeedJob();

        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");

        $job = $this->createJob(array("name" => "Test Success 2"));
        $job->succeedJob("very good!");
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals("very good!\n", $job->message);
        $this->assertEmpty($job->failure_count, "Wrong failure count");
    }

    public function testJobFailure()
    {
        $job = $this->createJob(array("name" => "Test Fail"));
        $job->failJob();

        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals(1, $job->failure_count, "Wrong failure count");


        $job = $this->createJob(array("name" => "Test Fail 2"));
        $job->failJob("very bad!");
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals("very bad!\n", $job->message);
    }

    public function testJobPartial()
    {
        global $timedate;
        $now = $timedate->getNow();
        $job = $this->createJob(array("name" => "Test Later", "job_delay" => 57));
        $job->postponeJob();

        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_PARTIAL, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, "Wrong status");
        $date = $timedate->fromDb($job->execute_time_db);
        $this->assertEquals($now->ts+57, $date->ts);

        $job = $this->createJob(array("name" => "Test Later 2", "job_delay" => 42));
        $job->postponeJob("who knows?");
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_PARTIAL, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, "Wrong status");
        $this->assertEquals("who knows?\n", $job->message);
        // then succeed
        $job->succeedJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
    }

    static public function staticJobFunction1($job, $data = null)
    {
         global $testJobFunction1Args;
         $testJobFunction1Args = func_get_args();
         return $data != "failme";
    }

    static private function staticJobFunctionPrivate($job, $data = null)
    {
         global $testJobFunction1Args;
         $testJobFunction1Args = func_get_args();
         return $data != "failme";
    }

    static public function staticJobFunctionErrors($job, $data = null)
    {
        trigger_error("User Warning", E_USER_WARNING);
        $fp = fopen("/nosuchfile", "r"); // generate warning
         return $data != "failme";
    }

    static public function staticJobFunctionInternal($job, $data = null)
    {
        if($data == "errors") {
            trigger_error("User Warning", E_USER_WARNING);
        }
        if($data == "failme") {
            $job->failJob("Job Failed");
            return true;
        } else {
            $job->succeedJob("Job OK");
            return false;
        }
    }

    static public function staticJobFunctionAccount($job, $data = null)
    {
        SugarTestAccountUtilities::createAccount($data);
        return $data != "failme";
    }

    public function testJobRunFunc()
    {
        global $testJobFunction1Args;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $testJobFunction1Args = array();
        $job = $this->createJob(array("name" => "Test Func", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "function::testJobFunction1", "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals(1, count($testJobFunction1Args), "Wrong number of args to function");
        $this->assertInstanceOf(get_class($job), $testJobFunction1Args[0], "Wrong type of arg 1");
        $this->assertEquals($testJobFunction1Args[0]->id, $job->id, "Argument 1 ID doesn't match");
        // function with args
        $job = $this->createJob(array("name" => "Test Func 2", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "function::testJobFunction1",
        	"data" => "function data", "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals(2, count($testJobFunction1Args), "Wrong number of args to function");
        $this->assertEquals($testJobFunction1Args[1], "function data", "Argument 2 doesn't match");
        // function returns failure
        $job = $this->createJob(array("name" => "Test Func 2", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "function::testJobFunction1",
        	"data" => "failme", "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals(2, count($testJobFunction1Args), "Wrong number of args to function");
        // static function
        $testJobFunction1Args = array();
        $job = $this->createJob(array("name" => "Test Func 3", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "function::SchedulersJobsTest::staticJobFunction1", "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals(1, count($testJobFunction1Args), "Wrong number of args to function");
    }

    public function testJobRunBadFunc()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $testJobFunction1Args = array();
        // unknown function
        $job = $this->createJob(array("name" => "Test Bad Func", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "function::nosuchfunctionblahblah", "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertContains("nosuchfun", $job->message);
        // No user
        $job = $this->createJob(array("name" => "Test Bad Func 2", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "function::testJobFunction1"));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertContains("No User ID", $job->message);
        // Bad user ID
        $job = $this->createJob(array("name" => "Test Bad Func 3", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "function::testJobFunction1", "assigned_user_id" => "Unexisting User"));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertContains("Unexisting User", $job->message);
        // Private function
        $testJobFunction1Args = array();
        $job = $this->createJob(array("name" => "Test Bad Func 4", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "function::SchedulersJobsTest::staticJobFunctionPrivate", "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertContains("staticJobFunctionPrivate", $job->message);
        // Bad target type
        $testJobFunction1Args = array();
        $job = $this->createJob(array("name" => "Test Bad Func 5", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "whatever::SchedulersJobsTest::staticJobFunctionPrivate", "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertContains("whatever", $job->message);
    }

    public function testJobErrors()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $job = $this->createJob(array("name" => "Test Func Errors", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "function::SchedulersJobsTest::staticJobFunctionErrors", "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertContains("User Warning", $job->message);
        $this->assertContains("nosuchfile", $job->message);
        // failing
        $job = $this->createJob(array("name" => "Test Func Errors", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "function::SchedulersJobsTest::staticJobFunctionErrors", "data" => "failme", "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertContains("User Warning", $job->message);
        $this->assertContains("nosuchfile", $job->message);
    }

    public function testJobResolution()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $job = $this->createJob(array("name" => "Test Func Errors", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "function::SchedulersJobsTest::staticJobFunctionInternal","assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertContains("Job OK", $job->message);
        // failing
        $job = $this->createJob(array("name" => "Test Func Errors 2", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "function::SchedulersJobsTest::staticJobFunctionInternal", "data" => "failme", "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertContains("Job Failed", $job->message);
        // errors
        $job = $this->createJob(array("name" => "Test Func Errors", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "function::SchedulersJobsTest::staticJobFunctionInternal", "data" => "errors",
             "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertContains("Job OK", $job->message);
        $this->assertContains("User Warning", $job->message);
    }

    public function testJobClients()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $job = $this->createJob(array("name" => "Test Func Clients", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "function::SchedulersJobsTest::staticJobFunctionInternal", "client" => "UnitTests",
        	"assigned_user_id" => $GLOBALS['current_user']->id));
        $res = SchedulersJob::runJobId($job->id, "UnitTests");
        $job->retrieve($job->id);
        $this->assertTrue($res, "Bad result from runJobId");
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertContains("Job OK", $job->message);
        // wrong client
        $job = $this->createJob(array("name" => "Test Func Clients 2", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "function::SchedulersJobsTest::staticJobFunctionInternal", "client" => "UnitTests",
        	"assigned_user_id" => $GLOBALS['current_user']->id));
        $res = SchedulersJob::runJobId($job->id, "UnitTests2");
        $job->retrieve($job->id);
        $this->assertFalse($res, "Bad result from runJobId");
    }

    public function testJobURL()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $job = $this->createJob(array("name" => "Test Url", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "url::".$GLOBALS['sugar_config']['site_url']."/"));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        // Bad URL
        $job = $this->createJob(array("name" => "Test Url 2", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"target" => "url::".$GLOBALS['sugar_config']['site_url']."/blahblahblah"));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
    }

    public function testJobUsers()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $user1 = SugarTestUserUtilities::createAnonymousUser();
        $user2 = SugarTestUserUtilities::createAnonymousUser();
        $job = $this->createJob(array("name" => "Test User 1", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"assigned_user_id" => $user1->id, "target" => "function::SchedulersJobsTest::staticJobFunctionAccount", "data" => "useracc1"));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");

        $job = $this->createJob(array("name" => "Test User 2", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"assigned_user_id" => $user2->id, "target" => "function::SchedulersJobsTest::staticJobFunctionAccount", "data" => "useracc2"));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");

        $a1 = new Account();
        $a1->retrieve('useracc1');
        $this->assertEquals($user1->id, $a1->created_by, "Wrong creating user ID for account 1");
        $a2 = new Account();
        $a2->retrieve('useracc2');
        $this->assertEquals($user2->id, $a2->created_by, "Wrong creating user ID for account 2");
    }

    public function testJobRetries()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $user1 = SugarTestUserUtilities::createAnonymousUser();
        $user2 = SugarTestUserUtilities::createAnonymousUser();
        global $timedate;
        $now = $timedate->getNow();

        $job = $this->createJob(array("name" => "Test User 1", "status" => SchedulersJob::JOB_STATUS_RUNNING,
        	"assigned_user_id" => $user1->id, "target" => "function::nosuchfunction",
        	"requeue" => true, "retry_count" => 2, "job_delay" => 1, "min_interval" => 242));
        $job->runJob();
        $this->assertTrue($job->onFailureCalled, "onFailure wasn't called");
        $this->assertEmpty($job->onFinalFailureCalled, "onFinalFailure was called prematurely");
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, "Wrong status");
        $this->assertEquals(1, $job->failure_count, "Wrong failure count");
        $date = $timedate->fromDb($job->execute_time_db);
        $this->assertEquals($now->ts+242, $date->ts);
        // try again
        $job->onFailureCalled = null;
        $job->onFinalFailureCalled = null;
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->save();
        $job->runJob();
        $this->assertTrue($job->onFailureCalled, "onFailure wasn't called");
        $this->assertEmpty($job->onFinalFailureCalled, "onFinalFailure was called prematurely");
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, "Wrong status");
        $this->assertEquals(2, $job->failure_count, "Wrong failure count");
        // and try again
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->onFailureCalled = null;
        $job->onFinalFailureCalled = null;
        $job->save();
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals(3, $job->failure_count, "Wrong failure count");
        $this->assertTrue($job->onFinalFailureCalled, "onFinalFailure wasn't called");
        $this->assertEmpty($job->onFailureCalled, "onFailure was called");
    }

    public function testJobDelete()
    {
        $job = $this->createJob(array("name" => "TestCreate"));
        $job->status = SchedulersJob::JOB_STATUS_DONE;
        $job->save();
        $this->assertNotEmpty($job->id);
        $id = $job->id;
        $job->retrieve($id);
        $this->assertNotEmpty($job->id);
        $job->mark_deleted($id);
        $job = new SchedulersJob();
        $job->retrieve($job->id, true, false);
        $this->assertEmpty($job->id);
    }

}

class TestSchedulersJob extends SchedulersJob
{
    public $onFailureCalled;
    public $onFinalFailureCalled;

    public function onFailure()
    {
        $this->onFailureCalled = true;
    }

    public function onFinalFailure()
    {
        $this->onFinalFailureCalled = true;
    }
}

function testJobFunction1($job, $data = null)
{
     global $testJobFunction1Args;
     $testJobFunction1Args = func_get_args();
     return $data != "failme";
}
