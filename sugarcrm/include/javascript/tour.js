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

// $Id: dashlets.js 24711 2007-07-27 01:51:57Z awu $
SUGAR.tour = function() {
var tourModal;
	return {
		init: function(params) {
			var modals = params.modals;
            var modalArray = new Array();
			tourModal = $('<div id="'+params.id+'" class="modal"></div>').modal({backdrop: false});
			var tourIdSel = "#"+params.id;
			$.ajax({
				url: params.modalUrl,
				success: function(data){
					$(tourIdSel).html(data);
							
					$(tourIdSel+'Start a.btn.btn-primary').on("click",function(e){
						$(tourIdSel+'Start').css("display","none");
						$(tourIdSel+'End').css("display","block");
						tourModal.modal("hide");
						modalArray[0].modal('show');
                        $(modals[0].target).popoverext('show');
					});
					$(tourIdSel+'Start a.btn').not('.btn-primary').on("click",function(e){
						$(tourIdSel+'Start').css("display","none");
						$(tourIdSel+'End').css("display","block");
					});
					$(tourIdSel+' a.close').on("click",function(e){
						tourModal.modal("hide");
						SUGAR.tour.saveUserPref(params.prefUrl);
						params.onTourFinish();
					});
					$(tourIdSel+'End a.btn.btn-primary').on("click",function(e){
						tourModal.modal("hide");
						SUGAR.tour.saveUserPref(params.prefUrl);
						params.onTourFinish();
					});

                    $('<div style="position: absolute;" id="tourArrow">arrow</div>');


					for(var i=0; i<modals.length; i++) {
                        var modalId =  modals[i].target.replace("#","")+"_modal";
                        modalArray[i] = $('<div id="'+modalId+'" class="modal"></div>').modal({backdrop: false});
//                        modalArray[i].modal('show');
                        var modalContent = "<div class=\"modal-header\"><a class=\"close\" data-dismiss=\"modal\">×</a><h3>"+modals[i].title+"</h3></div>";

                        modalContent += "<div class=\"modal-body\">"+modals[i].content+"</div>" ;

                        modalContent += footerTemplate(i,modals);
                        $('#'+modalId).html(modalContent);
                        modalArray[i].modal("hide");


						$(modals[i].target).ready(function(){

                            var direction = /top/.test(modals[i].placement) == true ? "down" : "up";
                            var bounce = /top/.test(modals[i].placement) == true ? "up" : "down";

							$(modals[i].target).popoverext({
							title: "",
							content: "arrow",
							footer: "",
							placement: modals[i].placement,
							id: true,
							fixed: true,
							trigger: 'manual',
                            template: '<div class="popover arrow"><div class="pointer ' +direction+'"></div></div>',
                            onShow:  function(){
                                $('.pointer').css('top','0px');

                                $(".popover .pointer").effect("bounce", { times:3, direction: bounce }, 300,
                                    function(){

//                                    $('.pointer').attr('style','');

                                    }
                                );
                            },
                            sidePadding: 0

							});
						});
                        //empty popover div and insert arrow
                        $(modals[i].target+"Popover").empty().html("arrow");
	
					}


                    function nextModal (i) {


                        if(modals.length - 1 != i) {
                            $(modals[i].target).popoverext('hide');
                            $(modals[i+1].target).popoverext('show');
                            modalArray[i].modal('hide');
                            modalArray[i+1].modal('show');
                        } else {
                            $(modals[i].target).popoverext('hide');
                            modalArray[i].modal('hide');
                            tourModal.modal("show");
                        }

                    }

                    function prevModal (i){

                        $(modals[i].target).popoverext('hide');
                        $(modals[i-1].target).popoverext('show');
                        modalArray[i].modal('hide');
                        modalArray[i-1].modal('show');
                    }


                    function skipTour (i) {
                        $(modals[i].target).popoverext('hide');
                        modalArray[i].modal('hide');
                        tourModal.modal("show");
                    }

                    function footerTemplate (i,modals) {
                        var content = $('<div></div>')
                        var footer = $("<div class=\"modal-footer\"></div>");

                        var skip = $("<a href=\"#\" class=\"btn-link\" id=\"skipTour\">Skip Tour</a>");
                        var next = $('<a class="btn btn-primary" id="nextModal'+i+'" href="#">Next <i class="icon-play icon-xsm"></i></a>');
                        content.append(footer);
                        footer.append(skip).append(next);

                        var back = $('<a class="btn" href="#" id="prevModal'+i+'">Back</a>');


                        $('#nextModal'+i).live("click", function(){
                            nextModal(i);
                        });

                        $('#prevModal'+i).live("click", function(){
                            prevModal(i);
                        });

                        $('#skipTour').live("click", function(){
                            skipTour(i);
                        });



                        if(i != 0) {
                            footer.append(back);
                        }

                        return content.html();
                    }




									
				}
			});	
		},
		saveUserPref: function(url) {	
	        $.ajax({
		    	type: "GET",
		    	url: url
		    });
		}

		
		
		
	 };
}();
