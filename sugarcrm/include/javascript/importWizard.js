/**
 * Javascript file for Sugar
 *
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

SUGAR.importWizard= {};

SUGAR.importWizard = function() {
	return {
	
		renderDialog: function(importModuleVAR,actionVar,sourceVar){
			
			//show loading panel
			//SUGAR.importWizard.renderLoadingDialog();
			
			
			// create dialog container div
			var oBody = document.getElementsByTagName('BODY').item(0);
			if ( !document.getElementById( "importWizardDialog" )) {
					var importWizardDialogDiv = document.createElement("div");
					importWizardDialogDiv.id = "importWizardDialog";
					importWizardDialogDiv.style.display = "none";
					importWizardDialogDiv.className = "dashletPanelMenu wizard import";
					importWizardDialogDiv.innerHTML = '<div class="hd"><a href="javascript:void(0)" onClick="javascript:SUGAR.importWizard.closeDialog();"><div class="container-close">&nbsp;</div></a><div class="title" id="importWizardDialogTitle"></div></div><div class="bd"><div class="screen" id="importWizardDialogDiv"></div><div id="submitDiv"></div></div>';
					oBody.appendChild(importWizardDialogDiv);
			}
			
			
			
			YAHOO.util.Event.onContentReady("importWizardDialog", function() 
			{
				SUGAR.importWizard.dialog = new YAHOO.widget.Dialog("importWizardDialog", 
				{ width : "1100px",
				  height: "600px",
				  fixedcenter : true,
				  draggable:false,
				  visible : false, 
				  modal : true,
				  close:false
				 } );
	
				var oHead = document.getElementsByTagName('HEAD').item(0);
				// insert requred js files
				if ( !document.getElementById( "sugar_grp_yui_widgets" )) {
						var oScript= document.createElement("script");
						oScript.type = "text/javascript";
						oScript.id = "sugar_grp_yui_widgets";
						oScript.src="include/javascript/sugar_grp_yui_widgets.js";
						oHead.appendChild( oScript);
				}
				
				if ( !document.getElementById( "sugar_grp_overlib" )) {
						var oScriptOverLib= document.createElement("script");
						oScriptOverLib.type = "text/javascript";
						oScriptOverLib.id = "sugar_grp_overlib";
						oScriptOverLib.src="include/javascript/sugar_grp_overlib.js";
						oHead.appendChild( oScriptOverLib);
						
						var overDiv= document.createElement("div");
						overDiv.id = "overDiv";
						overDiv.style.position = "absolute"
						overDiv.style.visibility = "hidden";
						overDiv.style.zIndex = "1000";
						overDiv.style.maxWidth = "400px";
						var parentEl = oBody.firstChild;
						parentEl.parentNode.insertBefore(overDiv, parentEl);
	
				}
				
				
				var success = function(data) {		
					eval(data.responseText);
					importWizardDialogDiv = document.getElementById('importWizardDialogDiv');
					var submitDiv = document.getElementById('submitDiv');
					var importWizardDialogTitle = document.getElementById('importWizardDialogTitle');
					importWizardDialogDiv.innerHTML = response['html'];
					importWizardDialogTitle.innerHTML = response['title'];
					submitDiv.innerHTML = response['submitContent'];
					document.getElementById('importWizardDialog').style.display = '';												 
					SUGAR.importWizard.dialog.render();
					SUGAR.importWizard.dialog.show();
	
					eval(response['script']);
	
	
				}
				
				var cObj = YAHOO.util.Connect.asyncRequest('GET', 'index.php?module=Import&action='+actionVar+'&import_module='+importModuleVAR+'&source='+sourceVar, {success: success, failure: success});			
				return false;
			});
			
			
			//document.getElementById('importWizardDialog_c').style.display = 'none';			
		},
		closeDialog: function() {
			
				SUGAR.importWizard.dialog.hide();
				var importWizardDialogDiv = document.getElementById('importWizardDialogDiv');
				var submitDiv = document.getElementById('submitDiv');
				importWizardDialogDiv.innerHTML = "";
				submitDiv.innerHTML = "";
				SUGAR.importWizard.dialog.destroy();
		},
		
		renderLoadingDialog: function() {
			SUGAR.importWizard.loading = new YAHOO.widget.Panel("loading",
			{ width:"240px",
			  fixedcenter:true,
			  close:false,
			  draggable:false,
              constraintoviewport:false, 															  
			  modal:true,
			  visible:false,
			  effect:[{effect:YAHOO.widget.ContainerEffect.SLIDE, duration:0.5},
			  		  {effect:YAHOO.widget.ContainerEffect.FADE, duration:.5}]
			});
			SUGAR.importWizard.loading.setBody('<div id="loadingPage" align="center" style="vertical-align:middle;"><img src="' + SUGAR.themes.image_server + 'index.php?entryPoint=getImage&themeName='+SUGAR.themes.theme_name+'&imageName=img_loading.gif" align="absmiddle" /> <b>' + SUGAR.language.get('app_strings', 'LBL_LOADING_PAGE') +'</b></div>');
			SUGAR.importWizard.loading.render(document.body);		
			if (document.getElementById('loading_c'))
                document.getElementById('loading_c').style.display = 'none';
		},
		
			
			};
}();