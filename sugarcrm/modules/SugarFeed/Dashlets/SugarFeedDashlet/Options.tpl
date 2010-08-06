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

// $Id: JotPadDashletOptions.tpl 24709 2007-07-27 01:50:04 +0000 (Fri, 27 Jul 2007) awu $

*}


<div style='width:100%'>
<form name='configure_{$id}' action="index.php" method="post" onSubmit='return SUGAR.dashlets.postForm("configure_{$id}", SUGAR.mySugar.uncoverPage);'>
<input type='hidden' name='id' value='{$id}'>
<input type='hidden' name='module' value='Home'>
<input type='hidden' name='action' value='ConfigureDashlet'>
<input type='hidden' name='to_pdf' value='true'>
<input type='hidden' name='configure' value='true'>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="edit view" align="center">
<tr>
    <td valign='top' nowrap class='dataLabel'>{$titleLBL}</td>
    <td valign='top' class='dataField'>
    	<input class="text" name="title" size='20' maxlength='80' value='{$title}'>
    </td>
</tr>
<tr>
    <td valign='top' nowrap class='dataLabel'>{$rowsLBL}</td>
    <td valign='top' class='dataField'>
    	<input class="text" name="rows" size='3' value='{$rows}'>
    </td>
</tr>
<tr>
    <td valign='top' nowrap class='dataLabel'>{$categoriesLBL}</td>
    <td valign='top' class='dataField'>
    	{html_options name='categories[]' options=$categories selected=$selectedCategories multiple=true size=6}
    </td>
</tr>
{foreach from=$user_filter_data key=index item=filter_data}
<tr id='div_{$filter_data.index}' style='display:block'>
    <td valign='top' nowrap class='dataLabel'>{$filter_data.label}</td>
    <td valign='top' class='dataField'>
  {if $filter_data.type == 'enum'}
	{capture name='msname'}{$filter_data.index}[]{/capture}
    {html_options name=$smarty.capture.msname options=$filter_data.options selected=$filter_data.value multiple=true size=6}
  {elseif $filter_data.type == 'int' or $filter_data.type == 'float' or $filter_data.type == 'double'}
  {else}
    {$filter_data.type}
  {/if}
    </td>
</tr>
{/foreach}
<!-- if revert back to hard coded
<tr id='div_opportunities_opportunity_type' style='display:block'>
    <td valign='top' nowrap class='dataLabel'>{$opportunity_typeLBL}</td>
    <td valign='top' class='dataField'>
    	{html_options name='opportunity_types[]' options=$opportunity_types selected=$selected_opportunity_types multiple=true size=4}
    </td>
</tr>
<tr id='div_opportunities_min_amount' style='display:block'>
    <td valign='top' nowrap class='dataLabel'>{$min_amountLBL}</td>
    <td valign='top' class='dataField'>
    	<input class="text" name="min_amount" size='8' value='{$min_amount}'></input>
    </td>
</tr>
-->
<tr>
    <td align="right" colspan="2">
        <input type='submit' class='button' value='{$saveLBL}'>
   	</td>
</tr>
</table>
</form>
</div>