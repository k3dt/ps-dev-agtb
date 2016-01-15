
//FILE SUGARCRM flav=int ONLY

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


var msgPanel
var	msg_panel
var c=0
var s=0
var t
var currStage
var timeOutWindowMultiplier = 1
var timeOutWindow = 60

				function populateTableColumns(){
	 	            var args = {    width:'300px',
		                            modal:true,
		                            fixedcenter: true,
		                            constraintoviewport: false,
		                            underlay:'shadow',
		                            close:false,
		                            draggable:true,

		                            effect:{effect:YAHOO.widget.ContainerEffect.FADE, duration:.5}
		                           } ;
            msg_panel = new YAHOO.widget.Panel('p_msg', args);
			var s = 0;
            msg_panel.setBody(document.getElementById('checkingDiv').innerHTML);
            currMsg = document.getElementById('table_id').value;
            if(currMsg=='select_a_table') {
            	currMsg = 'Cleaning Relationships and Columns';
            }
            else{
            	currMsg = 'Populating for '+currMsg;
            }
            timedCount(currMsg);
            //timedCount();
            //msg_panel.setFooter('Time Elapsed '+s);
            msg_panel.render(document.body);
             msgPanel = msg_panel;
	 	var callback = {
	     success:function(r) {
		  msgPanel.hide();
		  document.getElementById('schemaResults').style.display = 'none';
		  //tabColumns.innerHTML=r.responseText;
		  SUGAR.util.evalScript(document.getElementById('tabColumns').innerHTML=r.responseText);
	     }
	}
	var selectedTable = document.getElementById('table_id').value;
	msgPanel.show;
	postData = 'selectedTable=' + selectedTable+ '&module=Administration&action=populateColumns&to_pdf=1';
	YAHOO.util.Connect.asyncRequest('POST', 'index.php', callback, postData);
   }

	function populateTableRelatonships(){
	 var callback = {
	     success:function(r) {
		  //var oldhtml = document.getElementById('rels').innerHTML;
		  //document.getElementById('rels').innerHTML = r.responseText;
		  //rels.innerHTML = r.responseText;
		  //alert(r.responseText);
		  SUGAR.util.evalScript(document.getElementById('rels').innerHTML=r.responseText);
	     }
	}
	var selectedTable = document.getElementById('table_id').value;
	postData = 'selectedTable=' + selectedTable+ '&module=Administration&action=populateRelationships&to_pdf=1';
	YAHOO.util.Connect.asyncRequest('POST', 'index.php', callback, postData);
	}

	function checkTablesColumns(){
		if(document.getElementById('table_id').value == 'select_a_table'){
			alert('Select A Table First');
		}
		else{
			//launch the check and ajax call etc...
            var args = {    width:'300px',
		                            modal:true,
		                            fixedcenter: true,
		                            constraintoviewport: false,
		                            underlay:'shadow',
		                            close:false,
		                            draggable:true,

		                            effect:{effect:YAHOO.widget.ContainerEffect.FADE, duration:.5}
		                           } ;
            msg_panel = new YAHOO.widget.Panel('p_msg', args);
			var s = 0;
           	//If we haven't built our panel using existing markup,
            //we can set its content via script:
            //msg_panel.setHeader('Schema Check going');

            msg_panel.setBody(document.getElementById('checkingDiv').innerHTML);
            currMsg = 'Schema Check going for '+document.getElementById('table_id').value;
           if(document.getElementById('relation_id') != null){
	            currMsg = document.getElementById('relation_id').value;
	            if(currMsg==document.getElementById('table_id').value+'_'+'Relationships') {
	            	currMsg = 'Schema Check going for '+document.getElementById('table_id').value;
	            }
	            else{
	            	currMsg = 'Schema Check going for '+document.getElementById('table_id').value+'('+document.getElementById('relation_id').value+')';
	            }
            }
            timedCount(currMsg);
            //timedCount();
            //msg_panel.setFooter('Time Elapsed '+s);
            msg_panel.render(document.body);
            msgPanel = msg_panel;
			 var callback = {
			     success:function(r) {
	               //alert(r.responseText);
	               msgPanel.hide();

				   //alert(r.responseText);
				   //SUGAR.util.evalScript(document.getElementById('relation_id').innerHTML=r.responseText);
				   document.getElementById('schemaResults').style.display='';
				   document.getElementById('scanResult').value = r.responseText;
			     }
			}
			var selectedTable = document.getElementById('table_id').value;
			var selectedRelationship;
			if(document.getElementById('relation_id') != null){
				selectedRelationship = document.getElementById('relation_id').value;
			}
			else{
				selectedRelationship = document.getElementById('table_id').value+'_relationships';
			}
			var tableAndRelation = new Array();
			tableAndRelation[0] = selectedTable;
			tableAndRelation[1] = selectedRelationship;
			//alert(tableAndRelation);

            msgPanel.show;
			postData = 'tableAndRelation=' + YAHOO.lang.JSON.stringify(tableAndRelation)+'&module=Administration&action=checkTableRelationships&to_pdf=1';
			YAHOO.util.Connect.asyncRequest('POST', 'index.php', callback, postData);
		 }

	}

	function checkRelationShips(){
		if(document.getElementById('relation_id').value == 'select_a_relationship'){
			alert('Select A Relationship First');
		}
		else{
			//launch the check and ajax call etc...
		}
	}
function timedCount(currStage)
{
      constM = currStage;
      //msg_panel.setFooter(s);
      //cStage = currStage+"        "+s;
      cStage = currStage
      msg_panel.setHeader(cStage);
      //msg_panel.setFooter(s);
    	c=c+1
		s=c

		//timeOutWindowMultiples = timeOutWindowMultiplier*timeOutWindow
		//if(c == timeOutWindowMultiples){
		  //updateUpgradeStepTime(timeOutWindow)
		  //timeOutWindowMultiplier = timeOutWindowMultiplier+1
		//}

		if(c<10){
		 	s="0"+c
		}

	  if(c>=60 && c<3600){
			 m=1
			 while(c>=((m+1)*60)){
			    m=m+1
			  }
			 secs= (c-(m*60))
			 if(m < 10){
			     m = "0"+m
			  }
			  if(secs < 10){
			     secs = "0"+secs
			  }
			  s=m+":"+ secs
		 }
		 if(c>=3600){
			  h=1;
			  while(c>=((h+1)*3600)){
			    h=h+1;
			   }
			  r= c-(h*3600)
			  m = 0
			  secs = 0
			  if(r>=60){
				 m=1;
				  while(r>=((m+1)*60)){
				     m=m+1;
				  }
				  secs =  (r-(m*60))
			    }
			    if(h < 10){
			       h = "0"+h
			     }
			     if(m < 10){
			       m = "0"+m
			     }
			     if(secs <10){
				     secs = "0"+ secs
				  }
			  s=h+":"+m+":"+ secs
		   }

	t=setTimeout("timedCount(constM)",1000)
}

