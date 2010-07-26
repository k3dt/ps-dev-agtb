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

//Use loader to grab the modules needed
var DCMenu = YUI({combine: true, timeout: 10000, base:"include/javascript/yui3/build/", comboBase:"index.php?entryPoint=getYUIComboFile&"}).use('dd', 'anim', 'cookie', 'json', 'node-menunav', 'io-base','io-form', 'io-upload-iframe', "overlay", function(Y) {
    //Make this an Event Target so we can bubble to it
    var requests = {};
    var overlays = [];
    var overlayDepth = 0;
    var menuFunctions = {};
    function getOverlay(depth){
    		if(!depth)depth = 0;
    		if(typeof overlays[depth] == 'undefined'){
    			 overlays[depth] = new Y.Overlay({
            			bodyContent: "",
           			    zIndex:10,
            			shim:false,
            			visibility:false
        		});
        		overlays[depth].after('render', function(e) {
                    //Get the bounding box node and plug
                    this.get('boundingBox').plug(Y.Plugin.Drag, {
                        //Set the handle to the header element.
                        handles: ['.hd']
                    });
                });
        		overlays[depth].show = function(){
        			this.visible = true;
                    //Hack until the YUI 3 overlay classes no longer conflicts with the YUI 2 overlay css
					this.get('boundingBox').setStyle('position' , 'absolute');
    				this.get('boundingBox').setStyle('visibility','visible');
    				if(Y.get('#dcboxbody')) {
    					Y.get('#dcboxbody').setStyle('display','');
    				}
    			}
    			overlays[depth].hide = function(){
    				this.visible = false;
    				this.get('boundingBox').setStyle('visibility','hidden');
    			}
    		}
			var dcmenuContainer = Y.get('#dcmenuContainer');
			var dcmenuContainerHeight = dcmenuContainer.get('offsetHeight');
    		overlays[depth].set('xy', [20,dcmenuContainerHeight]);
   	  	overlays[depth].render();
    		return overlays[depth]
    }
    
    DCMenu.menu = function(module,title){
        if ( typeof(lastLoadedMenu) != 'undefined' && lastLoadedMenu == module ) {
            return;
        }
        
        lastLoadedMenu = module;

    	if(typeof menuFunctions[module] == 'undefined'){
    		loadView(module, 'index.php?source_module=' + this.module + '&record=' + this.record + '&action=Quickcreate&module=' + module,null,null,title); 	
    	}	
    }
    
    
    DCMenu.displayModuleMenu = function(obj, module){
    	loadView(module, 'index.php?module=' + module + '&action=ajaxmenu', 0, 'moduleTabLI_' + module); 	
    	
    }
    
    DCMenu.closeOverlay = function(depth){
    		
    		for(i in overlays){
    			if(!depth || i >= depth){
    				if(i == depth && !overlays[i].visible){
    					overlays[i].show();	
    				}else{
    					overlays[i].hide();
    				}
    			}
    		}
    }
    DCMenu.minimizeOverlay = function(){
 		//isIE7 = ua.indexOf('msie 7')!=-1;
		//box_style = isIE7 ? 'position:fixed; width:750px;' : 'none';
		
     	Y.get('#dcboxbody').setStyle('display','none');
     	Y.get('#dcboxbody').setStyle('width', '950px;');
    }
    function setBody(data, depth, parentid,type,title){
			if(typeof(data.html) == 'undefined')data = {html:data};
			//Check for the login page, meaning we have been logged out.
			if (SUGAR.util.isLoginPage(data.html))
				return false;
    		DCMenu.closeOverlay(depth);
    		var overlay = getOverlay(depth);
    		
    		ua = navigator.userAgent.toLowerCase();
    		isIE7 = ua.indexOf('msie 7')!=-1;
    		
    		var style = 'position:fixed';
    		if(parentid){
    			overlay.set("align", {node:"#" + parentid, points:[Y.WidgetPositionExt.TL, Y.WidgetPositionExt.BL]});
				overlay.set('y', 42);
    		}
    		var content = '';
    		if(false && depth == 0){
	    		content += '<div id="dcboxtitle">' 
	    		
	    		if(typeof data.title  !=  'undefined'){
	    			content += '<div style="float:left"><a href="' +data.url + '">' + data.title + '</a></div>';
	    		}
	    		
	    		 content += '<div style="float:right"><a id="dcmenu_close_link" href="javascript:DCMenu.closeOverlay()">[x]</a><a href="javascript:void()" onclick="DCMenu.minimizeOverlay()">[-]</a></div></div>';
    		}
    		content += '<div style="' + style + '"><div id="dcboxbody"  class="'+ parentid +'"><div class="dashletPanel dc"><div class="hd"><div class="tl"></div><div class="hd-center">';
			if ( title !== undefined )
			    content +=	'<span>' + title + '</span>';
			else
			    if(typeof type  !=  'undefined')
			        content +=	'<span>' + type + '</span>';
			    
			content += '<div class="close"><a id="dcmenu_close_link" href="javascript:lastLoadedMenu=undefined;DCMenu.closeOverlay()"><img src="index.php?entryPoint=getImage&themeName=' + SUGAR.themes.theme_name + '&imageName=close_button_24.png"></a></div></div><div class="tr"></div></div><div class="bd"><div class="ml"></div><div class="bd-center"><div class="dccontent">' + data.html + '</div></div><div class="mr"></div></div><div class="ft"><div class="bl"></div><div class="ft-center"></div><div class="br"></div></div></div></div>';
    		overlay.set('bodyContent', content);
    		
    		//DCMenu.all('#dcboxbody .view').replaceClass('view', 'dcview');
    		overlay.show();
    		return overlay;
    }
	
	DCMenu.showView = function(data, parent_id){
		setBody(data, 0, parent_id);
	}
	DCMenu.iFrame = function(url, width, height){
		setBody("<iframe style='border:0px;height:" + height + ";width:" + width + "'src='" + url + "'></iframe>");
	}
	//BEGIN SUGARCRM flav=pro ONLY
    DCMenu.addToFavorites = function(item, module, record){
		Y.one(item).replaceClass('off', 'on');
		item.onclick = function(){
			DCMenu.removeFromFavorites(this, module, record);
		}
		quickRequest('favorites', 'index.php?to_pdf=1&module=SugarFavorites&action=save&fav_id=' + record + '&fav_module=' + module);
	}
	
	DCMenu.removeFromFavorites = function(item, module, record){
		Y.one(item).replaceClass('on', 'off');
		item.onclick = function(){
			DCMenu.addToFavorites(this, module, record);
		}
		quickRequest('favorites', 'index.php?to_pdf=1&module=SugarFavorites&action=delete&fav_id=' + record + '&fav_module=' + module);
	}
	DCMenu.tagFavorites = function(item,module, record, tag){
		quickRequest('favorites', 'index.php?to_pdf=1&module=SugarFavorites&action=tag&fav_id=' + record + '&fav_module=' + module + '&tag=' + tag);
	}
	//END SUGARCRM flav=pro ONLY
	//BEGIN SUGARCRM flav=following ONLY
	DCMenu.addToFollowing = function(item, module, record){
		Y.one(item).replaceClass('off', 'on');
		item.onclick = function(){
			DCMenu.removeFromFollowing(this, module, record);
		}
		quickRequest('following', 'index.php?module=SugarFollowing&action=save&following_id=' + record + '&following_module=' + module);
	}
	
	DCMenu.removeFromFollowing = function(item, module, record){
		Y.one(item).replaceClass('on', 'off');
		item.onclick = function(){
			DCMenu.addToFollowing(this, module, record);
		}
		quickRequest('following', 'index.php?module=SugarFollowing&action=delete&following_id=' + record + '&following_module=' + module);
	}
	//END SUGARCRM flav=following ONLY
	function quickRequest(type,url, success){
     	if(!success)success=function(id, data) {}
        var id = Y.io(url, {
             method: 'POST',
             //XDR Listeners
 		    on: { 
 			    success: success,
 			    failure: function(id, data) {
                     //Something failed..
                     //alert('Feed failed to load..' + id + ' :: ' + data);
                 }
 		    }
         });	
    }
    
    DCMenu.pluginList = function(){
		quickRequest('plugins', 'index.php?to_pdf=1&module=Home&action=pluginList', pluginResults);
	}
	
	pluginResults = function(id, data){
		var overlay = setBody(data.responseText, 0, 'globalLinks');	
		overlay.set('y', 90);
	}
	DCMenu.history = function(q){
		quickRequest('spot', 'index.php?to_pdf=1&module=' + this.module + '&action=modulelistmenu', spotResults);
	}
	Y.spot = function(q){
	    ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_LOADING'));
		quickRequest('spot', 'index.php?to_pdf=1&module=' + this.module + '&action=spot&record=' + this.record + '&q=' + q, spotResults);
	}
	DCMenu.spotZoom = function(q, module, offset){
		quickRequest('spot', 'index.php?to_pdf=1&module=' + this.module + '&action=spot&record=' + this.record + '&q=' + q + '&zoom=' + module + '&offset=' + offset,  spotResults);
	}
	spotResults = function(id, data){
		var overlay = setBody(data.responseText, 0, 'sugar_spot_search');
		overlay.set('x', overlay.get('x') - 60);
		ajaxStatus.hideStatus();
		//set focus on first sugaraction element, identified by id sugaraction1
		var focuselement=document.getElementById('sugaraction1');
		if (typeof(focuselement) != 'undefined' && focuselement != null) {
			focuselement.focus(); 
		}		
	}
	
	DCMenu.miniDetailView = function(module, id){
		quickRequest('spot', 'index.php?to_pdf=1&module=' + module + '&action=quick&record=' + id , miniDetailViewResults);
	}
	miniDetailViewResults = function(id, data){
		setBody(Y.JSON.parse(data.responseText), 0);	
	}
	         
	DCMenu.save = function(id){
		ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_SAVING'));
		Y.io('index.php',{
			method:'POST',
			form:{
				id:id,
				upload: true
			},
			on:{
				complete: function(id, data){
				    try { 
                        var returnData = Y.JSON.parse(data.responseText);
                        
                        switch ( returnData.status ) {
                        case 'dupe':
                            location.href = 'index.php?' + returnData.get;
                            break;
                        case 'success':
                            ajaxStatus.flashStatus(SUGAR.language.get('app_strings', 'LBL_SAVED'), 2000);
                            break;
                        }
                    } 
                    catch (e) { 
                        ajaxStatus.flashStatus(SUGAR.language.get('app_strings', 'LBL_SAVED'), 2000);
                    }
				}	
			}
			
		});
		lastLoadedMenu=undefined;
		DCMenu.closeOverlay();	
		return false;	
	}
	
  
   
    DCMenu.loadView = function(type,url, depth, parentid, title){
        var id = Y.io(url, {
             method: 'POST',
             //XDR Listeners
 		    on: { 
 			    success: function(id, data) {
            		 //Parse the JSON data
            		 try{
                     	jData = Y.JSON.parse(data.responseText);
                     	//saveView(type, requests[id].url,jData);
                     	setBody(jData, requests[id].depth, requests[id].parentid,title);
                     	 var head =Y.Node.get('head')
                     	for(i in jData['scripts']){
                    	 var script = document.createElement('script');
                    	 script.src =jData['scripts'][i];
                    	 head.appendChild(script);
                     	}
                     	SUGAR.util.evalScript(jData.html);
                     	setTimeout("enableQS();", 1000);
            		 }catch(err){
            			setBody({html:data.responseText}, requests[id].depth, requests[id].parentid,requests[id].type,title);
            		 	SUGAR.util.evalScript(data.responseText);
            		 	setTimeout("enableQS();", 1000);
            		 }
                    
                     
                     
                 },
 			    failure: function(id, data) {
                     //Something failed..
                     //alert('Feed failed to load..' + id + ' :: ' + data);
                 }
 		    }
         });
         requests[id.id] = {type:type, url:url, parentid:parentid, depth:depth}; 	
    }
    
    var loadView = Y.loadView;
    DCMenu.notificationsList = function(q){
		quickRequest('notifications', 'index.php?to_pdf=1&module=Notifications&action=quicklist', notificationsListDisplay );
	}
	notificationsListDisplay = function(id, data){
		setBody(data.responseText, 0, 'dcmenuSugarCube');	
	}
	DCMenu.viewMiniNotification = function(id) {
	    quickRequest('notifications', 'index.php?to_pdf=1&module=Notifications&action=quickView&record='+id, notificationDisplay );
	}
    notificationDisplay = function(id, data){
        var jData = Y.JSON.parse(data.responseText);
		setBody(jData.contents, 0);	
		decrementUnreadNotificationCount();
	}
	decrementUnreadNotificationCount = function() {
	    var oldValue = parseInt(document.getElementById('notifCount').innerHTML);
		document.getElementById('notifCount').innerHTML = oldValue - 1;
	}
	updateNotificationNumber = function(id,data){
	    var jData = Y.JSON.parse(data.responseText);
		var oldValue = parseInt(document.getElementById('notifCount').innerHTML);
		document.getElementById('notifCount').innerHTML = parseInt(jData.unreadCount) + oldValue;
	}
	DCMenu.checkForNewNotifications = function(){
	    quickRequest('notifications', 'index.php?to_pdf=1&module=Notifications&action=checkNewNotifications', updateNotificationNumber );
	}


});