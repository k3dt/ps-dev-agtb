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

{if !$nodata}
<script type="text/javascript">
	var css = new Array();
	var chartConfig = new Array();
	{foreach from=$css key=selector item=property}
	css["{$selector}"] = '{$property}';
	{/foreach}
	{foreach from=$config key=name item=value}
	chartConfig["{$name}"] = '{$value}';
	{/foreach}
	if (typeof SUGAR == 'undefined' || typeof SUGAR.mySugar == 'undefined') {ldelim}
		// no op
		loadCustomChartForReports();
	{rdelim} else {ldelim}
		SUGAR.mySugar.customCharts.addToCustomChartsArray('{$chartId}','{$filename}',css,chartConfig,activePage);
	{rdelim}
	
	function loadCustomChartForReports() {ldelim}

	
		loadCustomChart('{$chartId}','{$filename}',css,chartConfig);
	{rdelim}
</script>

<div class="chartContainer">
    <div id="{$chartId}" class="chartCanvas" style="width: {$width}; height: {$height}px;"></div>  
	<div id="legend{$chartId}" class="legend"></div>
</div>
<div class="clear"></div>
{else}

{$nodata}
{/if}