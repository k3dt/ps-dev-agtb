	var CAL = {};

	CAL.dropped = 0;
	CAL.records_openable = true;
	CAL.moved_from_cell = "";
	CAL.deleted_id = "";
	CAL.deleted_module = "";
	CAL.old_caption = "";
	CAL.disable_creating = false;
	CAL.record_editable = false;		
	CAL.tp = false;
	CAL.tp1 = false;
	CAL.shared_users = {};
	CAL.shared_users_count = 0;
	CAL.script_evaled = false;
	CAL.recordDialog = false;
	CAL.settingsDialog = false;	
	CAL.scroll_slot = 0;
	
	CAL.dom = YAHOO.util.Dom;
	CAL.get = YAHOO.util.Dom.get;
	CAL.query = YAHOO.util.Selector.query;
	
	
	CAL.align_divs = function (cell_id){
		if(!cell_id)
			return;	
		cellElm = document.getElementById(cell_id);
		if(cellElm){				
			var total_height = 0;
			var prev_i = 0;
			var first = 1;
			var top = 0;
			var height = 0;
			var cnt = 0;
			var child_cnt = cellElm.childNodes.length;
			for(var i = 0; i < child_cnt; i++){
					var width_p = (92 / child_cnt);
					width = width_p.toString() + "%";
					if(cellElm.childNodes[i].tagName == "DIV"){
						cellElm.childNodes[i].style.top = "-1px";
						cellElm.childNodes[i].style.left = "-"+(cnt+1)+"px"; 
						cellElm.childNodes[i].style.width = width							
						cnt++;						
						prev_i = i;					
					}
			}
		}
	}
	
	CAL.add_item_to_grid = function (ActRecord){
			
			var duration_text = ActRecord.duration_hours + "h";
			if(ActRecord.duration_minutes > 0)
				duration_text += ActRecord.duration_minutes + "m";
			var startD = new Date((ActRecord.timestamp)*1000);
			
			var suffix = "";
			var id_suffix = "";			
			
			if( ActRecord.user_id != "" && CAL.pview == 'shared'){

				suffix = "_" + CAL.shared_users[ActRecord.user_id];	
				id_suffix = '____' + CAL.shared_users[ActRecord.user_id];			
			}
			
			var e = CAL.get(ActRecord.record + id_suffix);
			if(e)
				e.parentNode.removeChild(e);			
				
			var start_text = CAL.get_header_text(ActRecord.type,ActRecord.time_start,ActRecord.status,ActRecord.record);
			
			var time_cell = ActRecord.timestamp - ActRecord.timestamp % (CAL.t_step * 60);			
			
			var duration_coef; 
			if(ActRecord.module_name == 'Tasks'){
				duration_coef = 1;
				duration_text = " ";
			}else{	
				if((ActRecord.duration_minutes < CAL.t_step) && (ActRecord.duration_hours == 0))
					duration_coef = 1;
				else					
					duration_coef = (parseInt(ActRecord.duration_hours) * 60 + parseInt(ActRecord.duration_minutes)) / CAL.t_step;
			}			

			var item_text = "";
			if(CAL.item_text && (typeof ActRecord[CAL.item_text] != "undefined") )
				item_text = ActRecord[CAL.item_text];
			
			var contain_style = "";
			if(duration_coef < 1.75)
				contain_style = "style='display: none;'";							
			
			var elm_id = ActRecord.record + id_suffix;			
			
			var el = document.createElement("div");
			el.innerHTML = "<div class='head'><div class='adicon' onmouseover='return CAL.show_additional_details(" + '"' + ActRecord.record  + id_suffix + '"'  +  ");' onmouseout='return nd(400);' >&nbsp;&nbsp;</div><div>" + start_text + "</div>" + "" + "</div><div class='contain' "+contain_style+">" + item_text + "</div>"; 
			el.className = "act_item" + " " + ActRecord.type+"_item";
			el.setAttribute("id",elm_id);
			el.setAttribute("module_name",ActRecord.module_name);
			el.setAttribute("record",ActRecord.record);
			el.setAttribute("dur",duration_text);
			el.setAttribute("subj",ActRecord.record_name);
			el.setAttribute("date_start",ActRecord.date_start);
			el.setAttribute("desc",ActRecord.description);
			el.setAttribute("parent_name",ActRecord.parent_name);
			el.setAttribute("parent_type",ActRecord.parent_type);
			el.setAttribute("parent_id",ActRecord.parent_id);
			el.setAttribute("status",ActRecord.status);
			el.setAttribute("detailview",ActRecord.detailview);
			el.setAttribute("editview",ActRecord.editview);
			el.setAttribute("duration_coef",duration_coef);
			el.style.backgroundColor = CAL.activity_colors[ActRecord.module_name]['body'];
			el.style.borderColor = CAL.activity_colors[ActRecord.module_name]['border'];	
			el.style.height = parseInt(15 * duration_coef - 1) + "px";
			YAHOO.util.Event.on(el,"click",function(){
					if(this.getAttribute('detailview') == "1")
						CAL.FormLoad(this.getAttribute('module_name'),this.getAttribute('record'),false);
			});
			YAHOO.util.Event.on(el,"mouseover",function(){
				if(!CAL.records_openable)
					return;
				CAL.disable_creating = true;	
				CAL.tp = setTimeout(
					function(){
						var e;
						if(e = CAL.get(elm_id))
							e.style.zIndex = 2;
					},
					150
				); 
			});
			YAHOO.util.Event.on(el,"mouseout",function(){
				if(!CAL.records_openable)
					return;
				clearTimeout(CAL.tp);
				CAL.get(elm_id).style.zIndex = '';
				CAL.disable_creating = false;						
			});
			
			var slot;
			if(slot = CAL.get("t_" + time_cell + suffix)){
				slot.appendChild(el);						
				
				if(duration_coef < 1.75 && CAL.mouseover_expand){
					YAHOO.util.Event.on(elm_id,"mouseover",function(){
						if(CAL.records_openable)
							CAL.expand_record(this.getAttribute("id"));						
					});
					YAHOO.util.Event.on(elm_id,"mouseout",function(){	
						CAL.unexpand_record(this.getAttribute("id"));						
					});
					YAHOO.util.Event.on(elm_id,"click",function(){
						CAL.unexpand_record(this.getAttribute("id"));
					});
				}								
				
				if(CAL.items_draggable && ActRecord.module_name != "Tasks" && ActRecord.editview == 1){

					var dd = new YAHOO.util.DDCAL(elm_id,"cal",{isTarget: false,cont:'cal-grid'}); 									
													
					dd.onInvalidDrop = function(e){ 
						this.el.style.left = "-1px";
						this.el.style.top = "-1px";
						if(CAL.dropped == 0){
							this.el.childNodes[0].innerHTML = CAL.old_caption;
						}				 
					}					
									
					dd.onMouseDown = function(e){
						YAHOO.util.DDM.mode = YAHOO.util.DDM.POINT;
						YAHOO.util.DDM.clickPixelThresh = 20;
					}
					
					dd.onMouseUp = function(e){
						YAHOO.util.DDM.mode = YAHOO.util.DDM.INTERSECT;
						YAHOO.util.DDM.clickPixelThresh = 3;
					}
					
					dd.startDrag = function(x,y){					
						this.el = document.getElementById(this.id);
						this.el.style.zIndex = 5;
						CAL.dropped = 0;						
						CAL.records_openable = false;
						CAL.old_caption = this.el.childNodes[0].innerHTML;
						CAL.moved_from_cell = this.el.parentNode.id;							
						
						this.setDelta(2,2);
					}
					
					dd.endDrag  = function(x,y){		
						this.el = document.getElementById(this.id);
						this.el.style.zIndex = "";
						
						var nodes = CAL.query("#cal-grid .slot");
						CAL.each(nodes,function(i,v){
							YAHOO.util.Dom.removeClass(nodes[i],"slot_active");
						});						
					}
					
					dd.onDragDrop = function(e,id){ 
							
						var slot = document.getElementById(id);
						YAHOO.util.Dom.removeClass(slot,"slot_active");
						if(CAL.dropped) // prevent dropping on two slots in same time
							return;
						CAL.dropped = 1;
												
						this.el.style.position = "relative";
						this.el.style.cssFloat = "none";						
						
						if(CAL.pview != 'shared'){
							var box_id = this.id;
							var slot_id = id;
							var ex_slot_id = CAL.moved_from_cell;				
							CAL.move_activity(box_id,slot_id,ex_slot_id);
						}else{					
							var record = this.el.getAttribute("record");
							var tid = id;
							var tar = tid.split("_");					
							var timestamp = tar[1];
							var tid = CAL.moved_from_cell;
							var tar = tid.split("_");					
							var ex_timestamp = tar[1];

							for(i = 0; i < CAL.shared_users_count; i++){		
								var box_id = ""+record+"____"+i;	
								var slot_id = "t_"+timestamp+"_"+i;
								var ex_slot_id = "t_"+ex_timestamp+"_"+i;						
								CAL.move_activity(box_id,slot_id,ex_slot_id);
							}				
						}
						
						var callback = {
							success: function(o){
								CAL.records_openable = true;
								CAL.update_vcal();
								ajaxStatus.hideStatus();
							}							 
						};
						ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_SAVING'));						
						var url = "index.php?module=Calendar&action=AjaxReschedule&sugar_body_only=true";
						var data = {
								"current_module" : this.el.getAttribute("module_name"),
								"record" : this.el.getAttribute("record"),
								"datetime" : slot.getAttribute("datetime")
						};						
						YAHOO.util.Connect.asyncRequest('POST',url,callback,CAL.toURI(data));						
											
					
						YAHOO.util.Dom.removeClass(slot,"slot_active");	
						CAL.disable_creating = false;	
						CAL.records_openable = true;							
										
					}
					
					dd.onDragOver = function(e,id){
						var slot = document.getElementById(id);
						if(!YAHOO.util.Dom.hasClass(slot,"slot_active"))
							YAHOO.util.Dom.addClass(slot,"slot_active");						
						this.el.childNodes[0].childNodes[1].childNodes[0].innerHTML = slot.getAttribute('dur');											
					}
					
					dd.onDragOut = function(e,id){
						var slot = document.getElementById(id);
						YAHOO.util.Dom.removeClass(slot,"slot_active");
					}					
				}

				CAL.cut_record(ActRecord.record + id_suffix);				
				CAL.align_divs("t_" + time_cell + suffix);
			}
				
	}

	CAL.expand_record = function (id){
							CAL.tp1 = setTimeout(
								function(){
									var el = CAL.get(id);
									if(el){
										el.style.height = parseInt(15 * 2 - 1) + "px";
										el.childNodes[1].style.display = "block"; //innerHTML = el.getAttribute("item_text");
									}			
								},
								350
							);
	}

	CAL.unexpand_record = function (id){
							clearTimeout(CAL.tp1);
							var el = CAL.get(id);
							el.style.height = parseInt(15 * CAL.get(id).getAttribute("duration_coef") - 2) + "px";
							el.childNodes[1].style.display = "none"; //innerHTML = "";
							CAL.cut_record(id);
	}

	CAL.get_header_text = function (type,time_start,status,record){
			var start_text = "<span class='start_time'>" + time_start + "</span> " + SUGAR.language.languages.app_list_strings[type +'_status_dom'][status];
			return start_text;
	}
	
	CAL.cut_record = function (id){
	
			var el = CAL.get(id);			
			if(!el)
				return;
				
			var duration_coef = el.getAttribute("duration_coef");
			var real_celcount = CAL.celcount;
			
			if(CAL.pview == 'day' || CAL.pview == 'week')
				real_celcount = CAL.cells_per_day;	
			
			var celpos = 0;			
			var s = el.parentNode;
			while(s.previousSibling){
				celpos++;
				s = s.previousSibling;
			}
			
			if(CAL.pview == 'week')
				celpos = celpos + 1;
			
			if(real_celcount - celpos - duration_coef < 0)
				duration_coef = real_celcount - celpos + 1;							
			el.style.height = parseInt(15 * duration_coef - 1) + "px";
			
	}

	CAL.init_record_dialog = function (params){
		CAL.recordDialog = false;
		
		var rd = CAL.get("record_dialog");
		var content = CAL.get("dialog_content");
		
		if(CAL.dashlet && rd){
			document.getElementById("content").appendChild(rd);
		}
			
		rd.style.width = params.width+"px";				
		content.style.height = params.height+"px";
		content.style.overflow = "auto";
		content.style.padding = "0";

		CAL.recordDialog = new YAHOO.widget.Dialog("record_dialog",{ 
			fixedcenter : true,
			draggable : true,
			visible : false,
			modal : true,
			close : true,
			zIndex : 10
		});
		var listeners = new YAHOO.util.KeyListener(document, { keys : 27 }, {fn: function() { CAL.recordDialog.cancel();} } );
		CAL.recordDialog.cfg.queueProperty("keylisteners", listeners);
		
		
		
		CAL.recordDialog.cancelEvent.subscribe(function(e, a, o){					
			CAL.close_record_dialog();
		});
		
		rd.style.display = "block";
		CAL.recordDialog.render();
		
		rd.style.overflow = "auto";
		rd.style.overflowX = "hidden";
		rd.style.outline = "0 none";
		rd.style.height = "auto";
	
	}
	

	CAL.open_record_dialog = function (params){	
											
						CAL.recordDialog.show();		
						
						var nodes = CAL.query("#record_tabs li a");
						CAL.each(nodes,function(i,v){
							YAHOO.util.Event.on(nodes[i], 'click', function(){
								var nodes_li = CAL.query("#record_tabs li");
								CAL.each(nodes_li,function(j,v){
									CAL.dom.removeClass(nodes_li[j],"selected");
								});
							
								if(!CAL.dom.hasClass(this.parentNode,"selected"))
									CAL.dom.addClass(this.parentNode,"selected");	
								CAL.select_tab(this.getAttribute("tabname"));
							});
						});
						
						var nodes_li = CAL.query("#record_tabs li");
						CAL.each(nodes_li,function(j,v){																		
							CAL.dom.removeClass(nodes_li[j],"selected");
							if(j == 0)
								CAL.dom.addClass(nodes_li[j],"selected");	
						});
						
						var nodes = CAL.query(".yui-nav");
						CAL.each(nodes,function(i,v){
							nodes[i].style.overflowX = "visible";
						});					

	}

	CAL.close_record_dialog = function (){
					
					CAL.clearFields();
	}
	
	CAL.remove_record_dialog = function(){
		var rd_c = CAL.get("record_dialog_c");
		if(rd_c){
			rd_c.parentNode.removeChild(rd_c);
		}		
	}	
	
	CAL.clearFields = function (){
		var e;

		document.getElementById("form_content").innerHTML = "";	
		document.forms["CalendarEditView"].elements["current_module"].value = "Meetings";
	
		CAL.get("radio_call").removeAttribute("disabled");
		CAL.get("radio_meeting").removeAttribute("disabled");
		CAL.get("radio_call").checked = false;
		CAL.get("radio_meeting").checked = true;
		
		CAL.get("send_invites").value = "";
	
		if(e = CAL.get("record"))
			e.value = "";		
			
		if(e = CAL.get("list_div_win"))
			e.style.display = "none";
			
		if(e = CAL.get("edit_all_recurrences_btn"))
			e.style.display = "none"; 		
 		 		 		
 		var nodes = CAL.query("#repeat_type option[value='']");
 		CAL.each(nodes,function(i,v){
 			nodes[i].setAttribute("selected","selected");
 		});
 		
 		//CAL.repeat_type_selected(); 		
	
 		CAL.GR_update_focus("Meetings",""); 
 		CAL.select_tab("record_tabs-1");
	}

	CAL.select_tab = function (tid){
 		var nodes = CAL.query("#record_tabs .yui-content");
 		CAL.each(nodes,function(i,v){
 			nodes[i].style.display = "none";
 		});
 		var nodes = CAL.query("#record_tabs #"+tid);
 		CAL.each(nodes,function(i,v){
 			nodes[i].style.display = "block";
 		});
	}

	CAL.GR_update_user = function (user_id){	
	
		var callback = {
			success: function(o){				
				res = eval(o.responseText);
				GLOBAL_REGISTRY.focus.users_arr_hash = undefined;											
				//SugarWidgetScheduler.update_time();
			}
		};
	
		var data = {
			"users": user_id
		};
		var url = "index.php?module=Calendar&action=AjaxGetGRUsers&sugar_body_only=true";	
		YAHOO.util.Connect.asyncRequest('POST',url,callback,CAL.toURI(data));

	}
		
	CAL.GR_update_focus = function (module,record){
		if(record == ""){
			GLOBAL_REGISTRY["focus"] = {"module":module, users_arr:[],fields:{"id":"-1"}};
			SugarWidgetScheduler.update_time();			
		}else{		
			var callback = {
				success: function(o){
					res = eval(o.responseText);
					SugarWidgetScheduler.update_time();											
					if(CAL.record_editable){
						CAL.get("btn_save").removeAttribute("disabled");
						CAL.get("btn_delete").removeAttribute("disabled");
						CAL.get("btn_apply").removeAttribute("disabled");
						CAL.get("btn_send_invites").removeAttribute("disabled");
					}
				}
			};	

			var url = 'index.php?module=Calendar&action=AjaxGetGR&sugar_body_only=true&type=' + module + '&record=' + record;	
			YAHOO.util.Connect.asyncRequest('POST',url,callback,false);
		}		
	}

	CAL.toggle_settings = function (){
		var sd = CAL.get("settings_dialog");		
			
			
		if(!CAL.settingsDialog){	
			CAL.settingsDialog = new YAHOO.widget.Dialog("settings_dialog",{ 
				  	fixedcenter: true,
				  	draggable: false,
				  	visible : false, 
				 	modal : true,
				  	close: true
			});
			var listeners = new YAHOO.util.KeyListener(document, { keys : 27 }, {fn: function() { CAL.settingsDialog.cancel();} } );
			CAL.settingsDialog.cfg.queueProperty("keylisteners", listeners);
		}
		CAL.settingsDialog.cancelEvent.subscribe(function(e, a, o){
			CAL.get("form_settings").reset();
		});
		sd.style.display = "block";	 
		CAL.settingsDialog.render();
		CAL.settingsDialog.show();
	}

	CAL.toggle_whole_day = function (){
		var wd = CAL.get("whole_day");
		if(!wd.value)
			wd.value = "1";
		else
			wd.value = "";
		setTimeout(
			function(){
				if(wd.value){
			 		var nodes = CAL.query("#cal-grid .owt");
			 		CAL.each(nodes,function(i,v){			 			
			 			nodes[i].style.display = "block";
			 		});
				}else{
		 			var nodes = CAL.query("#cal-grid .owt");
			 		CAL.each(nodes,function(i,v){
			 			nodes[i].style.display = "none";
			 		});
				}
			},
			25);
	
	}
		
	CAL.fill_invitees = function (){	
		
		CAL.get("user_invitees").value = "";
		CAL.get("contact_invitees").value = "";
		CAL.get("lead_invitees").value = "";	

		CAL.each( GLOBAL_REGISTRY['focus'].users_arr, 	function(i,v){
									var field_name = "";
									if(v.module == "User")
										field_name = "user_invitees";
									if(v.module == "Contact")
										field_name = "contact_invitees";
									if(v.module == "Lead")
										field_name = "lead_invitees";
									var str = CAL.get(field_name).value;
									CAL.get(field_name).value = str + v.fields.id + ",";	
								}
		);	
	}	
				
	
	CAL.repeat_type_selected = function (){
		var rt;
		if(rt = CAL.get("repeat_type")){
			if(rt.value == 'Weekly'){
				var nodes = CAL.query(".weeks_checks_div");
				CAL.each(nodes,function (i,v){
					nodes[i].style.display = "block";				
				});
			}else{
				var nodes = CAL.query(".weeks_checks_div");
				CAL.each(nodes,function (i,v){
					nodes[i].style.display = "none";				
				});
			}
		
			if(rt.value == ''){
				CAL.get("repeat_interval").setAttribute("disabled","disabled");
				CAL.get("repeat_end_date").setAttribute("disabled","disabled");
			}else{
				CAL.get("repeat_interval").removeAttribute("disabled");
				CAL.get("repeat_end_date").removeAttribute("disabled");	
			}
		}
	}
		
	CAL.FormLoad = function (module_name,record,run_one_time){
	
		var e;
		var to_open = true;
		if(module_name == "Tasks")
			to_open = false;

		if(to_open && CAL.records_openable){
			CAL.get("form_content").style.display = "none";
		
			CAL.get("btn_delete").setAttribute("disabled","disabled");
			CAL.get("btn_apply").setAttribute("disabled","disabled");
			CAL.get("btn_save").setAttribute("disabled","disabled");
			CAL.get("btn_send_invites").setAttribute("disabled","disabled");		
	
			CAL.get("title-record_dialog").innerHTML = CAL.lbl_loading;
			
			ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_LOADING'));
			
			CAL.open_record_dialog();	
			CAL.get("record").value = "";	
						
	
			var callback = {
																	
					success: function(o){
						res = eval("("+o.responseText+")");			
						if(res.success == 'yes'){						
							var fc = document.getElementById("form_content");
							CAL.script_evaled = false;
							fc.innerHTML = '<script type="text/javascript">CAL.script_evaled = true;</script>'+res.html; 
							if(!CAL.script_evaled){											
								SUGAR.util.evalScript(res.html);
							}
							
							CAL.get("record").value = res.record;
							CAL.get("current_module").value = res.module_name;	
							
							var mod_name = res.module_name;	
							
							if(mod_name == "Meetings")
								CAL.get("radio_meeting").checked = true;
							if(mod_name == "Calls")
								CAL.get("radio_call").checked = true;
								
							if(res.editview == 1){
								CAL.record_editable = true;
							}else{
								CAL.record_editable = false;
							}
							
							CAL.get("radio_call").setAttribute("disabled","disabled");
							CAL.get("radio_meeting").setAttribute("disabled","disabled");														
		
							eval(res.gr);							
							SugarWidgetScheduler.update_time();											
							if(CAL.record_editable){
								CAL.get("btn_save").removeAttribute("disabled");
								CAL.get("btn_delete").removeAttribute("disabled");
								CAL.get("btn_apply").removeAttribute("disabled");
								CAL.get("btn_send_invites").removeAttribute("disabled");
							}							
							
							CAL.get("form_content").style.display = "";
							CAL.get("title-record_dialog").innerHTML = CAL.lbl_edit;
							ajaxStatus.hideStatus();
							
							if(typeof changeParentQS != 'undefined')							
								changeParentQS("parent_name");
								
							setTimeout(function(){
								enableQS(false);
								disableOnUnloadEditView();
							},500);
							
						}else
							alert(CAL.lbl_error_loading);
																			
					},
					failure: function(){
						alert(CAL.lbl_error_loading);
					}
			};
			var url = "index.php?module=Calendar&action=AjaxLoadForm&sugar_body_only=true";
			var data = {
				"current_module" : module_name,
				"record" : record
			};
			YAHOO.util.Connect.asyncRequest('POST',url,callback,CAL.toURI(data));
			
		}
		CAL.records_openable = true;								
	}
		
	
	CAL.removeSharedById = function (record_id){
			var e;
			var cell_id;
			if(e = CAL.get(record_id + '____' + "0"))
				cell_id = e.parentNode.id;
				
			if(typeof cell_id != "undefined"){
				var cell_id_arr = cell_id.split("_");
				cell_id = "t_" + cell_id_arr[1];
			}											
			CAL.each(CAL.shared_users,function(i,v){	
				if(e = CAL.get(record_id + '____' + v))	
					e.parentNode.removeChild(e);														
				CAL.align_divs(cell_id + '_' + v);
			});
			if(e = CAL.get(record_id))
				e.parentNode.removeChild(e);
			CAL.align_divs(cell_id);				
	}
			
	CAL.add_items = function (res){
				
			if(CAL.pview != 'shared'){
				CAL.add_item_to_grid(res);								

			}else{
				CAL.removeSharedById(res.record);
				record_id = res.record;
				//var rec_id_c = res.rec_id_c;
				var timestamp = res.timestamp;
				CAL.each(
					res.users,
					function (i,v){						
						var rec = res;
						//rec.rec_id_c = rec_id_c;
						rec.timestamp = timestamp;						
						rec.user_id = v;
						rec.record = record_id;
						CAL.add_item_to_grid(rec);
						
						CAL.each(
							rec.arr_rec,
							function (j,r){
								rec.record = r.record;
								rec.timestamp = r.timestamp;
								//rec.rec_id_c = record_id;
								CAL.add_item_to_grid(rec);
							}				
						);																	 						
					}											 					
				);
			}
	}
		
	CAL.move_activity = function (box_id,slot_id,ex_slot_id){
				var u,s;						
				if(u = CAL.get(box_id)){
					if(s = CAL.get(slot_id)){
						s.appendChild(u);
						CAL.align_divs(slot_id);
						CAL.align_divs(ex_slot_id);
						CAL.cut_record(box_id);					
						var start_text = CAL.get_header_text(CAL.act_types[u.getAttribute('module_name')],s.getAttribute('dur'),u.getAttribute('status'),u.getAttribute('record'));
						u.setAttribute("date_start",s.getAttribute("datetime"));				
						u.childNodes[0].childNodes[1].innerHTML = start_text;
					}
				}
	}	
	
	CAL.change_activity_type = function (mod_name){
		if(typeof CAL.current_params.module_name != "undefined" )
			if(CAL.current_params.module_name == mod_name)
				return;
	
		var e,user_name,user_id,date_start;
		
		CAL.get("title-record_dialog").innerHTML = CAL.lbl_loading;
				
		document.forms["CalendarEditView"].elements["current_module"].value = mod_name; 	
	
		CAL.current_params.module_name = mod_name;
		CAL.load_create_form(CAL.current_params);				
	}


	
	CAL.load_create_form = function (params){
	
			ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_LOADING'));
			
			var callback = {
																	
					success: function(o){
						res = eval("("+o.responseText+")");			
						if(res.success == 'yes'){						
							var fc = document.getElementById("form_content");
							CAL.script_evaled = false;
							fc.innerHTML = '<script type="text/javascript">CAL.script_evaled = true;</script>'+res.html; 
							
							if(!CAL.script_evaled){											
								SUGAR.util.evalScript(res.html);
							}
														
							CAL.get("record").value = "";
							CAL.get("current_module").value = res.module_name;								
							var mod_name = res.module_name;	
													
							if(res.editview == 1){
								CAL.record_editable = true;
							}else{
								CAL.record_editable = false;
							}
														
							CAL.get("title-record_dialog").innerHTML = CAL.lbl_create_new;
																					
							SugarWidgetScheduler.update_time();
								
							setTimeout(function(){
								enableQS(false);
								disableOnUnloadEditView();
							},500);
							
							ajaxStatus.hideStatus();
							
						}else{
							alert(CAL.lbl_error_loading);
							ajaxStatus.hideStatus();
						}
					},
					failure: function() {
						alert(CAL.lbl_error_loading);
						ajaxStatus.hideStatus();
					}	
			};			
							
			var url = "index.php?module=Calendar&action=AjaxLoadForm&sugar_body_only=true";
			var data = {
				"current_module" : params.module_name,
				"assigned_user_id" : params.user_id,
				"assigned_user_name" : params.user_name,
				"date_start" : params.date_start,
				"duration_hours" : 1,
				"duration_minutes" : 0
			};
			YAHOO.util.Connect.asyncRequest('POST',url,callback,CAL.toURI(data));	
	}
		
	
	CAL.dialog_create = function (cell){
	
			var e,user_id,user_name;
			CAL.get("title-record_dialog").innerHTML = CAL.lbl_loading;														
			CAL.open_record_dialog();
			CAL.get("btn_delete").setAttribute("disabled","disabled");
			
			var module_name = CAL.get("current_module").value;
			
			if(CAL.pview == 'shared'){
				user_name = cell.parentNode.parentNode.parentNode.getAttribute("user_name");
				user_id = cell.parentNode.parentNode.parentNode.getAttribute("user_id");
				CAL.GR_update_user(user_id);
			}else{
				user_id = CAL.current_user_id;
				user_name = CAL.current_user_name;
				CAL.GR_update_user(CAL.current_user_id);												
			}
			
			var params = {	
				'module_name': module_name,
				'user_id': user_id,	
				'user_name': user_name,			
				'date_start': cell.getAttribute("datetime")				
			};
			CAL.current_params = params;
			CAL.load_create_form(CAL.current_params);
							
	}
	
	CAL.dialog_save = function(){						
						ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_SAVING'));
						
						CAL.get("title-record_dialog").innerHTML = CAL.lbl_saving;																					
						CAL.fill_invitees();							
						//CAL.fill_recurrence();
												
						var callback = {
								success: function(o){
									res = eval('('+o.responseText+')');	
									if(res.success == 'yes'){
										CAL.add_items(res);
										CAL.recordDialog.cancel();
										CAL.update_vcal();
										ajaxStatus.hideStatus();												
									}else{
										alert(CAL.lbl_error_saving);
										ajaxStatus.hideStatus();
									}														
								},
								failure: function(){
										alert(CAL.lbl_error_saving);
										ajaxStatus.hideStatus();
								}
						};						
						var url = "index.php?module=Calendar&action=AjaxSave&sugar_body_only=true"; 
						YAHOO.util.Connect.setForm(CAL.get("CalendarEditView"));
						YAHOO.util.Connect.asyncRequest('POST',url,callback,false);	
	}
	
	CAL.dialog_apply = function(){
						ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_SAVING'));
						
						CAL.get("title-record_dialog").innerHTML = CAL.lbl_saving;								
						CAL.fill_invitees();							
						//CAL.fill_recurrence();	
						
						var e;
						if(e = CAL.get("radio_call"))
							e.setAttribute("disabled","disabled");
						if(e = CAL.get("radio_meeting"))
							e.setAttribute("disabled","disabled");													

						CAL.get("btn_apply").setAttribute("disabled","disabled");
						
						var callback = {
								success: function(o){
									res = eval('('+o.responseText+')');	
									if(res.success == 'yes'){										
										var e;
										CAL.get("record").value = res.record;	
										//SugarWidgetScheduler.update_time();
										//CAL.GR_update_focus(CAL.get("current_module").value,res.record);
										CAL.add_items(res);
										CAL.update_vcal();											 											 				
										CAL.get("title-record_dialog").innerHTML = CAL.lbl_edit;
										if(e = CAL.get("send_invites"))
											e.removeAttribute("checked");
										ajaxStatus.hideStatus();											
									}else{
										alert(CAL.lbl_error_saving);
										ajaxStatus.hideStatus();
									}														
								},
								failure: function(){
										alert(CAL.lbl_error_saving);
										ajaxStatus.hideStatus();
								}
						};						
						var url = "index.php?module=Calendar&action=AjaxSave&sugar_body_only=true"; 
						YAHOO.util.Connect.setForm(CAL.get("CalendarEditView"));
						YAHOO.util.Connect.asyncRequest('POST',url,callback,false);
	}
	
	CAL.dialog_remove = function(){
									CAL.deleted_id = CAL.get("record").value;
									CAL.deleted_module = CAL.get("current_module").value;									
									var delete_recurring = false;								
											
									var callback = {
											success: function(o){
												res = eval('('+o.responseText+')');
													
												var e,cell_id;
												if(e = CAL.get(CAL.deleted_id))
													cell_id = e.parentNode.id;
												if(CAL.pview == 'shared')	
													CAL.removeSharedById(CAL.deleted_id);	
													
												if(e = CAL.get(CAL.deleted_id))
													e.parentNode.removeChild(e);									
												
												CAL.align_divs(cell_id);
								
																										
											},
											failure: function(){
													alert(CAL.lbl_error_saving);
											}
									};									
									
									var data = {
										"current_module" : CAL.deleted_module,
										"record" : CAL.deleted_id,
										"delete_recurring": delete_recurring
									};
									var url = "index.php?module=Calendar&action=AjaxRemove&sugar_body_only=true";									
									YAHOO.util.Connect.asyncRequest('POST',url,callback,CAL.toURI(data));

									CAL.recordDialog.cancel();	
	}
	
	CAL.show_additional_details = function (d_id){
		var obj = CAL.get(d_id);
	
		var record = obj.getAttribute("record");
		mod = obj.getAttribute("module_name");
		var atype = CAL.act_types[mod];
	
		var subj = obj.getAttribute("subj");
		var date_start = obj.getAttribute("date_start");
		var duration = obj.getAttribute("dur");	
		var desc = obj.getAttribute("desc");	
		var detailview = parseInt(obj.getAttribute("detailview"));
		var editview = parseInt(obj.getAttribute("editview"));				
		
		var related = "";
		if(obj.getAttribute("parent_id") != '')
			related = "<b>" + CAL.lbl_related + ":</b> <a href='index.php?module="+obj.getAttribute("parent_type")+"&action=DetailView&record="+obj.getAttribute("parent_id")+"'>"+obj.getAttribute("parent_name")+"</a>" + "<br>";

		if(desc != '')
			desc = '<b>'+ CAL.lbl_desc + ':</b><br> ' + desc +'<br>';
			
		if(subj == '')
			return "";

		var date_lbl = CAL.lbl_start_t;
		var duration_text = '<b>'+CAL.lbl_duration+':</b> ' + duration + '<br>';
		if(mod == "Tasks"){
			date_lbl = CAL.lbl_due_t;
			duration_text = "";			
		}

		var caption = "<div style='float: left;'>"+CAL.lbl_title+"</div><div style='float: right;'>";
		if(editview){
			caption += "<a title=\'"+SUGAR.language.get('app_strings', 'LBL_EDIT_BUTTON')+"\' href=\'index.php?module="+mod+"&action=EditView&record="+record+"\'><img border=0  src=\'"+CAL.img_edit_inline+"\'></a>";
		}
		if(detailview){
			caption += "<a title=\'"+SUGAR.language.get('app_strings', 'LBL_VIEW_BUTTON')+"\' href=\'index.php?module="+mod+"&action=DetailView&record="+record+"\'><img border=0  style=\'margin-left:2px;\' src=\'"+CAL.img_view_inline+"\'></a>";
		}
		caption += "<a title=\'"+SUGAR.language.get('app_strings', 'LBL_ADDITIONAL_DETAILS_CLOSE_TITLE')+"\' href=\'javascript:return cClick();\' onclick=\'javascript:return cClick();\'><img border=0  style=\'margin-left:2px;margin-right:2px;\' src=\'"+CAL.img_close+"\'></a></div>";

		
		var body = '<b>'+CAL.lbl_name+':</b> ' + subj + ' <br><b>'+date_lbl+':</b> ' + date_start + '<br>' + duration_text + related + desc;
		return overlib(body, CAPTION, caption, DELAY, 200, STICKY, MOUSEOFF, 200, WIDTH, 300, CLOSETEXT, '', CLOSETITLE, SUGAR.language.get('app_strings','LBL_ADDITIONAL_DETAILS_CLOSE_TITLE'), CLOSECLICK, FGCLASS, 'olFgClass', CGCLASS, 'olCgClass', BGCLASS, 'olBgClass', TEXTFONTCLASS, 'olFontClass', CAPTIONFONTCLASS, 'olCapFontClass ecCapFontClass', CLOSEFONTCLASS, 'olCloseFontClass');
	}		
	
	CAL.toggleDisplay = function (id){

		if(document.getElementById(id).style.display=='none'){
			document.getElementById(id).style.display='inline'
			if(document.getElementById(id+"link") != undefined){
				document.getElementById(id+"link").style.display='none';
			}
		}else{
			document.getElementById(id).style.display='none'
			if(document.getElementById(id+"link") != undefined){
				document.getElementById(id+"link").style.display='inline';
			}
		}
	}

	CAL.goto_date_call = function (){
		var date_string = CAL.get("goto_date").value;
		var date_arr = [];
		date_arr = date_string.split("/");
		
		window.location.href = "index.php?module=Calendar&view="+CAL.pview+"&day="+date_arr[1]+"&month="+date_arr[0]+"&year="+date_arr[2];	
	}
	
	CAL.toURI = function (a){
			t=[];
			for(x in a){			
				if(!(a[x].constructor.toString().indexOf('Array') == -1)){
					for(i in a[x])
						t.push(x+"[]="+encodeURIComponent(a[x][i]));
				}else			
					t.push(x+"="+encodeURIComponent(a[x]));
			}
			return t.join("&");
	}	
	
	CAL.each = function (object, callback) {
		
		if(typeof object == "undefined")
			return;
		var name, i = 0,
		length = object.length,
		isObj = (length === undefined) || (typeof (object) === "function");
		if(isObj){
			    for (name in object) {
				if (callback.call(object[name], name, object[name]) === false) {
				    break;
				}
			    }
		}else{
			    for (; i < length;) {
				if (callback.call(object[i], i, object[i++]) === false) {
				    break;
				}
			    }
		}
	 	return object;
	}
	
	CAL.getStyle = function(oElm, strCssRule){
		var strValue = "";
		if(document.defaultView && document.defaultView.getComputedStyle){
			strValue = document.defaultView.getComputedStyle(oElm, "").getPropertyValue(strCssRule);
		}
		else if(oElm.currentStyle){
			strCssRule = strCssRule.replace(/\-(\w)/g, function (strMatch, p1){
				return p1.toUpperCase();
			});
			strValue = oElm.currentStyle[strCssRule];
		}
		return strValue;
	}	
	
	CAL.update_vcal = function(){
												
										var v = CAL.current_user_id;												
										var callback = {
											success: function(result){ 
												if (typeof GLOBAL_REGISTRY.freebusy == 'undefined') {
													GLOBAL_REGISTRY.freebusy = new Object();
												}
												if (typeof GLOBAL_REGISTRY.freebusy_adjusted == 'undefined') {
														GLOBAL_REGISTRY.freebusy_adjusted = new Object();
												}
												// parse vCal and put it in the registry using the user_id as a key:
												GLOBAL_REGISTRY.freebusy[v] = SugarVCalClient.parseResults(result.responseText, false);                  
												// parse for current user adjusted vCal
												GLOBAL_REGISTRY.freebusy_adjusted[v] = SugarVCalClient.parseResults(result.responseText, true);
												SugarWidgetScheduler.update_time();
											}												
										};
												
										var url = "vcal_server.php?type=vfb&source=outlook&user_id="+v;									
										YAHOO.util.Connect.asyncRequest('GET',url,callback,false);
	}
	
	CAL.fit_grid = function(){
		var day_width;
		var cal_width = document.getElementById("cal-grid").parentNode.offsetWidth;
		if(CAL.pview == "day")
			day_width = parseInt((cal_width - 80));
		else							
			day_width = parseInt((cal_width - 80) / 7 );
		var nodes = CAL.query("#cal-grid div.day_col");
		CAL.each(nodes, function(i,v){		
			nodes[i].style.width = day_width + "px";
		});	
		
		document.getElementById("cal-grid").style.visibility = "";
	}
	
	
	YAHOO.util.DDCAL = function(id, sGroup, config){ 
		this.cont = config.cont; 
		YAHOO.util.DDCAL.superclass.constructor.apply(this, arguments);
	}
	YAHOO.extend(YAHOO.util.DDCAL, YAHOO.util.DD, { 
		cont: null,				
		init: function(){
			YAHOO.util.DDCAL.superclass.init.apply(this, arguments);
			this.initConstraints();
			YAHOO.util.Event.on(window, 'resize', function() {
				this.initConstraints();
			}, this, true);	
		},
		initConstraints: function() { 
			var region = YAHOO.util.Dom.getRegion(this.cont);
			var el = this.getEl();
			var xy = YAHOO.util.Dom.getXY(el);
			var width = parseInt(YAHOO.util.Dom.getStyle(el, 'width'), 10);
			var height = parseInt(YAHOO.util.Dom.getStyle(el, 'height'), 10); 
			var left = xy[0] - region.left;
			var right = region.right - xy[0] - width;
			var top = xy[1] - region.top;
			var bottom = region.bottom - xy[1] - height;
			this.setXConstraint(left, right);
			this.setYConstraint(top, bottom);
		}
	});
	
	var cal_loaded = true;
