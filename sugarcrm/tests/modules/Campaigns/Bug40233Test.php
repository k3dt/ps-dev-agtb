<?php 
require_once('modules/Contacts/Contact.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Campaigns/Campaign.php');
require_once('modules/CampaignLog/CampaignLog.php');
require_once('modules/Campaigns/utils.php');
require_once('modules/EmailMarketing/EmailMarketing.php');
require_once('include/ListView/ListView.php');
require_once('SugarTestContactUtilities.php');
require_once('SugarTestLeadUtilities.php');
require_once('tests/modules/Campaigns/Bug39665Test.php');

class Bug40233Test extends Bug39665Test
{
	
	public function setUp()
    {
    	$this->clear_database = true;
    	$this->remove_beans = true;
    	$this->saved_current_user = $GLOBALS['current_user'];
    	$user = new User();
    	$user->retrieve('1');
    	$GLOBALS['current_user'] = $user;
    	
    	$this->campaign = new Campaign();
    	$this->campaign->name = 'Bug39665Test ' . time();
    	$this->campaign->campaign_type = 'Email';
    	$this->campaign->status = 'Active';
    	$timeDate = new TimeDate();
    	$this->campaign->end_date = $timeDate->to_display_date(date('Y')+1 .'-01-01');
    	$this->campaign->assigned_id = $user->id;
    	$this->campaign->team_id = '1';
    	$this->campaign->team_set_id = '1';
    	$this->campaign->save();
    	
    	$this->emailmarketing = new EmailMarketing();
    	$this->emailmarketing->name = $this->campaign->name . ' Email1';
    	$this->emailmarketing->campaign_id = $this->campaign->id;
    	$this->emailmarketing->from_name = 'SugarCRM';
    	$this->emailmarketing->from_addr = 'from@exmaple.com';
    	$this->emailmarketing->reply_to_name = 'SugarCRM';
    	$this->emailmarketing->reply_to_addr = 'reply@exmaple.com';
    	$this->emailmarketing->status = 'active';
    	$this->emailmarketing->all_prospect_lists = 1;
    	$this->emailmarketing->date_start = $timeDate->to_display_date(date('Y')+1 .'-01-01') . ' 00:00:00';
    	
    	$this->emailmarketing2 = new EmailMarketing();
    	$this->emailmarketing2->name = $this->campaign->name . ' Email2';
    	$this->emailmarketing2->campaign_id = $this->campaign->id;
    	$this->emailmarketing2->from_name = 'SugarCRM';
    	$this->emailmarketing2->from_addr = 'do_not_reply@exmaple.com';
    	$this->emailmarketing2->reply_to_name = 'SugarCRM';
    	$this->emailmarketing2->reply_to_addr = 'reply@exmaple.com';    	
    	$this->emailmarketing2->status = 'active';
    	$this->emailmarketing2->all_prospect_lists = 1;
    	$this->emailmarketing2->date_start = $timeDate->to_display_date(date('Y')+1 .'-01-01') . ' 00:00:00';    	
    	
    	$query = 'SELECT id FROM inbound_email WHERE deleted=0';
    	$result = $GLOBALS['db']->query($query);
    	while($row = $GLOBALS['db']->fetchByAssoc($result))
    	{
			  $this->emailmarketing->inbound_email_id = $row['id'];
			  $this->emailmarketing2->inbound_email_id = $row['id'];
			  break;
		}    	
    	
		$query = 'SELECT id FROM email_templates WHERE deleted=0';
    	while($row = $GLOBALS['db']->fetchByAssoc($result))
    	{
			  $this->emailmarketing->template_id = $row['id'];
			  $this->emailmarketing2->template_id = $row['id'];
			  break;
		}    		
		
    	$this->emailmarketing->save();
    	$this->emailmarketing2->save();
    	
    	$this->campaign->load_relationship('prospectlists');
  		$this->prospectlist = new ProspectList();
        $this->prospectlist->name = $this->campaign->name.' Prospect List1';
        $this->prospectlist->assigned_user_id= $GLOBALS['current_user']->id;
        $this->prospectlist->list_type = "default";
        $this->prospectlist->save();
        $this->campaign->prospectlists->add($this->prospectlist->id);
        
    	$this->campaign->load_relationship('prospectlists');
  		$this->prospectlist2 = new ProspectList();
        $this->prospectlist2->name = $this->campaign->name.' Prospect List2';
        $this->prospectlist2->assigned_user_id= $GLOBALS['current_user']->id;
        $this->prospectlist2->list_type = "default";
        $this->prospectlist2->save();       
        $this->campaign->prospectlists->add($this->prospectlist2->id);         
        
        $campaign_log_states = array(0=>'viewed', 1=>'link', 2=>'invalid email', 3=>'send error', 4=>'removed', 5=>'blocked', 6=>'lead', 7=>'contact');
        
        for($i=0; $i < 10; $i++)
        {
        	$contact = SugarTestContactUtilities::createContact();
        	$contact->campaign_id = $this->campaign->id;
        	$contact->email2 = 'contact'. mt_rand() . '@sugar.com'; //Simulate a secondary email
        	$contact->save();
            $contact->load_relationship('prospect_lists');
	        $contact->prospect_lists->add($this->prospectlist->id);
	        $contact->prospect_lists->add($this->prospectlist2->id);
	        
	        $this->create_campaign_log($this->campaign, $contact, $this->emailmarketing, $this->prospectlist, 'targeted');
	        $this->create_campaign_log($this->campaign, $contact, $this->emailmarketing, $this->prospectlist, $campaign_log_states[mt_rand(0, 7)]);

	        $this->create_campaign_log($this->campaign, $contact, $this->emailmarketing2, $this->prospectlist, 'targeted');
	        $this->create_campaign_log($this->campaign, $contact, $this->emailmarketing2, $this->prospectlist, $campaign_log_states[mt_rand(0, 7)]);	        
        }

        for($i=0; $i < 10; $i++)
        {
        	$lead = SugarTestLeadUtilities::createLead();
        	$lead->campaign_id = $this->campaign->id;
        	$lead->email2 = 'lead2' . mt_rand() . '@sugar.com'; //Simulate a secondary email
        	$lead->save();
 			$lead->load_relationship('prospect_lists');
	        $lead->prospect_lists->add($this->prospectlist->id);
	        $lead->prospect_lists->add($this->prospectlist2->id);
	        
	        $this->create_campaign_log($this->campaign, $lead, $this->emailmarketing, $this->prospectlist, 'targeted');
	        $this->create_campaign_log($this->campaign, $lead, $this->emailmarketing, $this->prospectlist, $campaign_log_states[mt_rand(0, 7)]);
      
	        $this->create_campaign_log($this->campaign, $lead, $this->emailmarketing2, $this->prospectlist, 'targeted');
	        $this->create_campaign_log($this->campaign, $lead, $this->emailmarketing2, $this->prospectlist, $campaign_log_states[mt_rand(0, 7)]);        
       }        
  
       
        //But wait, there's more... now we email the friggin thing to place it in the queue


		if ($this->campaign->db->dbType=='oci8') {
		//BEGIN SUGARCRM flav=ent ONLY
			$current_date="TO_DATE('".gmdate($GLOBALS['timedate']->get_db_date_time_format())."','YYYY-MM-DD HH24:MI:SS')";
		//END SUGARCRM flav=ent ONLY
		} else {
			$current_date= "'".gmdate($GLOBALS['timedate']->get_db_date_time_format())."'";
		}
		
		//start scheduling now.....
		$emailmarketing_beans = array($this->emailmarketing, $this->emailmarketing2);
		foreach ($emailmarketing_beans as $marketing) {
		
			$mergedvalue=$GLOBALS['timedate']->merge_date_time($marketing->date_start,$marketing->time_start);
			
			if ($this->campaign->db->dbType=='oci8') {
		//BEGIN SUGARCRM flav=ent ONLY
					$send_date_time= "TO_DATE('".$GLOBALS['timedate']->to_db_date($mergedvalue)  . ' ' . $GLOBALS['timedate']->to_db_time($mergedvalue)."','YYYY-MM-DD HH24:MI:SS')";
		//END SUGARCRM flav=ent ONLY
			} else {
					$send_date_time= "'".$GLOBALS['timedate']->to_db_date($mergedvalue) . ' ' .$GLOBALS['timedate']->to_db_time($mergedvalue)."'";	
			}
		
			//find all prospect lists associated with this email marketing message.
			if ($marketing->all_prospect_lists == 1) {
				$query="SELECT prospect_lists.id prospect_list_id from prospect_lists ";
				$query.=" INNER JOIN prospect_list_campaigns plc ON plc.prospect_list_id = prospect_lists.id";
				$query.=" WHERE plc.campaign_id='{$this->campaign->id}'"; 
				$query.=" AND prospect_lists.deleted=0";
				$query.=" AND plc.deleted=0";
				$query.=" AND prospect_lists.list_type!='test' AND prospect_lists.list_type not like 'exempt%'";					
			} else {
				$query="select email_marketing_prospect_lists.* FROM email_marketing_prospect_lists ";
				$query.=" inner join prospect_lists on prospect_lists.id = email_marketing_prospect_lists.prospect_list_id";
				$query.=" WHERE prospect_lists.deleted=0 and email_marketing_id = '{$marketing->id}' and email_marketing_prospect_lists.deleted=0";
				$query.=" AND prospect_lists.list_type!='test' AND prospect_lists.list_type not like 'exempt%'";					
			}
			$result=$this->campaign->db->query($query);
			while (($row=$this->campaign->db->fetchByAssoc($result))!=null ) {
				$prospect_list_id=$row['prospect_list_id'];
				//delete all messages for the current campaign and current email marketing message.
				$delete_emailman_query="delete from emailman where campaign_id='{$this->campaign->id}' and marketing_id='{$marketing->id}' and list_id='{$prospect_list_id}'";
				$this->campaign->db->query($delete_emailman_query);
				
				$insert_query= "INSERT INTO emailman (date_entered, user_id, campaign_id, marketing_id,list_id, related_id, related_type, send_date_time";
				if ($this->campaign->db->dbType=='oci8') {
		//BEGIN SUGARCRM flav=ent ONLY
					$insert_query.=',id'; 		
		//END SUGARCRM flav=ent ONLY
				}
				$insert_query.=')'; 
				$insert_query.= " SELECT $current_date,'{$GLOBALS['current_user']->id}',plc.campaign_id,'{$marketing->id}',plp.prospect_list_id, plp.related_id, plp.related_type,{$send_date_time} ";  
				if ($this->campaign->db->dbType=='oci8') {
		//BEGIN SUGARCRM flav=ent ONLY
					$insert_query.=',EMAILMAN_ID_SEQ.nextval '; 		
		//END SUGARCRM flav=ent ONLY
				}
				$insert_query.= "FROM prospect_lists_prospects plp ";
				$insert_query.= "INNER JOIN prospect_list_campaigns plc ON plc.prospect_list_id = plp.prospect_list_id "; 
				$insert_query.= "WHERE plp.prospect_list_id = '{$prospect_list_id}' ";
				$insert_query.= "AND plp.deleted=0 ";
				$insert_query.= "AND plc.deleted=0 ";
				$insert_query.= "AND plc.campaign_id='{$this->campaign->id}'";
		
				if ($this->campaign->db->dbType=='oci8') {
		//BEGIN SUGARCRM flav=ent ONLY
					$insert_query.= " AND plp.id not in ( ";
					$insert_query.= " 		SELECT niplp.id from prospect_lists_prospects niplp ";
					$insert_query.= " 		INNER JOIN prospect_list_campaigns niplc ON niplc.id = niplp.prospect_list_id and niplc.campaign_id='{$this->campaign->id}' ";		
					$insert_query.= " 		INNER JOIN prospect_lists nipl ON nipl.id = niplp.prospect_list_id and nipl.list_type = 'exempt'  ";
					$insert_query.= " 		WHERE niplp.deleted=0 ";
					$insert_query.= " 		and nipl.deleted=0 ";
					$insert_query.= " 		and niplc.deleted=0 ";
					$insert_query.= " ) ";
		//END SUGARCRM flav=ent ONLY
				}
				$this->campaign->db->query($insert_query);
			}
		}
	}
	
	function tearDown()
	{
		parent::tearDown();
		if($this->clear_database)
		{			
			$sql = 'DELETE FROM emailman WHERE campaign_id = \'' . $this->campaign->id . '\'';
			$GLOBALS['db']->query($sql);	
		}		
	}
    
	function test_viewed_message()
	{
		$this->assertTrue(true);
    }
    
}
?>