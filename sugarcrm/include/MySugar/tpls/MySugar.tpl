{*

/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: Home.tpl 24431 2007-07-18 22:40:32Z awu $

*}
{literal}
<style>
.menu{
	z-index:100;
}

.subDmenu{
	z-index:100;
}


li.active a img.deletePageImg {
   display: inline !important;
   margin-bottom: 2px;
}

div.moduleTitle {
height: 10px;
	}
</style>
{/literal}

<!-- begin includes for overlib -->
{sugar_getscript file="cache/include/javascript/sugar_grp_overlib.js"}
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000"></div>
<!-- end includes for overlib -->


{sugar_getscript file="cache/include/javascript/sugar_grp_yui_widgets.js"}
{sugar_getscript file='include/javascript/dashlets.js'}
<link rel='stylesheet' href='{sugar_getjspath file='include/ytree/TreeView/css/folders/tree.css'}'>
{$chartResources}
{$mySugarChartResources}

<script type="text/javascript">
//BEGIN SUGARCRM flav=pro ONLY
var numPages = {$numPages};
var loadedPages = new Array();
loadedPages[0] = '{$loadedPage}';
var numCols = {$numCols};
//END SUGARCRM flav=pro ONLY
var activePage = {$activePage};
var theme = '{$theme}';
current_user_id = '{$current_user}';
jsChartsArray = new Array();
var moduleName = '{$module}';
document.body.setAttribute("class", "yui-skin-sam");
{literal}
var mySugarLoader = new YAHOO.util.YUILoader({
	require : ["my_sugar", "sugar_charts"],
	onSuccess: function(){
		initMySugar();
		initmySugarCharts();
		//BEGIN SUGARCRM flav=pro || flav=sales ONLY
		{/literal}
		{counter assign=hiddenCounter start=0 print=false}
		{foreach from=$columns key=colNum item=data}
			{foreach from=$data.dashlets key=id item=dashlet}
				SUGAR.mySugar.attachToggleToolsetEvent('{$id}');
			{/foreach}
		{counter}
		{/foreach}
		{literal}
		//END SUGARCRM flav=pro || flav=sales ONLY
		SUGAR.mySugar.maxCount = 	{/literal}{$maxCount}{literal};
		SUGAR.mySugar.homepage_dd = new Array();
		var j = 0;

		{/literal}
		var dashletIds = {$dashletIds};

		{if !$lock_homepage}
			<!--//BEGIN SUGARCRM flav=pro ONLY -->
			SUGAR.mySugar.attachDashletCtrlEvent();
			<!--//END SUGARCRM flav=pro ONLY -->
			for(i in dashletIds) {ldelim}
				SUGAR.mySugar.homepage_dd[j] = new ygDDList('dashlet_' + dashletIds[i]);
				SUGAR.mySugar.homepage_dd[j].setHandleElId('dashlet_header_' + dashletIds[i]);
				SUGAR.mySugar.homepage_dd[j].onMouseDown = SUGAR.mySugar.onDrag;
				SUGAR.mySugar.homepage_dd[j].afterEndDrag = SUGAR.mySugar.onDrop;
				j++;
			{rdelim}
			{if $hiddenCounter > 0}
			for(var wp = 0; wp <= {$hiddenCounter}; wp++) {ldelim}
				SUGAR.mySugar.homepage_dd[j++] = new ygDDListBoundary('page_'+activePage+'_hidden' + wp);
			{rdelim}
			{/if}
			YAHOO.util.DDM.mode = 1;
		{/if}
		{literal}
		SUGAR.mySugar.renderDashletsDialog();
		//BEGIN SUGARCRM flav=pro ONLY
		SUGAR.mySugar.renderAddPageDialog();
		SUGAR.mySugar.renderChangeLayoutDialog();
		SUGAR.mySugar.renderLoadingDialog();
		//END SUGARCRM flav=pro ONLY
		SUGAR.mySugar.sugarCharts.loadSugarCharts(activePage);
		{/literal}
		//BEGIN SUGARCRM flav=pro ONLY
		{$activeTabJavascript}
		//END SUGARCRM flav=pro ONLY
		{literal}
	}
});
mySugarLoader.addModule({
	name :"my_sugar",
	type : "js",
	fullpath: {/literal}"{sugar_getjspath file='include/MySugar/javascript/MySugar.js'}"{literal},
	varName: "initMySugar",
	requires: []
});
mySugarLoader.addModule({
	name :"sugar_charts",
	type : "js",
	fullpath: {/literal}"{sugar_getjspath file="include/SugarCharts/Jit/js/mySugarCharts.js"}"{literal},
	varName: "initmySugarCharts",
	requires: []
});
mySugarLoader.insert();
{/literal}
</script>




<!--//BEGIN SUGARCRM flav=pro || flav=sales ONLY -->
{$form_header}
<table cellpadding="0" cellspacing="0" border="0" width="100%" id="tabListContainerTable">
<tr>
<td nowrap id="tabListContainerTD">
<div id="tabListContainer" class="yui-module yui-scroll">
	<div class="yui-hd">
		<span class="yui-scroll-controls">
			<a title="scroll left" class="yui-scrollup"><em>scroll left</em></a>
			<a title="scroll right" class="yui-scrolldown"><em>scroll right</em></a>
		</span>
	</div>

	<div class="yui-bd">
		<ul class="subpanelTablist" id="tabList">
		{foreach from=$pages key=pageNum item=pageData}
		<li id="pageNum_{$pageNum}" {if ($pageNum == $activePage)}class="active"{/if}>
		<a id="pageNum_{$pageNum}_anchor" class="{$pageData.tabClass}" href="javascript:SUGAR.mySugar.togglePages('{$pageNum}');">
			<span id="pageNum_{$pageNum}_input_span" style="display:none;">
			<input type="hidden" id="pageNum_{$pageNum}_name_hidden_input" value="{$pageData.pageTitle}"/>
			<input type="text" id="pageNum_{$pageNum}_name_input" value="{$pageData.pageTitle}" size="10" onblur="SUGAR.mySugar.savePageTitle('{$pageNum}',this.value);"/>
			</span>
			<span id="pageNum_{$pageNum}_link_span" class="tabText">
			<span id="pageNum_{$pageNum}_title_text" {if !$lock_homepage}ondblclick="SUGAR.mySugar.renamePage('{$pageNum}');"{/if}>{$pageData.pageTitle}</span></span>
			<img id="pageNum_{$pageNum}_delete_page_img" class="deletePageImg" style="display: none;" onclick="return SUGAR.mySugar.deletePage()" src='{sugar_getimagepath file="info-del.png"}' alt='{$lblLnkHelp}' border='0' align='absmiddle'>
		   </a>
	   </li>
	   {/foreach}
		</ul>
	</div>

</div>
<!--//BEGIN SUGARCRM flav=pro ONLY -->
	<div id="addPage">
		<a href='javascript:void(0)' id="add_page" onclick="return SUGAR.mySugar.showAddPageDialog();"><img src='{sugar_getimagepath file="info-add-page.png"}' alt='{$lblLnkHelp}' border='0' align='absmiddle'></a>
	</div>
<!--//END SUGARCRM flav=pro ONLY -->
</td>

<!--//BEGIN SUGARCRM flav=pro || flav=sales ONLY -->
{if !$lock_homepage}
<td nowrap id="dashletCtrlsTD">
	<div id="dashletCtrls">
			<a href="javascript:void(0)" id="add_dashlets" onclick="return SUGAR.mySugar.showDashletsDialog();" class='utilsLink'>
			<img src='{sugar_getimagepath file="info-add.png"}' alt='{$lblLnkHelp}' border='0' align='absmiddle'>
				{$mod.LBL_ADD_DASHLETS}
			</a>
			<!--//BEGIN SUGARCRM flav=pro ONLY -->
			<a href="javascript:void(0)" id="change_layout" onclick="return SUGAR.mySugar.showChangeLayoutDialog();" class='utilsLink'>
			<img src='{sugar_getimagepath file="info-layout.png"}' alt='{$lblLnkHelp}' border='0' align='absmiddle'>
				{$app.LBL_CHANGE_LAYOUT}
			</a>
			<!--//END SUGARCRM flav=pro ONLY -->
	</div>
</td>
{/if}
<!--//END SUGARCRM flav=pro || flav=sales ONLY -->
</tr>
</table>
<!--//END SUGARCRM flav=pro || flav=sales ONLY -->
<div class="clear"></div>
<div id="pageContainer" class="yui-skin-sam">
<div id="pageNum_{$activePage}_div">
<table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 5px;">
	{* //BEGIN SUGARCRM flav=pro ONLY*}
	{if $numCols > 1}
	{* //END SUGARCRM flav=pro ONLY*}
 	<tr>
 		{* //BEGIN SUGARCRM flav=pro ONLY*}
 		{if $numCols > 2}
 		{* //END SUGARCRM flav=pro ONLY*}
	 	<td>

		</td>

		<td rowspan="3">
				<img src='{sugar_getimagepath file='blank.gif'}' width='40' height='1' border='0'>
		</td>
 		{* //BEGIN SUGARCRM flav=pro ONLY*}
		{/if}
		{if $numCols > 1}
		<td>

		</td>
		<td rowspan="3">
				<img src='{sugar_getimagepath file='blank.gif'}' width='40' height='1' border='0'>
		</td>
		{/if}
		{* //END SUGARCRM flav=pro ONLY*}
		{* //BEGIN SUGARCRM flav=com ONLY*}
		<td align='right'>
			{if !$lock_homepage}<input id="add_dashlets" class="button" type="button" value="{$lblAddDashlets}" onclick="return SUGAR.mySugar.showDashletsDialog();"/>{/if}
		</td>
		{* //END SUGARCRM flav=com ONLY*}
	</tr>
	{* //BEGIN SUGARCRM flav=pro ONLY*}
	{/if}
	{* //END SUGARCRM flav=pro ONLY*}
	<tr>
		{counter assign=hiddenCounter start=0 print=false}
		{foreach from=$columns key=colNum item=data}
		<td valign='top' width='{$data.width}'>
			<ul class='noBullet' id='col_{$activePage}_{$colNum}'>
				<li id='page_{$activePage}_hidden{$hiddenCounter}b' style='height: 5px; margin-top: 12px\9;' class='noBullet'>&nbsp;&nbsp;&nbsp;</li>
		        {foreach from=$data.dashlets key=id item=dashlet}
				<li class='noBullet' id='dashlet_{$id}'>
					<div id='dashlet_entire_{$id}' class='dashletPanel'>
						{$dashlet.script}
					{$dashlet.displayHeader}
						{$dashlet.display}
                        {$dashlet.displayFooter}
                  </div>
				</li>
				{/foreach}
				<li id='page_{$activePage}_hidden{$hiddenCounter}' style='height: 5px' class='noBullet'>&nbsp;&nbsp;&nbsp;</li>
			</ul>
		</td>
		{counter}
		{/foreach}
	</tr>
</table>
	</div>

	{foreach from=$divPages key=divPageIndex item=divPageNum}
	<div id="pageNum_{$divPageNum}_div" style="display:none;">
	</div>
	{/foreach}

	{* //BEGIN SUGARCRM flav=pro ONLY*}
	<div id="addPageDialog" style="display:none;">
		<div class="hd">{$lblAddPage}</div>
		<div class="bd">
			<form method="POST" action="index.php?module=Home&action=DynamicAction&DynamicAction=addTab&to_pdf=1">
				<label>{$lblPageName}: </label><input type="textbox" name="pageName" /><br /><br />
				<label>{$lblNumberOfColumns}:</label>
				<table align="center" cellpadding="8">
					<tr>
						<td align="center"><img src="{sugar_getimagepath file='icon_Column_1.gif'}" border="0"/><br /><input type="radio" name="numColumns" value="1" /></td>
						<td align="center"><img src="{sugar_getimagepath file='icon_Column_2.gif'}" border="0"/><br /><input type="radio" name="numColumns" value="2" checked="yes" /></td>
						<td align="center"><img src="{sugar_getimagepath file='icon_Column_3.gif'}" border="0"/><br /><input type="radio" name="numColumns" value="3" /></td>
					</tr>
				</table>
			</form>
		</div>
	</div>
	{* //END SUGARCRM flav=pro ONLY*}

	{* //BEGIN SUGARCRM flav=pro ONLY*}
	<div id="changeLayoutDialog" style="display:none;">
		<div class="hd">{$lblChangeLayout}</div>
		<div class="bd">
			<label>{$lblNumberOfColumns}:</label>
			<br /><br />
			<table align="center" cellpadding="15">
				<tr>
					<td align="center"><a id="change_layout_1_column" href="javascript:SUGAR.mySugar.changePageLayout(1);"><img src="{sugar_getimagepath file='icon_Column_1.gif'}" border="0"/></a></td>
					<td align="center"><a id="change_layout_2_column" href="javascript:SUGAR.mySugar.changePageLayout(2);"><img src="{sugar_getimagepath file='icon_Column_2.gif'}" border="0"/></a></td>
					<td align="center"><a id="change_layout_3_column" href="javascript:SUGAR.mySugar.changePageLayout(3);"><img src="{sugar_getimagepath file='icon_Column_3.gif'}" border="0"/></a></td>
				</tr>
			</table>
		</div>
	</div>
	{* //END SUGARCRM flav=pro ONLY*}

	<div id="dashletsDialog" style="display:none;">
		<div class="hd" id="dashletsDialogHeader"><a href="javascript:void(0)" onClick="javascript:SUGAR.mySugar.closeDashletsDialog();">
			<div class="container-close">&nbsp;</div></a>{$lblAdd}
		</div>
		<div class="bd" id="dashletsList">
			<form></form>
		</div>

	</div>
				
	
</div>