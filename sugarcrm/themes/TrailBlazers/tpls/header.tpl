{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
*}
{include file="_head.tpl" theme_template=true}
<body onMouseOut="closeMenus();">

<div id="HideMenu" class="leftList">
{if $AUTHENTICATED}
{include file="_leftFormHiddenLastViewed.tpl" theme_template=true}
{include file="_leftFormHiddenShortcuts.tpl" theme_template=true}
{/if}
</div>

<div id="header">
    
    <div style="display: block; z-index: 6; position: absolute; left: 0px; top: 0px; opacity: 1; width: 100%;">
    {include file="_colorFontPicker.tpl" theme_template=true}
    {include file="_globalLinks.tpl" theme_template=true}
    {include file="_welcome.tpl" theme_template=true}
    </div>
    <div id="firstdiv">
    </div>
    <div class="clear"></div>
    <br /><br />
    {include file="_companyLogo.tpl" theme_template=true}
    {include file="_headerSearch.tpl" theme_template=true}
    <div class="clear"></div>
    {if !$AUTHENTICATED}
    <br /><br />
    {/if}
    {if $USE_GROUP_TABS}
    {include file="_headerModuleListGroupTabs.tpl" theme_template=true}
    {else}
    {include file="_headerModuleList.tpl" theme_template=true}
    <div class="clear"></div>
    <div class="line"></div>
    {/if}
    {if $AUTHENTICATED}
    {include file="_headerLastViewed.tpl" theme_template=true}
    {include file="_headerShortcuts.tpl" theme_template=true}
    {/if}
</div>

<div id="main">
    {if $AUTHENTICATED}
    {include file="_leftFormHide.tpl" theme_template=true}
    <div id="leftColumn">
        {include file="_leftFormLastViewed.tpl" theme_template=true}
        {include file="_leftFormShortcuts.tpl" theme_template=true}
        {include file="_leftFormNewRecord.tpl" theme_template=true}
    </div>
    {/if}
    <div id="content" {if !$AUTHENTICATED}class="noLeftColumn" {/if}>
        <table><tr><td>
