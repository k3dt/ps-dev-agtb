<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2005 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*
 * This is the array that is used to determine how to group/concatenate js files together
 * The format is to define the location of the file to be concatenated as the array element key
 * and the location of the file to be created that holds the child files as the array element value.
 * So: $original_file_location => $Concatenated_file_location
 *
 * If you wish to add a grouping that contains a file that is part of another group already,
 * add a '.' after the .js in order to make the element key unique.  Make sure you pare the extension out
 *
 */

       $js_groupings = array(
            $summer_js = array(
                "sidecar/lib/jquery/jquery.min.js" => "summer/summer.min.js",
                "sidecar/lib/jquery/jquery.iframe.transport.js" => "summer/summer.min.js",
                "sidecar/lib/jquery-ui/js/jquery-ui-1.8.18.custom.min.js" => "summer/summer.min.js",
                "sidecar/lib/backbone/underscore.js" => "summer/summer.min.js",
                "sidecar/lib/backbone/backbone.js" => "summer/summer.min.js",
                "sidecar/lib/handlebars/handlebars-1.0.0.beta.6.js" => "summer/summer.min.js",
                "sidecar/lib/stash/stash.js" => "summer/summer.min.js",
                "sidecar/lib/async/async.js" => "summer/summer.min.js",
                "sidecar/lib/chosen/chosen.jquery.js" => "summer/summer.min.js",
                "sidecar/lib/sugar/sugar.searchahead.js" => "summer/summer.min.js",
                "sidecar/lib/sugar/sugar.timeago.js" => "summer/summer.min.js",
                "summer/lib/jquery/jquery.fancybox-1.3.4.js" => "summer/summer.min.js",
                "summer/lib/Crypto/Crypto.js" => "summer/summer.min.js",
                "summer/lib/TimelineJS/js/storyjs-embed.js" => "summer/summer.min.js",
                "summer/lib/fullcalendar/fullcalendar.js" => "summer/summer.min.js",
                "sidecar/lib/sugarapi/sugarapi.js" => "summer/summer.min.js",
                "sidecar/src/app.js" => "summer/summer.min.js",
                "sidecar/src/utils/date.js" => "summer/summer.min.js",
                "sidecar/src/utils/utils.js" => "summer/summer.min.js",
                "sidecar/src/utils/currency.js" => "summer/summer.min.js",
                "sidecar/src/core/cache.js" => "summer/summer.min.js",
                "sidecar/src/core/events.js" => "summer/summer.min.js",
                "sidecar/src/core/error.js" => "summer/summer.min.js",
                "summer/error.js" => "summer/summer.min.js",
                "summer/sugarAuthStore.js" => "summer/summer.min.js",
                "sidecar/src/view/template.js" => "summer/summer.min.js",
                "sidecar/src/core/context.js" => "summer/summer.min.js",
                "sidecar/src/core/controller.js" => "summer/summer.min.js",
                "sidecar/src/core/router.js" => "summer/summer.min.js",
                "sidecar/src/core/language.js" => "summer/summer.min.js",
                "sidecar/src/core/metadata-manager.js" => "summer/summer.min.js",
                "sidecar/src/core/acl.js" => "summer/summer.min.js",
                "sidecar/src/core/user.js" => "summer/summer.min.js",
                "summer/user.js" => "summer/summer.min.js",
                "sidecar/src/utils/logger.js" => "summer/summer.min.js",
                "summer/config.js" => "summer/summer.min.js",
                "sidecar/src/data/bean.js" => "summer/summer.min.js",
                "sidecar/src/data/bean-collection.js" => "summer/summer.min.js",
                "sidecar/src/data/data-manager.js" => "summer/summer.min.js",
                "sidecar/src/data/validation.js" => "summer/summer.min.js",
                "sidecar/src/view/hbt-helpers.js" => "summer/summer.min.js",
                "sidecar/src/view/view-manager.js" => "summer/summer.min.js",
                "sidecar/src/view/component.js" => "summer/summer.min.js",
                "sidecar/src/view/view.js" => "summer/summer.min.js",
                "sidecar/src/view/field.js" => "summer/summer.min.js",
                "sidecar/src/view/layout.js" => "summer/summer.min.js",
                "summer/views/alert-view.js" => "summer/summer.min.js",
                "sidecar/src/view/alert.js" => "summer/summer.min.js",
                "summer/summer.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-transition.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-collapse.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-scrollspy.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-tab.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-typeahead.js" => "summer/summer.min.js",
                "summer/lib/twitterbootstrap/js/jquery.dataTables.js" => "summer/summer.min.js",
                "summer/lib/twitterbootstrap/js/wicked.js" => "summer/summer.min.js",
                "styleguide/styleguide/js/jquery.jeditable.js" => "summer/summer.min.js",
                "summer/lib/twitterbootstrap/js/editable.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-button.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-tooltip.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-popover.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-dropdown.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-modal.js" => "summer/summer.min.js",
                "styleguide/assets/js/bootstrap-alert.js" => "summer/summer.min.js",
                "summer/summer-ui.js" => "summer/summer.min.js",
                "styleguide/styleguide/js/nvd3/lib/d3.v2.js" => "summer/summer.min.js",
                "styleguide/styleguide/js/nvd3/nv.d3.min.js" => "summer/summer.min.js",
                "styleguide/styleguide/js/nvd3/src/models/pie.js" => "summer/summer.min.js",
                "styleguide/styleguide/js/nvd3/src/models/pieChart.js" => "summer/summer.min.js",
                "styleguide/styleguide/js/nvd3/src/models/funnel.js" => "summer/summer.min.js",
                "styleguide/styleguide/js/nvd3/src/models/funnelChart.js" => "summer/summer.min.js",
            ),

            $summer_css = array(
                "sidecar/lib/chosen/chosen.css" => "summer/summer.min.css",
                "summer/lib/fullcalendar/fullcalendar.css" => "summer/summer.min.css",
                "sidecar/lib/jquery-ui/css/smoothness/jquery-ui-1.8.18.custom.css" => "summer/summer.min.css",
                "styleguide/styleguide/css/nvd3/src/nv.d3.css" => "summer/summer.min.css",
                "summer/lib/TimelineJS/css/timeline.css" => "summer/summer.min.css",
            ),

            $summer_splash_js = array(
                "sidecar/lib/jquery/jquery.min.js" => "summer/summer-splash.min.js",
                "summer/lib/twitterbootstrap/js/bootstrap-alert.js" => "summer/summer-splash.min.js",
                "sidecar/lib/handlebars/handlebars-1.0.0.beta.6.js" => "summer/summer-splash.min.js",
                "summer/splash/login.js" => "summer/summer-splash.min.js",
            ),

            $summer_splash_css = array(
                "sidecar/lib/chosen/chosen.css" => "summer/summer-splash.min.css",
                "summer/lib/twitterbootstrap/css/bootstrap.css" => "summer/summer-splash.min.css",
                "sidecar/lib/jquery-ui/css/smoothness/jquery-ui-1.8.18.custom.css" => "summer/summer-splash.min.css",
            ),

           $sugar_grp1 = array(
                //scripts loaded on first page
                'include/javascript/sugar_3.js'         => 'include/javascript/sugar_grp1.js',
                'include/javascript/ajaxUI.js'          => 'include/javascript/sugar_grp1.js',
                'include/javascript/cookie.js'          => 'include/javascript/sugar_grp1.js',
                'include/javascript/menu.js'            => 'include/javascript/sugar_grp1.js',
                'include/javascript/calendar.js'        => 'include/javascript/sugar_grp1.js',
                'include/javascript/quickCompose.js'    => 'include/javascript/sugar_grp1.js',
                'include/javascript/yui/build/yuiloader/yuiloader-min.js' => 'include/javascript/sugar_grp1.js',
                //HTML decode
                'include/javascript/phpjs/license.js' => 'include/javascript/sugar_grp1.js',
                'include/javascript/phpjs/get_html_translation_table.js' => 'include/javascript/sugar_grp1.js',
                'include/javascript/phpjs/html_entity_decode.js' => 'include/javascript/sugar_grp1.js',
                'include/javascript/phpjs/htmlentities.js' => 'include/javascript/sugar_grp1.js',
				//BEGIN SUGARCRM flav=pro ONLY
	            //Expression Engine
	            'include/Expressions/javascript/expressions.js'  => 'include/javascript/sugar_grp1.js',
	            'include/Expressions/javascript/dependency.js'   => 'include/javascript/sugar_grp1.js',
	            //END SUGARCRM flav=pro ONLY
               'include/EditView/Panels.js'   => 'include/javascript/sugar_grp1.js',
            ),
			//jquery libraries
			$sugar_grp_jquery = array(
			'include/javascript/jquery/jquery-min.js'              => 'include/javascript/sugar_grp1_jquery.js',
			'include/javascript/jquery/jquery-ui-min.js'          => 'include/javascript/sugar_grp1_jquery.js',
			'include/javascript/jquery/jquery.json-2.3.js'        => 'include/javascript/sugar_grp1_jquery.js',
			//bootstrap
            'include/javascript/jquery/bootstrap/bootstrap.min.js'              => 'include/javascript/sugar_grp1_jquery.js',
            //jquery for moddule menus
            'include/javascript/jquery/jquery.hoverIntent.js'            => 'include/javascript/sugar_grp1_jquery.js',
            'include/javascript/jquery/jquery.hoverscroll.js'            => 'include/javascript/sugar_grp1_jquery.js',
            'include/javascript/jquery/jquery.hotkeys.js'                => 'include/javascript/sugar_grp1_jquery.js',
            'include/javascript/jquery/jquery.superfish.js'              => 'include/javascript/sugar_grp1_jquery.js',
            'include/javascript/jquery/jquery.tipTip.js'              	 => 'include/javascript/sugar_grp1_jquery.js',
            'include/javascript/jquery/jquery.sugarMenu.js'              => 'include/javascript/sugar_grp1_jquery.js',
            'include/javascript/jquery/jquery.highLight.js'              => 'include/javascript/sugar_grp1_jquery.js',
            'include/javascript/jquery/jquery.showLoading.js'            => 'include/javascript/sugar_grp1_jquery.js',
            'include/javascript/jquery/jquery.dataTables.min.js'         => 'include/javascript/sugar_grp1_jquery.js',
            'include/javascript/jquery/jquery.dataTables.customSort.js'  => 'include/javascript/sugar_grp1_jquery.js',
            'include/javascript/jquery/jquery.jeditable.js'              => 'include/javascript/sugar_grp1_jquery.js',
            'include/javascript/jquery/jquery.chosen.min.js'             => 'include/javascript/sugar_grp1_jquery.js',
            'include/javascript/jquery/jquery.jstree.js'              	 => 'include/javascript/sugar_grp1_jquery.js',


			),
           $sugar_field_grp = array(
               'include/SugarFields/Fields/Collection/SugarFieldCollection.js' => 'include/javascript/sugar_field_grp.js',
               //BEGIN SUGARCRM flav=pro ONLY
               'include/SugarFields/Fields/Teamset/Teamset.js' => 'include/javascript/sugar_field_grp.js',
               //END SUGARCRM flav=pro ONLY
               'include/SugarFields/Fields/Datetimecombo/Datetimecombo.js' => 'include/javascript/sugar_field_grp.js',
           ),
            $sugar_grp1_yui = array(
			//YUI scripts loaded on first page
            'include/javascript/yui3/build/yui/yui-min.js'              => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui3/build/loader/loader-min.js'        => 'include/javascript/sugar_grp1_yui.js',
			'include/javascript/yui/build/yahoo/yahoo-min.js'           => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/dom/dom-min.js'               => 'include/javascript/sugar_grp1_yui.js',
			'include/javascript/yui/build/yahoo-dom-event/yahoo-dom-event.js'
			    => 'include/javascript/sugar_grp1_yui.js',
			'include/javascript/yui/build/event/event-min.js'           => 'include/javascript/sugar_grp1_yui.js',
			'include/javascript/yui/build/logger/logger-min.js'         => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/animation/animation-min.js'   => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/connection/connection-min.js' => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/dragdrop/dragdrop-min.js'     => 'include/javascript/sugar_grp1_yui.js',
            //Ensure we grad the SLIDETOP custom container animation
            'include/javascript/yui/build/container/container-min.js'   => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/element/element-min.js'       => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/tabview/tabview-min.js'       => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/selector/selector.js'     => 'include/javascript/sugar_grp1_yui.js',
            //This should probably be removed as it is not often used with the rest of YUI
            'include/javascript/yui/ygDDList.js'                        => 'include/javascript/sugar_grp1_yui.js',
            //YUI based quicksearch
            'include/javascript/yui/build/datasource/datasource-min.js' => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/json/json-min.js'             => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/autocomplete/autocomplete-min.js'=> 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/quicksearch.js'                         => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/menu/menu-min.js'             => 'include/javascript/sugar_grp1_yui.js',
			'include/javascript/sugar_connection_event_listener.js'     => 'include/javascript/sugar_grp1_yui.js',
			'include/javascript/yui/build/calendar/calendar.js'     => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/history/history.js'     => 'include/javascript/sugar_grp1_yui.js',
            'include/javascript/yui/build/resize/resize-min.js'     => 'include/javascript/sugar_grp1_yui.js',
            ),

            $sugar_grp_yui_widgets = array(
			//sugar_grp1_yui must be laoded before sugar_grp_yui_widgets
            'include/javascript/yui/build/datatable/datatable-min.js'   => 'include/javascript/sugar_grp_yui_widgets.js',
            'include/javascript/yui/build/treeview/treeview-min.js'     => 'include/javascript/sugar_grp_yui_widgets.js',
			'include/javascript/yui/build/button/button-min.js'         => 'include/javascript/sugar_grp_yui_widgets.js',
            'include/javascript/yui/build/calendar/calendar-min.js'     => 'include/javascript/sugar_grp_yui_widgets.js',
			'include/javascript/sugarwidgets/SugarYUIWidgets.js'        => 'include/javascript/sugar_grp_yui_widgets.js',
            // Include any Sugar overrides done to YUI libs for bugfixes
            'include/javascript/sugar_yui_overrides.js'   => 'include/javascript/sugar_grp_yui_widgets.js',
            ),

			$sugar_grp_yui_widgets_css = array(
				"include/javascript/yui/build/fonts/fonts-min.css" => 'include/javascript/sugar_grp_yui_widgets.css',
				"include/javascript/yui/build/treeview/assets/skins/sam/treeview.css"
					=> 'include/javascript/sugar_grp_yui_widgets.css',
				"include/javascript/yui/build/datatable/assets/skins/sam/datatable.css"
					=> 'include/javascript/sugar_grp_yui_widgets.css',
				"include/javascript/yui/build/container/assets/skins/sam/container.css"
					=> 'include/javascript/sugar_grp_yui_widgets.css',
                "include/javascript/yui/build/button/assets/skins/sam/button.css"
					=> 'include/javascript/sugar_grp_yui_widgets.css',
				"include/javascript/yui/build/calendar/assets/skins/sam/calendar.css"
					=> 'include/javascript/sugar_grp_yui_widgets.css',
			),

            $sugar_grp_yui2 = array(
               //YUI combination 2
               'include/javascript/yui/build/dragdrop/dragdrop-min.js'    => 'include/javascript/sugar_grp_yui2.js',
               'include/javascript/yui/build/container/container-min.js'  => 'include/javascript/sugar_grp_yui2.js',
            ),

            //Grouping for emails module.
            $sugar_grp_emails = array(
            'include/javascript/yui/ygDDList.js' => 'include/javascript/sugar_grp_emails.js',
            'include/SugarEmailAddress/SugarEmailAddress.js' => 'include/javascript/sugar_grp_emails.js',
            'include/SugarFields/Fields/Collection/SugarFieldCollection.js' => 'include/javascript/sugar_grp_emails.js',
            //BEGIN SUGARCRM flav=pro ONLY
            'include/SugarRouting/javascript/SugarRouting.js' => 'include/javascript/sugar_grp_emails.js',
            'include/SugarDependentDropdown/javascript/SugarDependentDropdown.js' => 'include/javascript/sugar_grp_emails.js',
            //END SUGARCRM flav=pro ONLY
            'modules/InboundEmail/InboundEmail.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/EmailUIShared.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/EmailUI.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/EmailUICompose.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/ajax.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/grid.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/init.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/complexLayout.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/composeEmailTemplate.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/displayOneEmailTemplate.js' => 'include/javascript/sugar_grp_emails.js',
            'modules/Emails/javascript/viewPrintable.js' => 'include/javascript/sugar_grp_emails.js',
            'include/javascript/quicksearch.js' => 'include/javascript/sugar_grp_emails.js',

            ),

            //Grouping for the quick compose functionality.
            $sugar_grp_quick_compose = array(
            'include/javascript/jsclass_base.js' => 'include/javascript/sugar_grp_quickcomp.js',
            'include/javascript/jsclass_async.js' => 'include/javascript/sugar_grp_quickcomp.js',
            'modules/Emails/javascript/vars.js' => 'include/javascript/sugar_grp_quickcomp.js',
            'include/SugarFields/Fields/Collection/SugarFieldCollection.js' => 'include/javascript/sugar_grp_quickcomp.js', //For team selection
            'modules/Emails/javascript/EmailUIShared.js' => 'include/javascript/sugar_grp_quickcomp.js',
            'modules/Emails/javascript/ajax.js' => 'include/javascript/sugar_grp_quickcomp.js',
            'modules/Emails/javascript/grid.js' => 'include/javascript/sugar_grp_quickcomp.js', //For address book
            'modules/Emails/javascript/EmailUICompose.js' => 'include/javascript/sugar_grp_quickcomp.js',
            'modules/Emails/javascript/composeEmailTemplate.js' => 'include/javascript/sugar_grp_quickcomp.js',
            'modules/Emails/javascript/complexLayout.js' => 'include/javascript/sugar_grp_quickcomp.js',
            ),

            $sugar_grp_jsolait = array(
                'include/javascript/jsclass_base.js'    => 'include/javascript/sugar_grp_jsolait.js',
                'include/javascript/jsclass_async.js'   => 'include/javascript/sugar_grp_jsolait.js',
                'modules/Meetings/jsclass_scheduler.js'   => 'include/javascript/sugar_grp_jsolait.js',
            ),

           $sugar_grp_sidecar = array(
               'sidecar/lib/jquery/jquery.placeholder.min.js'         => 'include/javascript/sugar_sidecar.min.js',
               'styleguide/assets/js/bootstrap-button.js'  => 'include/javascript/sugar_sidecar.min.js',
               'styleguide/assets/js/bootstrap-tooltip.js' => 'include/javascript/sugar_sidecar.min.js',
               'styleguide/assets/js/bootstrap-dropdown.js'=> 'include/javascript/sugar_sidecar.min.js',
               'styleguide/assets/js/bootstrap-popover.js' => 'include/javascript/sugar_sidecar.min.js',
               'styleguide/assets/js/bootstrap-modal.js'   => 'include/javascript/sugar_sidecar.min.js',
               'styleguide/assets/js/bootstrap-alert.js'   => 'include/javascript/sugar_sidecar.min.js',
               'portal2/error.js'               => 'include/javascript/sugar_sidecar.min.js',
               'portal2/views/alert-view.js'    => 'include/javascript/sugar_sidecar.min.js',
               'include/javascript/jquery/jquery.popoverext.js'           => 'include/javascript/sugar_sidecar.min.js',
               'include/javascript/jquery/jquery.effects.custombounce.js'           => 'include/javascript/sugar_sidecar.min.js',
           ),
           //BEGIN SUGARCRM flav=ent ONLY
            $sugar_grp_portal2 = array(
                'sidecar/lib/jquery/jquery.placeholder.min.js'         => 'portal2/portal.min.js',

                'styleguide/assets/js/bootstrap-button.js'  => 'portal2/portal.min.js',
                'styleguide/assets/js/bootstrap-tooltip.js' => 'portal2/portal.min.js',
                'styleguide/assets/js/bootstrap-dropdown.js'=> 'portal2/portal.min.js',
                'styleguide/assets/js/bootstrap-popover.js' => 'portal2/portal.min.js',
                'styleguide/assets/js/bootstrap-modal.js'   => 'portal2/portal.min.js',
                'styleguide/assets/js/bootstrap-alert.js'   => 'portal2/portal.min.js',
                'portal2/error.js'               => 'portal2/portal.min.js',
                'portal2/user.js'                => 'portal2/portal.min.js',
                'portal2/views/alert-view.js'    => 'portal2/portal.min.js',
                'portal2/portal.js'              => 'portal2/portal.min.js',
                'portal2/portal-ui.js'           => 'portal2/portal.min.js',
                'include/javascript/jquery/jquery.popoverext.js'           => 'portal2/portal.min.js',
                'include/javascript/jquery/jquery.effects.custombounce.js'           => 'portal2/portal.min.js',

            ),
           //END SUGARCRM flav=ent ONLY
        );

    /**
     * Check for custom additions to this code
     */
    if(file_exists("custom/application/Ext/JSGroupings/jsgroups.ext.php")) {
        require("custom/application/Ext/JSGroupings/jsgroups.ext.php");
    }
