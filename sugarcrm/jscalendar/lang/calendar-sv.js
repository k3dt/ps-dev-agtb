/*

Modification information for LGPL compliance

r58364 - 2010-09-29 22:39:05 -0700 (Wed, 29 Sep 2010) - kjing - Author: Stanislav Malyshev <smalyshev@gmail.com>
    Fix languages in JS calendar

r56990 - 2010-06-16 13:05:36 -0700 (Wed, 16 Jun 2010) - kjing - snapshot "Mango" svn branch to a new one for GitHub sync

r56989 - 2010-06-16 13:01:33 -0700 (Wed, 16 Jun 2010) - kjing - defunt "Mango" svn dev branch before github cutover

r55980 - 2010-04-19 13:31:28 -0700 (Mon, 19 Apr 2010) - kjing - create Mango (6.1) based on windex

r51719 - 2009-10-22 10:18:00 -0700 (Thu, 22 Oct 2009) - mitani - Converted to Build 3  tags and updated the build system 

r51634 - 2009-10-19 13:32:22 -0700 (Mon, 19 Oct 2009) - mitani - Windex is the branch for Sugar Sales 1.0 development

r50375 - 2009-08-24 18:07:43 -0700 (Mon, 24 Aug 2009) - dwong - branch kobe2 from tokyo r50372

r48025 - 2009-06-03 21:56:58 -0700 (Wed, 03 Jun 2009) - weidong - 27345, change the langfiles to be utf-8 no BOM

r42807 - 2008-12-29 11:16:59 -0800 (Mon, 29 Dec 2008) - dwong - Branch from trunk/sugarcrm r42806 to branches/tokyo/sugarcrm

r550 - 2004-09-03 15:39:17 -0700 (Fri, 03 Sep 2004) - jostrow - Calendar feature


*/

﻿// ** I18N

// Calendar SV language (Swedish, svenska)
// Author: Mihai Bazon, <mishoo@infoiasi.ro>
// Translation team: <sv@li.org>
// Translator: Leonard Norrgård <leonard.norrgard@refactor.fi>
// Last translator: Leonard Norrgård <leonard.norrgard@refactor.fi>
// Encoding: UTF-8
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// full day names
Calendar._DN = new Array
("söndag",
 "måndag",
 "tisdag",
 "onsdag",
 "torsdag",
 "fredag",
 "lördag",
 "söndag");

// Please note that the following array of short day names (and the same goes
// for short month names, _SMN) isn't absolutely necessary.  We give it here
// for exemplification on how one can customize the short day names, but if
// they are simply the first N letters of the full name you can simply say:
//
//   Calendar._SDN_len = N; // short day name length
//   Calendar._SMN_len = N; // short month name length
//
// If N = 3 then this is not needed either since we assume a value of 3 if not
// present, to be compatible with translation files that were written before
// this feature.
Calendar._SDN_len = 2;
Calendar._SMN_len = 3;

// full month names
Calendar._MN = new Array
("januari",
 "februari",
 "mars",
 "april",
 "maj",
 "juni",
 "juli",
 "augusti",
 "september",
 "oktober",
 "november",
 "december");

// tooltips
if(Calendar._TT == undefined) Calendar._TT = {};
Calendar._TT["INFO"] = "Om kalendern";

Calendar._TT["ABOUT"] =
"DHTML Datum/tid-väljare\n" +
"(c) dynarch.com 2002-2003\n" + // don't translate this this ;-)
"För senaste version gå till: http://dynarch.com/mishoo/calendar.epl\n" +
"Distribueras under GNU LGPL.  Se http://gnu.org/licenses/lgpl.html för detaljer." +
"\n\n" +
"Val av datum:\n" +
"- Använd knapparna \xab, \xbb för att välja år\n" +
"- Använd knapparna " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " för att välja månad\n" +
"- Håll musknappen nedtryckt på någon av ovanstående knappar för snabbare val.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Val av tid:\n" +
"- Klicka på en del av tiden för att öka den delen\n" +
"- eller skift-klicka för att minska den\n" +
"- eller klicka och drag för snabbare val.";

Calendar._TT["PREV_YEAR"] = "Föregående år (håll för menu)";
Calendar._TT["PREV_MONTH"] = "Föregående månad (håll för menu)";
Calendar._TT["GO_TODAY"] = "Gå till dagens datum";
Calendar._TT["NEXT_MONTH"] = "Följande månad (håll för menu)";
Calendar._TT["NEXT_YEAR"] = "Följande år (håll för menu)";
Calendar._TT["SEL_DATE"] = "Välj datum";
Calendar._TT["DRAG_TO_MOVE"] = "Drag för att flytta";
Calendar._TT["PART_TODAY"] = " (idag)";
Calendar._TT["MON_FIRST"] = "Visa måndag först";
Calendar._TT["SUN_FIRST"] = "Visa söndag först";
Calendar._TT["CLOSE"] = "Stäng";
Calendar._TT["TODAY"] = "Idag";
Calendar._TT["TIME_PART"] = "(Skift-)klicka eller drag för att ändra tid";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%A %d %b %Y";

Calendar._TT["WK"] = "vecka";
