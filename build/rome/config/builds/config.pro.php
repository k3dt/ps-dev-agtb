<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$config['builds']['pro']['flav'] = array('pro');
$config['builds']['pro']['languages']= array(
'bg_BG',
'cs_CZ',
'da_DK',
'de_DE',
'el_EL',
'es_ES',
'fr_FR',
'he_IL',
'hu_HU',
'it_it',
'lt_LT',
'ja_JP',
'ko_KR',
'lv_LV',
'nb_NO',
'nl_NL',
'pl_PL',
'pt_PT',
'ro_RO',
'ru_RU',
'sv_SE',
'tr_TR',
'zh_CN',
'pt_BR',
'ca_ES',
'en_UK',
'sr_RS',
'sk_SK',
'sq_AL',
);
$config['builds']['pro']['lic'] = array('sub');
$config['blackList']['pro'] = array(
'sugarcrm/build'=>1,
'sugarcrm/themes/Awesome80s'=>1,
'sugarcrm/themes/BoldMove'=>1,
'sugarcrm/themes/FinalFrontier'=>1,
'sugarcrm/themes/GoldenGate'=>1,
'sugarcrm/themes/Legacy'=>1,
'sugarcrm/themes/Links'=>1,
'sugarcrm/themes/Love'=>1,
'sugarcrm/themes/Paradise'=>1,
'sugarcrm/themes/Retro'=>1,
'sugarcrm/themes/RipCurl'=>1,
'sugarcrm/themes/RipCurlorg'=>1,
'sugarcrm/themes/Shred'=>1,
'sugarcrm/themes/Sugar2006'=>1,
'sugarcrm/themes/SugarClassic'=>1,
'sugarcrm/themes/SugarIE6'=>1,
'sugarcrm/themes/SugarLite'=>1,
'sugarcrm/themes/Sunset'=>1,
'sugarcrm/themes/TrailBlazers'=>1,
'sugarcrm/themes/VintageSugar'=>1,
'sugarcrm/themes/WhiteSands'=>1,
'sugarcrm/modules/CustomQueries'=>1,
'sugarcrm/modules/DataSets'=>1,
'sugarcrm/modules/ReportMaker'=>1,

'sugarcrm/include/externalAPI/LotusLiveDirect'=>1,
'sugarcrm/include/externalAPI/LotusLiveCastIron'=>1,

'sugarcrm/themes/default/images/gmail_logo.png'=>1,
'sugarcrm/themes/default/images/yahoomail_logo.png'=>1,
'sugarcrm/themes/default/images/exchange_logo.png'=>1,

'sugarcrm/modules/SugarFollowing'=>1,
'sugarcrm/themes/default/images/user_follow.png'=>1,    
'sugarcrm/themes/default/images/user_unfollow.png'=>1,

'sugarcrm/include/EditView/InlineEdit.css'=>1,
'sugarcrm/include/EditView/InlineEdit.js'=>1,
'sugarcrm/include/EditView/InlineEdit.php'=>1,
'sugarcrm/include/MVC/View/views/view.inlinefield.php'=>1,
'sugarcrm/include/MVC/View/views/view.inlinefieldsave.php'=>1,

'sugarcrm/modules/DCEActions'=>1,
'sugarcrm/modules/DCEClients'=>1,
'sugarcrm/modules/DCEClusters'=>1,
'sugarcrm/modules/DCEDataBases'=>1,
'sugarcrm/modules/DCEInstances'=>1,
'sugarcrm/modules/DCEReports'=>1,
'sugarcrm/modules/DCETemplates'=>1,
'sugarcrm/modules/Charts/Dashlets/DCEActionsByTypesDashlet'=>1,

'sugarcrm/themes/default/images/dce_settings.gif'=>1,
'sugarcrm/themes/default/images/DCEClusters.gif'=>1,
'sugarcrm/themes/default/images/DCEInstances.gif'=>1,
'sugarcrm/themes/default/images/DCElicensingReport.gif'=>1,
'sugarcrm/themes/default/images/DCETemplates.gif'=>1,
'sugarcrm/themes/default/images/DCEDataBases.gif'=>1,
'sugarcrm/themes/default/images/createDCEClusters.gif'=>1,
'sugarcrm/themes/default/images/createDCEInstances.gif'=>1,
'sugarcrm/themes/default/images/createDCETemplates.gif'=>1,
'sugarcrm/themes/default/images/createDCEDataBases.gif'=>1,
'sugarcrm/themes/default/images/icon_DCEActions_32.gif'=>1,
'sugarcrm/themes/default/images/icon_DCEDataBases_32.gif'=>1,
'sugarcrm/themes/default/images/icon_DCEInstances_32.gif'=>1,
'sugarcrm/themes/default/images/icon_DCEClusters_32.gif'=>1,
'sugarcrm/themes/default/images/icon_DCETemplates_32.gif'=>1,
'sugarcrm/themes/default/images/icon_DCEReports_32.gif'=>1,

'sugarcrm/modules/QueryBuilder'=>1,
'sugarcrm/modules/Queues'=>1,

'sugarcrm/include/images/sugarsales_lg.png'=>1,
'sugarcrm/include/images/sugarsales_lg_dce.png'=>1,
'sugarcrm/include/images/sugarsales_lg_ent.png'=>1,
'sugarcrm/include/images/sugarsales_lg_express.png'=>1,
'sugarcrm/include/images/sugarsales_lg_open.png'=>1,
'sugarcrm/include/images/sugarsales_lg_corp.png'=>1,
'sugarcrm/include/images/sugarsales_lg_ult.png'=>1,
'sugarcrm/include/images/sugar_md_dce.png'=>1,
'sugarcrm/include/images/sugar_md_dev.png'=>1,
'sugarcrm/include/images/sugar_md_ent.png'=>1,
'sugarcrm/include/images/sugar_md_express.png'=>1,
'sugarcrm/include/images/sugar_md_open.png'=>1,
'sugarcrm/include/images/sugar_md_sales.png'=>1,
'sugarcrm/include/images/sugar_md_corp.png'=>1,
'sugarcrm/include/images/sugar_md_ult.png'=>1,

'sugarcrm/portal2' =>1,
'sugarcrm/tests/portal2'=>1,

'sugarcrm/styleguide/styleguide/js/chosen.jquery.min.js'=>1,
'sugarcrm/styleguide/styleguide/js/jit-yc.js'=>1,
'sugarcrm/styleguide/styleguide/js/testcss.js'=>1,
'sugarcrm/styleguide/styleguide/js/jquery.numberformatter.min.js'=>1,
'sugarcrm/styleguide/styleguide/js/jquery.nouislider.min.js'=>1,
'sugarcrm/styleguide/styleguide/js/jquery.datatables.js'=>1,
'sugarcrm/styleguide/styleguide/js/wicked.js'=>1,
'sugarcrm/styleguide/styleguide/js/includes.js'=>1,
'sugarcrm/styleguide/styleguide/js/stal.js'=>1,
'sugarcrm/styleguide/styleguide/js/testharness.js'=>1,
'sugarcrm/styleguide/styleguide/js/jquery.jstree.js'=>1,
'sugarcrm/styleguide/styleguide/js/jquery.jeditable.js'=>1,
'sugarcrm/styleguide/styleguide/js/rgbcolor.js'=>1,
'sugarcrm/styleguide/styleguide/js/application.js'=>1,
'sugarcrm/styleguide/styleguide/js/jit.js'=>1,
'sugarcrm/styleguide/styleguide/js/sortable.js'=>1,
'sugarcrm/styleguide/styleguide/js/excanvas.js'=>1,
'sugarcrm/styleguide/styleguide/js/testharnessreport.js'=>1,
'sugarcrm/styleguide/styleguide/js/styleguide.js'=>1,
'sugarcrm/styleguide/styleguide/js/editable.js'=>1,
'sugarcrm/styleguide/styleguide/js/handlebars.js'=>1,
'sugarcrm/styleguide/styleguide/js/mustache.js'=>1,
'sugarcrm/styleguide/styleguide/js/mobile.js'=>1,
'sugarcrm/styleguide/styleguide/js/jquery.prettynumber.js'=>1,
'sugarcrm/styleguide/styleguide/js/droparea.js'=>1,
'sugarcrm/styleguide/styleguide/js/jquery.timepicker.js'=>1,
'sugarcrm/styleguide/styleguide/js/mobile-json.js'=>1,
'sugarcrm/styleguide/styleguide/js/testharness.css'=>1,
'sugarcrm/styleguide/styleguide/js/FlashCanvas/flashcanvas.js'=>1,
'sugarcrm/styleguide/styleguide/js/FlashCanvas/canvas2png.js'=>1,
'sugarcrm/styleguide/styleguide/js/FlashCanvas/proxy.php'=>1,
'sugarcrm/styleguide/styleguide/js/FlashCanvas/save.php'=>1,
'sugarcrm/styleguide/styleguide/js/README.md'=>1,
'sugarcrm/styleguide/styleguide/js/nvd3/nv.d3.min.js'=>1,
'sugarcrm/styleguide/styleguide/js/nvd3/src/utils.js'=>1,
'sugarcrm/styleguide/styleguide/js/nvd3/src/models/multiBar.js'=>1,
'sugarcrm/styleguide/styleguide/js/nvd3/src/models/scatter.js'=>1,
'sugarcrm/styleguide/styleguide/js/nvd3/src/models/axis.js'=>1,
'sugarcrm/styleguide/styleguide/js/nvd3/src/models/multiBarChart_sugar_v3.js'=>1,
'sugarcrm/styleguide/styleguide/js/nvd3/src/models/legend.js'=>1,
'sugarcrm/styleguide/styleguide/js/nvd3/src/models/line.js'=>1,
'sugarcrm/styleguide/styleguide/js/nvd3/lib/d3.v2.min.js'=>1,
'sugarcrm/styleguide/styleguide/js/zepto/assets.js'=>1,
'sugarcrm/styleguide/styleguide/js/zepto/fx.js'=>1,
'sugarcrm/styleguide/styleguide/js/zepto/form.js'=>1,
'sugarcrm/styleguide/styleguide/js/zepto/data.js'=>1,
'sugarcrm/styleguide/styleguide/js/zepto/touch.js'=>1,
'sugarcrm/styleguide/styleguide/js/zepto/fx_methods.js'=>1,
'sugarcrm/styleguide/styleguide/js/zepto/gesture.js'=>1,
'sugarcrm/styleguide/styleguide/js/zepto/event.js'=>1,
'sugarcrm/styleguide/styleguide/js/zepto/zepto.onpress.js'=>1,
'sugarcrm/styleguide/styleguide/js/zepto/zepto.js'=>1,
'sugarcrm/styleguide/styleguide/js/zepto/ajax.js'=>1,
'sugarcrm/styleguide/styleguide/js/zepto/detect.js'=>1,
'sugarcrm/styleguide/styleguide/js/zepto/polyfill.js'=>1,
'sugarcrm/styleguide/styleguide/js/google-code-prettify/prettify.js'=>1,
'sugarcrm/styleguide/styleguide/js/google-code-prettify/prettify.css'=>1,
'sugarcrm/styleguide/styleguide/datatable_ref.txt'=>1,
'sugarcrm/styleguide/styleguide/widgets.html'=>1,
'sugarcrm/styleguide/styleguide/portal'=>1,
'sugarcrm/styleguide/styleguide/components.html'=>1,
'sugarcrm/styleguide/styleguide/scaffolding.html'=>1,
'sugarcrm/styleguide/styleguide/base-css.html'=>1,
'sugarcrm/styleguide/styleguide/less.html'=>1,
'sugarcrm/styleguide/styleguide/css/nvd3/src/nv.d3.css'=>1,
'sugarcrm/styleguide/styleguide/css/generatedbootstrap.css'=>1,
'sugarcrm/styleguide/styleguide/css/jquery.ui.theme.css'=>1,
'sugarcrm/styleguide/styleguide/css/jquery.ui.theme.min.css'=>1,
'sugarcrm/styleguide/styleguide/css/styleguide.css'=>1,
'sugarcrm/styleguide/styleguide/css/jquery-ui-1.8.18.custom.css'=>1,
'sugarcrm/styleguide/styleguide/index.html'=>1,
);
$build = 'pro';
