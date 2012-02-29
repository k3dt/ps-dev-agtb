{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
*}
<link type="text/css" href="modules/Calendar/Cal.css" rel="stylesheet" />
<script type="text/javascript" src="modules/Calendar/Cal.js"></script>
<script type="text/javascript">

	{literal}
	YAHOO.util.Event.onDOMReady(function(){
		dom_loaded = true;
	});
	
	function check_cal_loaded(){
		return (typeof cal_loaded != 'undefined' && cal_loaded == true && typeof dom_loaded != 'undefined' && dom_loaded == true);
	}
	{/literal}
	
	SUGAR.util.doWhen(check_cal_loaded, function(){literal}{{/literal}
	
		CAL.view = "{$view}";
		CAL.style = "{$style}";
		CAL.t_step = {$t_step};
		CAL.current_user_id = "{$current_user_id}";	
		CAL.current_user_name = "{$current_user_name}";
		CAL.time_format = "{$time_format}";
		CAL.enable_repeat = "{$enable_repeat}";
		CAL.items_draggable = "{$items_draggable}";
		CAL.items_resizable = "{$items_resizable}";
		CAL.cells_per_day = {$cells_per_day};	
		CAL.current_params = {literal}{}{/literal};
		CAL.dashlet = "{$dashlet}";		
		CAL.grid_start_ts = {$grid_start_ts};
		CAL.scroll_slot = {$scroll_slot};
		CAL.basic.min_height = {$basic_min_height};

		CAL.lbl_create_new = "{$MOD.LBL_CREATE_NEW_RECORD}";
		CAL.lbl_edit = "{$MOD.LBL_EDIT_RECORD}";
		CAL.lbl_saving = "{$MOD.LBL_SAVING}";
		CAL.lbl_loading = "{$MOD.LBL_LOADING}";
		CAL.lbl_sending = "{$MOD.LBL_SENDING_INVITES}";
		CAL.lbl_confirm_remove = "{$MOD.LBL_CONFIRM_REMOVE}";
		CAL.lbl_confirm_remove_all_recurring = "{$MOD.LBL_CONFIRM_REMOVE_ALL_RECURRING}";
		
		CAL.lbl_error_saving = "{$MOD.LBL_ERROR_SAVING}";
		CAL.lbl_error_loading = "{$MOD.LBL_ERROR_LOADING}";
		CAL.lbl_repeat_limit_error = "{$MOD.LBL_REPEAT_LIMIT_ERROR}";
		
		{literal}
		var scrollable = CAL.get("cal-scrollable");
		if(scrollable){
			scrollable.scrollTop = (CAL.slot_height + 1) * CAL.scroll_slot - 1;
			if(CAL.view == "day")
				scrollable.scrollTop++;
		}
		{/literal}			

		{if $view == "shared"}
			{counter name="un" start=0 print=false assign="un"}
			{foreach name="shared" from=$shared_ids key=k item=member_id}				
				CAL.shared_users['{$member_id}'] = '{$un}';
				{counter name="un" print=false}
			{/foreach}
			CAL.shared_users_count = "{$shared_users_count}";
		{/if}
	
		CAL.field_list = new Array();
		CAL.field_disabled_list = new Array();			

		CAL.activity_colors = [];				
		{foreach name=colors from=$activity_colors key=module item=v}
			CAL.activity_colors['{$module}'] = [];
			CAL.activity_colors['{$module}']['border'] = '{$v.border}';
			CAL.activity_colors['{$module}']['body'] = '{$v.body}'
		{/foreach}

		CAL.act_types = [];
		CAL.act_types['Meetings'] = 'meeting';
		CAL.act_types['Calls'] = 'call';
		CAL.act_types['Tasks'] = 'task';

		{literal}

		if(CAL.items_draggable){			
			var target_slots = [];			
			var slots = CAL.query('#cal-grid div.slot');
			var cnt = 0;
			CAL.each(
				slots,
				function(i,v){					
					target_slots[i] = new YAHOO.util.DDTarget(slots[i].id,"cal");
					cnt++;
				}
			);
			slots = CAL.query('#cal-grid div.basic_slot');
			CAL.each(
				slots,
				function(i,v){
					target_slots[cnt + i] = new YAHOO.util.DDTarget(slots[i].id,"basic_cal");
				}
			);				
		}	
		
		var nodes = CAL.query("#cal-grid div.slot, #cal-grid div.basic_slot");
		CAL.each(nodes, function(i,v){
			YAHOO.util.Event.on(nodes[i],"mouseover",function(){
				if(CAL.records_openable && !CAL.disable_creating)
					this.style.backgroundColor = "#D1DCFF";							
				if(!this.childNodes.length)	
					this.setAttribute("title",this.getAttribute("time"));
			});
			YAHOO.util.Event.on(nodes[i],"mouseout",function(){
				this.style.backgroundColor = "";
				this.removeAttribute("title");
			});
			YAHOO.util.Event.on(nodes[i],"click",function(){
				if(!CAL.disable_creating){							
					CAL.dialog_create(this);
				}
			});
		});				
		
		CAL.init_edit_dialog({
			width: "{/literal}{$editview_width}{literal}",
			height: "{/literal}{$editview_height}{literal}"
		});
		
		YAHOO.util.Event.on(window, 'resize', function(){
			CAL.fit_grid();
			CAL.update_dd.fire();
		});		
				
		YAHOO.util.Event.on("btn-save","click",function(){																	
			if(!CAL.check_forms())
				return false;											
			CAL.dialog_save();	
		});
		
		YAHOO.util.Event.on("btn-send-invites","click",function(){																				
			if(!CAL.check_forms())
				return false;	
			CAL.get("send_invites").value = "1";							
			CAL.dialog_save();	
		});		
				
		YAHOO.util.Event.on("btn-delete","click",function(){
			if(CAL.get("record").value != "")
				if(confirm(CAL.lbl_confirm_remove))
					CAL.dialog_remove();
						
		});	
	
		YAHOO.util.Event.on("btn-cancel","click",function(){			
			CAL.editDialog.cancel();						
		}); 
		
		YAHOO.util.Event.on("btn-full-form","click",function(){			
			CAL.full_form();						
		}); 

		CAL.select_tab("cal-tab-1");

		YAHOO.util.Event.on(CAL.get("btn-cancel-settings"), 'click', function(){
			CAL.settingsDialog.cancel();	
		});
		
		YAHOO.util.Event.on(CAL.get("btn-save-settings"), 'click', function(){			
			CAL.get("form_settings").submit();
		});
		
		{/literal}
				
		var calendar_items = {$a_str};
					
		{literal}
		CAL.each(calendar_items, function(i,v){
			CAL.add_item_to_grid(calendar_items[i]);
		});
		{/literal}
		
		{if $view != "year"}
		CAL.arrange_advanced();
		CAL.basic.populate_grid();		
		CAL.fit_grid();
		CAL.update_dd.fire();
		{/if}
		
		cal_loaded = null;	
	});
</script>
			
<div id="cal-edit" style="display: none;">
	
	<div class="hd"><span id="title-cal-edit"></span></div>
	<div class="bd" id="edit-dialog-content">
		<div id="cal-tabs" class="yui-navset yui-navset-top yui-content" style="height: auto; padding: 0 2px;">
			<ul class="yui-nav">
				<li id="tab_general"><a tabname="cal-tab-1" id="cal-tab-1-link"><em>{$MOD.LBL_GENERAL_TAB}</em></a></li>
				<li id="tab_invitees"><a tabname="cal-tab-2" id="cal-tab-2-link"><em>{$MOD.LBL_PARTICIPANTS_TAB}</em></a></li>
				{if $enable_repeat}
				<li id="tab_repeat"><a tabname="cal-tab-3" id="cal-tab-3-link"><em>{$MOD.LBL_REPEAT_TAB}</em></a></li>
				{/if}
			</ul>
			<div id="cal-tab-1" class="yui-content">
				{include file=$form}
			</div>				
			<div id="cal-tab-2" class="yui-content">
				<div class="h3Row" id="scheduler"></div>
			</div>
			{if $enable_repeat}
			<div id="cal-tab-3" class="yui-content">
				{include file=$repeat}
			</div>
			{/if}
		</div>
	</div>	
	<div id="cal-edit-buttons" class="ft">
		<button id="btn-save" class="button" type="button">{$MOD.LBL_SAVE_BUTTON}</button>
		<button id="btn-cancel" class="button" type="button">{$MOD.LBL_CANCEL_BUTTON}</button>
		<button id="btn-delete" class="button" type="button">{$MOD.LBL_DELETE_BUTTON}</button>
		<button id="btn-send-invites" class="button" type="button">{$MOD.LBL_SEND_INVITES}</button>
		<button id="btn-full-form" class="button" type="button">{$APP.LBL_FULL_FORM_BUTTON_LABEL}</button>
	</div>
</div>

{if $settings}
{include file=$settings}
{/if}
	
<script type="text/javascript">	
{$GRjavascript}
</script>
	
<script type="text/javascript">	
{literal}
YAHOO.util.Event.onDOMReady(function(){	
	var schedulerLoader = new YAHOO.util.YUILoader({
		require : ["jsclass_scheduler"],
		onSuccess: function(){
			var root_div = document.getElementById('scheduler');
			var sugarContainer_instance = new SugarContainer(document.getElementById('scheduler'));
			sugarContainer_instance.start(SugarWidgetScheduler);
		}
	});
	schedulerLoader.addModule({
		name :"jsclass_scheduler",
		type : "js",
		fullpath: "modules/Meetings/jsclass_scheduler.js",
		varName: "global_rpcClient",
		requires: []
	});
	schedulerLoader.insert();
});	
{/literal}	
</script>
	
<script type="text/javascript" src="include/javascript/jsclass_base.js"></script>
<script type="text/javascript" src="include/javascript/jsclass_async.js"></script>	
	
<style type="text/css">
{literal}
	.schedulerDiv h3{
		display: none;
	}
	.schedulerDiv{
		width: auto !important;
	}
{/literal}
</style>	
{if $view == 'day'}
<style type="text/css">
{literal}
	#cal-grid div.col, #cal-grid div.left_col{
		border-top: 1px solid silver;	
	}
{/literal}
</style>
{/if}

<div id="cal-width-helper" style="width: auto;"></div>

