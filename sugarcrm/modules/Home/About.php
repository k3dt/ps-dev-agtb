<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

//NOTE: Under the License referenced above, you are required to leave in all copyright statements in both
//the code and end-user application.

global $sugar_config, $mod_strings;
//BEGIN SUGARCRM flav=PRO ONLY
include('ThirdPartyCredits.php');
//END SUGARCRM flav=PRO ONLY
?>
<style type="text/css">
ul li {
list-style-type: square;
}
</style>
<script language="javascript" src="modules/Home/about.js"></script>
<span>
<div class="about" style="padding: 10px 15px 20px 15px;">
<p>
<!-- //BEGIN SUGARCRM flav=pro && flav!=ent ONLY -->
<IMG src="include/images/sugar_md.png" alt="SugarCRM" width="425" height="30" ondblclick='abouter.display();'>
<!-- //END SUGARCRM flav=pro && flav!=ent ONLY -->
<!-- //BEGIN SUGARCRM flav=sales ONLY
<IMG src="include/images/sugar_md_sales.png" alt="SugarCRM" width="425" height="30" ondblclick='abouter.display();'>
<!-- //END SUGARCRM flav=sales ONLY
<!-- //BEGIN SUGARCRM flav=dev ONLY
<IMG src="include/images/sugar_md_dev.png" alt="SugarCRM" width="425" height="30" ondblclick='abouter.display();'>
<!-- //END SUGARCRM flav=dev ONLY
<!-- //BEGIN SUGARCRM flav=com && lic=sub && flav!=dev ONLY
<IMG src="include/images/sugar_md_express.png" alt="SugarCRM" width="425" height="30" ondblclick='abouter.display();'>
<!-- //END SUGARCRM flav=com && lic=sub && flav!=dev ONLY		
<!-- //BEGIN SUGARCRM flav=com && lic!=sub ONLY
<IMG src="include/images/sugar_md_open.png" alt="SugarCRM" width="425" height="30" ondblclick='abouter.display();'>
<!--//END SUGARCRM flav=com && lic!=sub ONLY
<!-- //BEGIN SUGARCRM flav=dce ONLY
<IMG src="include/images/sugar_md_dce.png" alt="SugarCRM" width="425" height="30" ondblclick='abouter.display();'>
<!-- //END SUGARCRM flav=dce ONLY
<!-- //BEGIN SUGARCRM flav=ent && flav!=dev ONLY
<IMG src="include/images/sugar_md_ent.png" alt="SugarCRM" width="425" height="30" ondblclick='abouter.display();'>
<!-- //END SUGARCRM flav=ent && flav!=dev ONLY -->
<br>
<b><?php echo $mod_strings['LBL_VERSION']." ".$sugar_version." (".$mod_strings['LBL_BUILD']." ".$sugar_build.")";
    if( is_file( "custom_version.php" ) ){
        include( "custom_version.php" );
        print( "&nbsp;&nbsp;&nbsp;" . $custom_version );
    }
?>
<!--//BEGIN SUGARCRM flav=int ONLY -->
<br /><?php echo $sugar_codename; ?>
<!--//END SUGARCRM flav=int ONLY -->
</b></p>

<?php
//BEGIN SUGARCRM lic!=sub ONLY
echo "<P>Copyright ".$app_strings['LBL_SUGAR_COPYRIGHT']."</P>";
//END SUGARCRM lic!=sub ONLY
//BEGIN SUGARCRM lic=sub ONLY
echo "<P>Copyright ".$app_strings['LBL_SUGAR_COPYRIGHT_SUB']."</P>";
//END SUGARCRM lic=sub ONLY
//BEGIN SUGARCRM flav=com  && dep=os ONLY

// This version of viewLicenseText is for Community Edition only.
$viewLicenseText = $mod_strings['LBL_VIEWLICENSE_COM'];

//END SUGARCRM flav=com  && dep=os ONLY
 
//BEGIN SUGARCRM flav=int ONLY

$viewLicenseText = "";

//END SUGARCRM flav=int ONLY
//BEGIN SUGARCRM flav=com  && dep=os ONLY

echo $viewLicenseText;

//END SUGARCRM flav=com  && dep=os ONLY

//BEGIN SUGARCRM flav=com  && dep=os ONLY

$imgTagString = '<img style="margin-top: 2px" border="0" width="106" height="23" src="include/images/poweredby_sugarcrm.png" alt="Powered By SugarCRM">';

//END SUGARCRM flav=com  && dep=os ONLY
 
  //BEGIN SUGARCRM lic=sub ONLY

$imgTagString = '<P><A href="http://www.sugarcrm.com" target="_blank"> <img style="margin-top: 2px" border="0" width="106" height="23" src="include/images/poweredby_sugarcrm.png" alt="Powered By SugarCRM"></A>';

  //END SUGARCRM lic=sub ONLY

echo $imgTagString;
?>

<?php
//BEGIN SUGARCRM flav=com  && dep=os ONLY

$additionalTerm = $mod_strings['LBL_ADD_TERM_COM'];

//END SUGARCRM flav=com  && dep=os ONLY
 
//BEGIN SUGARCRM flav=int ONLY

$additionalTerm = "";

//END SUGARCRM flav=int ONLY
//BEGIN SUGARCRM flav=com  && dep=os ONLY

echo $additionalTerm;

//END SUGARCRM flav=com  && dep=os ONLY

?>

<P> SugarCRM &reg;,
<?php

//BEGIN SUGARCRM flav=com  && dep=os ONLY

// Product Name for Community Edition.
$theProductName = 'Sugar Community Edition';
  //END SUGARCRM flav=com  && dep=os ONLY
  //BEGIN SUGARCRM flav=exp ONLY
// Product Name for Express Edition.
$theProductName = 'Sugar Express Edition';

//END SUGARCRM flav=exp ONLY
 //BEGIN SUGARCRM flav=sales ONLY
// Product Name for Sales edition.
$theProductName = "Sugar Sales";
//END SUGARCRM flav=sales ONLY
//BEGIN SUGARCRM flav=pro ONLY

// Product Name for Professional edition.
$theProductName = "Sugar Professional";
//END SUGARCRM flav=pro ONLY
//BEGIN SUGARCRM flav=ent ONLY
// Product Name for Enterprise edition.
$theProductName = "Sugar Enterprise";
//END SUGARCRM flav=ent ONLY
echo $theProductName."&#8482; ".$mod_strings['LBL_AND']." Sugar&#8482; ".$mod_strings['LBL_ARE'];
?>
<a href="http://www.sugarcrm.com/crm/open-source/trademark-information.html"
	target="_blank">
	<?php echo $mod_strings['LBL_TRADEMARKS']."</a> ".$mod_strings['LBL_OF']; ?> SugarCRM Inc.</p>


<p ><table width="100%" border="0" cellspacing="0" cellpadding="0" class="contentBox">
<tr>
    <td  style="padding-right: 10px;" valign="top" rowspan="2" width="300" >

<object  classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="300" height="300" id="SugarPlanet" align="middle">
<param name="allowScriptAccess" value="sameDomain" />
<param name="movie" value="include/images/SugarPlanet.swf" />
<param name="quality" value="high" />
<param name="bgcolor" value="#ffffff" />
<param name="wmode" value="opaque" />
<embed  src="include/images/SugarPlanet.swf" wmode="opaque" quality="high" bgcolor="#ffffff" width="300" height="300" name="SugarPlanet" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>
<br>
<h3><?php echo $mod_strings['LBL_GET_SUGARCRM_RSS']; ?></h3>

<ul class="noBullet">
	<li class="noBullet" style="margin-bottom: 6px;"><a href="http://www.sugarcrm.com/crm/index2.php?no_html=1&stype=rss20&task=returnRSS&option=com_rss_feed_manager&channel=Corporate" target="_blank"><img src="include/images/rss_xml.gif" border="0" alt="XML" align="top"></a>&nbsp;<a href="http://www.sugarcrm.com/crm/index2.php?no_html=1&stype=rss20&task=returnRSS&option=com_rss_feed_manager&channel=Corporate" target="_blank"><?php echo $mod_strings['LBL_SUGARCRM_NEWS']; ?></a></li>
	<li class="noBullet" style="margin-bottom: 6px;"><a href="http://www.sugarcrm.com/forums/external.php?type=rss" target="_blank"><img src="include/images/rss_xml.gif" border="0" alt="XML" align="top"></a>&nbsp;<a href="http://www.sugarcrm.com/forums/external.php?type=rss" target="_blank"><?php echo $mod_strings['LBL_SUGARCRM_FORUMS']; ?></a></li>
	<li class="noBullet" style="margin-bottom: 6px;"><a href="http://www.sugarforge.org/export/rss_sfnews.php" target="_blank"><img src="include/images/rss_xml.gif" border="0" alt="XML" align="top"></a>&nbsp;<a href="http://www.sugarforge.org/export/rss_sfnews.php" target="_blank"><?php echo $mod_strings['LBL_SUGARFORGE_NEWS']; ?></a></li>
	<li class="noBullet" style="margin-bottom: 6px;"><a href="http://www.sugarcrm.com/crm/index2.php?no_html=1&stype=rss20&task=returnRSS&option=com_rss_feed_manager&channel=all" target="_blank"><img src="include/images/rss_xml.gif" border="0" alt="XML" align="top"></a>&nbsp;<a href="http://www.sugarcrm.com/crm/index2.php?no_html=1&stype=rss20&task=returnRSS&option=com_rss_feed_manager&channel=all" target="_blank"><?php echo $mod_strings['LBL_ALL_NEWS']; ?></a></li>
</ul>
<br>
<h3><?php echo $mod_strings['LBL_JOIN_SUGAR_COMMUNITY']; ?></h3>
<ul class="noBullet">
	<li class="noBullet" style="margin-bottom: 6px;"><a href="http://www.sugarforge.org/" target="_blank">SugarForge</a>: <?php echo $mod_strings['LBL_DETAILS_SUGARFORGE']; ?><br></li>
	<li class="noBullet" style="margin-bottom: 6px;"><a href="http://www.sugarexchange.com/" target="_blank">SugarExchange</a>: <?php echo $mod_strings['LBL_DETAILS_SUGAREXCHANGE']; ?><br></li>
	<li class="noBullet" style="margin-bottom: 6px;"><a href="http://www.sugarcrm.com/crm/university" target="_blank"><?php echo $mod_strings['LBL_TRAINING']; ?></a>: <?php echo $mod_strings['LBL_DETAILS_TRAINING']; ?><br></li>
	<li class="noBullet" style="margin-bottom: 6px;"><a href="http://www.sugarcrm.com/forums/" target="_blank"><?php echo $mod_strings['LBL_FORUMS']; ?></a>: <?php echo $mod_strings['LBL_DETAILS_FORUMS']; ?><br></li>
	<li class="noBullet" style="margin-bottom: 6px;"><a href="http://www.sugarcrm.com/wiki/" target="_blank"><?php echo $mod_strings['LBL_WIKI']; ?></a>: <?php echo $mod_strings['LBL_DETAILS_WIKI']; ?></li>
	<li class="noBullet" style="margin-bottom: 6px;"><a href="http://developer.sugarcrm.com/" target="_blank"><?php echo $mod_strings['LBL_DEVSITE']; ?></a>: <?php echo $mod_strings['LBL_DETAILS_DEVSITE']; ?></li>
</ul>

</td>

    <td colspan="2" valign="top" style="padding: 15px 10px 0px 10px;"><h3>SugarCRM Inc.</h3>

		10050 North Wolfe Road, Suite SW2-130, Cupertino, CA, 95014 USA,&nbsp;
		+1 (408) 454-6940,&nbsp;

<a href="http://www.sugarcrm.com" target="_blank">http://www.sugarcrm.com</a>

<iframe id='abouterdiv' border=0  width=500 style='overflow:hidden;display:none' frameborder="0" marginwidth="0" marginheight="0">
</iframe>
</td>
</tr>

<tr>
    <td valign="top" style="padding: 15px 10px 15px 10px;">
<p><B><a href="http://www.sugarforge.org/content/community/community-spotlight/contributions.php" target="_blank"><?php echo $mod_strings['LBL_LINK_CURRENT_CONTRIBUTORS']; ?></a></b></p>

<P>&nbsp;</p>
<P><h3><?php echo $mod_strings['LBL_SOURCE_CODE']; ?></h3></p>
<ul style="margin-bottom: 20px; padding-left: 0px;">
<LI><?php echo $mod_strings['LBL_SOURCE_SUGAR']; ?> (<A href="http://www.sugarcrm.com" target="_blank">http://www.sugarcrm.com</A>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_XTEMPLATE']; ?> (<A href="http://sourceforge.net/projects/xtpl" target="_blank">http://sourceforge.net/projects/xtpl</A>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_NUSOAP']; ?> (<a href="http://sourceforge.net/projects/nusoap/" target="_blank">http://sourceforge.net/projects/nusoap/</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_JSCALENDAR']; ?> (<a href="http://www.dynarch.com/mishoo/calendar.epl" target="_blank">http://www.dynarch.com/mishoo/calendar.epl</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_PHPPDF']; ?> (<a href="http://ros.co.nz/pdf/" target="_blank">http://ros.co.nz/pdf/</a>)
<LI><?php echo $mod_strings['LBL_SOURCE_HTTP_WEBDAV_SERVER']; ?> (<a href="http://pear.php.net/package/HTTP_WebDAV_Server" target="_blank">http://pear.php.net/package/HTTP_WebDAV_Server</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_JS_O_LAIT']; ?> (<a href="http://jsolait.net/" target="_blank">http://jsolait.net/</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_PCLZIP']; ?> (<a href="http://www.phpconcept.net/pclzip/" target="_blank">http://www.phpconcept.net/pclzip/</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_SMARTY']; ?> (<a href="http://www.smarty.net/" target="_blank">http://www.smarty.net/</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_OVERLIBMWS']; ?> (<a href="http://www.macridesweb.com/oltest/" target="_blank">http://www.macridesweb.com/oltest/</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_YAHOO_UI_LIB']; ?> (<a href="http://developer.yahoo.net/yui/" target="_blank">http://developer.yahoo.net/yui/</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_PHPMAILER']; ?> (<a href="http://sourceforge.net/projects/phpmailer/" target="_blank">http://sourceforge.net/projects/phpmailer/</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_CRYPT_BLOWFISH']; ?> (<a href="http://pear.php.net/package/Crypt_Blowfish/" target="_blank">http://pear.php.net/package/Crypt_Blowfish/</a>) </LI>
<LI><?php echo $mod_strings['LBL_SOURCE_HTML_SAFE']; ?> (<a href="http://pear.php.net/package/HTML_Safe/" target="_blank">http://pear.php.net/package/HTML_Safe/</a>) </LI>
<LI><?php echo $mod_strings['LBL_SOURCE_XML_HTMLSAX3']; ?> (<a href="http://pear.php.net/package/XML_HTMLSax3/" target="_blank">http://pear.php.net/package/XML_HTMLSax3/</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_YAHOO_UI_LIB_EXT']; ?> (<a href="http://www.jackslocum.com/blog/" target="_blank">http://www.jackslocum.com/blog/</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_JSMIN']; ?> (<a href="https://github.com/rgrove/jsmin-php/" target="_blank">https://github.com/rgrove/jsmin-php/</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_SWFOBJECT']; ?> (<a href="http://blog.deconcept.com/swfobject/" target="_blank">http://blog.deconcept.com/swfobject</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_TINYMCE']; ?> (<a href="http://wiki.moxiecode.com/index.php/TinyMCE:Index" target="_blank">http://wiki.moxiecode.com/index.php/TinyMCE:Index</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_TCPDF']; ?> (<a href="http://www.tcpdf.org/" target="_blank">http://www.tcpdf.org/</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_RECAPTCHA']; ?> (<a href="http://recaptcha.net/" target="_blank">http://recaptcha.net/</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_CSSMIN']; ?> (<a href="http://code.google.com/p/cssmin/" target="_blank">http://code.google.com/p/cssmin/</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_PHPSAML']; ?> (<a href="https://github.com/onelogin/php-saml" target="_blank">https://github.com/onelogin/php-saml/</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_ISCROLL']; ?> (<a href="http://cubiq.org/iscroll" target="_blank">http://cubiq.org/iscroll</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_JIT']; ?> (<a href="http://thejit.org/" target="_blank">http://thejit.org/</a>)</LI>
<LI><?php echo $mod_strings['LBL_SOURCE_FLASHCANVAS']; ?> (<a href="http://flashcanvas.net/" target="_blank">http://flashcanvas.net/</a>)</LI>

</ul>

//BEGIN SUGARCRM flav=PRO ONLY
<?php foreach($credits as $type => $details) {
	echo "<P><h3>". $type . "</h3></p>";
	echo "<ul style=\"margin-bottom: 20px; padding-left: 0px;\">";
		foreach($details as $key => $value) {
			echo "<li><b>".$value['name']."</b> by ".$value['author']." (<a href='http://{$value['website']}' target='_blank'>".$value['website']."</a>)</li>";
		}
	echo "</ul>";
}?>
//END SUGARCRM flav=PRO ONLY
	</td>

</tr>
</table>

</span>

<br>


</div>
