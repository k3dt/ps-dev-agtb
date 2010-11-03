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

function CompanyDetailsDialog(div_id, text, x, y)
{
    this.div_id = div_id;
    this.text = text;
    this.width = 300;
    this.header = '';
    this.footer = '';
    this.x = x;
    this.y = y;
}

function header(header)
{
    this.header = header;
}

function footer(footer)
{
    this.footer = footer;
}

function display() {
 		dialog = new YAHOO.widget.SimpleDialog(this.div_id,  
	             { width: this.width, 
	               //fixedcenter: true, 
	               visible: true, 
	               draggable: true, 
	               close: true, 
	               text: this.text,
	               constraintoviewport: true,
	               x: this.x,
	               y: this.y
	        } );
	        
	    dialog.setHeader(this.header);
	    dialog.setBody(this.text);
	    dialog.setFooter(this.footer);
	    dialog.render(document.body);
	    dialog.show(); 
}

CompanyDetailsDialog.prototype.setHeader = header;
CompanyDetailsDialog.prototype.setFooter = footer;
CompanyDetailsDialog.prototype.display = display;
