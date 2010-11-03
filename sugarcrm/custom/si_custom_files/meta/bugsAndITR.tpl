{*

/**
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

 */
 
*}
Between {$START_DATE} and {$END_DATE}<BR><BR>
<table class="tabForm" border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
    <td>
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tbody>
 {section name=record loop=$meta}
    <tr>
    {foreach from=$meta[record] item=entry key=name}
       <td>{$entry}</td>
    {/foreach}
    </tr>
 {/section} 
    </tbody>
    </table>
    </td>
</tr>
</tbody>
</table>
