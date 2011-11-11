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
{if $AUTHENTICATED}
{$DCSCRIPT}
<div id='dcmenutop'></div>
<div id='dcmenu' class='dcmenu dcmenuFloat'>

<!--
<div id="dcmenuSugarCube" {$NOTIFCLASS}>
  {$NOTIFICON}
  {$NOTIFCODE}
</div>
-->
	
			

  {include file="_headerModuleList.tpl" theme_template=true}
    


//BEGIN SUGARCRM flav=sales ONLY
{if !$ISADMIN}
//END SUGARCRM flav=sales ONLY
	<div id="dcmenuSearchDiv">
        <div id="sugar_spot_search_div">
            <input size=20 id='sugar_spot_search'  title='enter global search term'/>
            <div id="results">
                <section>
                    <div class="resultTitle">Top hit</div>
                    <ul>
                        <li><a href="">Anytime Air Support Inc - 1000 units </a></li>
                        <li><a href="">Orville Yuen</a></li>
                    </ul>
                <div class="clear"></div>
                </section>
                <section>
                    <div class="resultTitle">Favorites</div>
                    <ul>
                        <li><a href="">Nettie Tanguay</a></li>
                    </ul>
                <div class="clear"></div>
                </section>
                <section>
                    <div class="resultTitle">Contacts</div>
                    <ul>
                        <li><a href="">Dena Staggs</a></li>
                        <li><a href="">Saul Wash</a></li>
                        <li><a href="">Alexis Tylor</a></li>
                    </ul>
                <div class="clear"></div>
                </section>
                <a href="" class="resultAll">View all results</a>

                <section class="resultNull">
                    <h1>No results found</h1>
                    <a href="">Search again</a>
                </section>
            </div>
        </div>
	<div id="glblSearchBtn">{$ICONSEARCH}
    </div>
//BEGIN SUGARCRM flav=sales ONLY
{/if}
//END SUGARCRM flav=sales ONLY


	
	
</div>

    {if $AUTHENTICATED}
    {include file="_quickcreate.tpl" theme_template=true}
    {include file="_globalLinks.tpl" theme_template=true}
    
	{/if}
</div>
{/if}