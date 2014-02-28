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

// $Id: coreTop.tpl 56676 2010-05-25 21:33:32Z dwheeler $

*}

<table width="100%">
<tr>
	<td class='mbLBL' width='30%' >{sugar_translate module="DynamicFields" label="COLUMN_TITLE_NAME"}:</td>
	<td>
	{if $hideLevel == 0}
		<input id="field_name_id" maxlength={if isset($package->name) && $package->name != "studio"}30{else}28{/if} type="text" name="name" value="{$vardef.name}"
		  onchange="
		document.getElementById('label_key_id').value = 'LBL_'+document.getElementById('field_name_id').value.toUpperCase();
		document.getElementById('label_value_id').value = document.getElementById('field_name_id').value.replace(/\_+/g,' ').replace(/^\s\s*/, '').replace(/\s\s*$/, '');
		document.getElementById('field_name_id').value = document.getElementById('field_name_id').value.toLowerCase();" />
	{else}
		<input id= "field_name_id" type="hidden" name="name" value="{$vardef.name}"
		  onchange="
		document.getElementById('label_key_id').value = 'LBL_'+document.getElementById('field_name_id').value.toUpperCase();
		document.getElementById('label_value_id').value = document.getElementById('field_name_id').value.replace(/\_+/g,' ').replace(/^\s\s*/, '').replace(/\s\s*$/, '');
		document.getElementById('field_name_id').value = document.getElementById('field_name_id').value.toLowerCase();"/>
		{$vardef.name}
	{/if}
        <script>
            {literal}
            addToValidateCallback("popup_form", "name", "callback", true, "{/literal}{sugar_translate module="DynamicFields" label="COLUMN_TITLE_NAME"}{literal}", (function(nameExceptions, existingFields) {
                return function(formName, fieldName, index) {
                    var el = document.forms[formName].elements[fieldName],
                        value = el.value, i, arrValue;

                    // will be already validated as required
                    if (value === "") {
                        return true;
                    }

                    if (!isDBName(value)) {
                        validate[formName][index][msgIndex] = "{/literal}{sugar_translate module="DynamicFields" label="ERR_FIELD_NAME_NON_DB_CHARS"}{literal}";
                        return false;
                    }

                    value = value.toUpperCase();

                    // check where field name is in the list of exceptions
                    for (i = 0; i < nameExceptions.length; i++) {
                        arrValue = nameExceptions[i];
                        if (arrValue == value) {
                            validate[formName][index][msgIndex] = "{/literal}{sugar_translate module="DynamicFields" label="ERR_RESERVED_FIELD_NAME"}{literal}";
                            return false;
                        }
                    }

                    {/literal}{if $hideLevel == 0}{literal}
                    // check where field name is in the list of existing fields
                    for (i = 0; i < existingFields.length; i++) {
                        arrValue = existingFields[i];
                        if (arrValue == value) {
                            validate[formName][index][msgIndex] = "{/literal}{sugar_translate module="DynamicFields" label="ERR_FIELD_NAME_ALREADY_EXISTS"}{literal}";
                            return false;
                        }
                    }
                    {/literal}{/if}{literal}

                    return true;
                }
            })({/literal}{$field_name_exceptions}, {$existing_field_names}));
        </script>
	</td>
</tr>
<tr>
	<td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_DISPLAY_LABEL"}:</td>
	<td>
		<input id="label_value_id" type="text" name="labelValue" value="{$lbl_value}">
	</td>
</tr>
<tr>
	<td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_LABEL"}:</td>
	<td>
    {if $hideLevel < 1}
	    <input id ="label_key_id" type="text" name="label" value="{$vardef.vname}">
	{else}
		<input type="text" readonly value="{$vardef.vname}" disabled=1>
		<input id ="label_key_id" type="hidden" name="label" value="{$vardef.vname}">
	{/if}
	</td>
</tr>
<tr>
	<td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_HELP_TEXT"}:</td><td>{if $hideLevel < 5 }<input type="text" name="help" value="{$vardef.help}">{else}<input type="hidden" name="help" value="{$vardef.help}">{$vardef.help}{/if}
	</td>
</tr>
<tr>
    <td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_COMMENT_TEXT"}:</td><td>{if $hideLevel < 5 }<input type="text" name="comments" value="{$vardef.comments}">{else}<input type="hidden" name="comment" value="{$vardef.comment}">{$vardef.comment}{/if}
    </td>
</tr>
