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

// $Id: JotPadDashletScript.tpl 28381 2007-10-18 21:40:33Z bsoufflet $

*}


{literal}<script>
if(typeof SugarCards == 'undefined') { // since the dashlet can be included multiple times a page, don't redefine these functions
	SugarCards = function() {
	    return {
	    	/**
	    	 * Called when the textarea is blurred
	    	 */
	        getImage: function(id, number) {
	        	ajaxStatus.showStatus('{/literal}{$lang.LBL_LOADING}{literal}'); // show that AJAX call is happening
	        	// what data to post to the dashlet
    	    	postData = 'to_pdf=1&module=Home&action=CallMethodDashlet&method=newImage&id=' + id;
    	    	if(typeof(number) != 'undefined'){postData += '&number=' + number}
				var cObj = YAHOO.util.Connect.asyncRequest('POST','index.php', 
								  {success: SugarCards.loadedImage, failure: SugarCards.loadedImage, argument:id}, postData);
	        },
		 
		    /**
	    	 * handle the response of the saveText method
	    	 */
	        loadedImage: function(o) {
	           	ajaxStatus.showStatus('{/literal}{$lang.LBL_LOADED}{literal}');
	           	var image = o.responseText.match(/(\d{2}).jpg/);
	           	image[1] -= 4;
	           	document.getElementById( o.argument + '_cardnum').value = image[1];
	    		document.getElementById('loading' + o.argument).src = o.responseText;
	    		
	           	window.setTimeout('ajaxStatus.hideStatus()', 2000);
	        },
	        changeSpashScreen: function(id, checked) {
	        	ajaxStatus.showStatus('{/literal}{$lang.LBL_SAVING}{literal}'); // show that AJAX call is happening
	        	// what data to post to the dashlet
    	    	postData = 'to_pdf=1&module=Home&action=CallMethodDashlet&method=changeSplashScreen&id=' + id + '&checked=' + checked;
				var cObj = YAHOO.util.Connect.asyncRequest('POST','index.php', 
								  {success: SugarCards.saved, failure: SugarCards.saved}, postData);
	        },
	        saved: function(o){
	        	ajaxStatus.flashStatus('{/literal}{$lang.LBL_SAVED}{literal}');
	        }
	        
	        
	        
	    };
	}();
}
</script>{/literal}