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

// $Id: customSugarCharts.js 2010-12-01 23:11:36Z lhuynh $

function loadSugarChart (chartId,jsonFilename,css,chartConfig,pageCols) {

                //Bug#45831
                if(document.getElementById(chartId) == null) {
                    return false;
                }
				var labelType, useGradients, nativeTextSupport, animate;		    	
				(function() {
				  var ua = navigator.userAgent,
					  typeOfCanvas = typeof HTMLCanvasElement,
					  nativeCanvasSupport = (typeOfCanvas == 'object' || typeOfCanvas == 'function'),
					  textSupport = nativeCanvasSupport 
						&& (typeof document.createElement('canvas').getContext('2d').fillText == 'function');
				  labelType = 'Native';
				  nativeTextSupport = labelType == 'Native';
				  useGradients = nativeCanvasSupport;
				  animate = false;
				})();
				
			var delay = (SUGAR.isIE) ? 500 : 0;
			switch(chartConfig["chartType"]) {
			case "barChart":
				var handleFailure = function(o){
				alert('fail');
					if(o.responseText !== undefined){
						alert('failed');
					}
				}	
				var handleSuccess = function(o){

					if(o.responseText !== undefined && o.responseText != "No Data"){	
					var json = eval('('+o.responseText+')');

				var properties = $jit.util.splat(json.properties)[0];	
				var marginBottom = (chartConfig["orientation"] == 'vertical' && json.values.length > 8) ? 20*4 : 20;
				//init BarChart
				var barChart = new $jit.BarChart({
				  //id of the visualization container
				  injectInto: chartId,
				  //whether to add animations
				  animate: false,
				  nodeCount: json.values.length,
				  renderBackground: chartConfig['imageExportType'] == "jpg" ? true: false,
				  backgroundColor: 'rgb(255,255,255)',
				  colorStop1: 'rgba(255,255,255,.8)',
				  colorStop2: 'rgba(255,255,255,0)',
				  shadow: {
				     enable: true,
				     size: 2	
				  },
				  //horizontal or vertical barcharts
				  orientation: chartConfig["orientation"],
				  hoveredColor: false,
				  Title: {
					text: properties['title'],
					size: 16,
					color: '#444444',
					offset: 20
				  },
				  Subtitle: {
					text: properties['subtitle'],
					size: 11,
					color: css["color"],
					offset: 20
				  },
				  Ticks: {
					enable: true,
					color: css["gridLineColor"]
				  },
				  //bars separation
				  barsOffset: (chartConfig["orientation"] == "vertical") ? 30 : 20,
				  //visualization offset
				  Margin: {
					top:20,
					left: 30,
					right: 20,
					bottom: marginBottom
				  },
				  ScrollNote: {
				  	text: (chartConfig["scroll"] && SUGAR.util.isTouchScreen()) ? "Use two fingers to scroll" : "",
				  	size: 12
				  },
				  Events: {
					enable: true,
					onClick: function(node) {  
					if(!node || SUGAR.util.isTouchScreen()) return;  
					if(node.link == 'undefined' || node.link == '') return;
					window.location.href=node.link;
					}
				  },
				  //labels offset position
				  labelOffset: 5,
				  //bars style
				  type: useGradients? chartConfig["barType"]+':gradient' : chartConfig["barType"],
				  //whether to show the aggregation of the values
				  showAggregates:true,
				  //whether to show the labels for the bars
				  showLabels:true,
				  //labels style
				  Label: {
					type: labelType, //Native or HTML
					size: 12,
					family: css["font-family"],
					color: css["color"],
					colorAlt: "#ffffff"
				  },
				  //add tooltips
				  Tips: {
					enable: true,
					onShow: function(tip, elem) {
					  if(elem.link != 'undefined' && elem.link != '') {
						drillDown = (SUGAR.util.isTouchScreen()) ? "<br><a href='"+ elem.link +"'>Click to drilldown</a>" : "<br>Click to drilldown";
					  } else {
						drillDown = "";
					  }

					  if(elem.valuelabel != 'undefined' && elem.valuelabel != undefined && elem.valuelabel != '') {
						value = "elem.valuelabel";
					  } else {
						value = "elem.value";
					  }
					  eval("tip.innerHTML = '<b>' + elem."+chartConfig["tip"]+" + '</b>: ' + "+value+" + ' - ' + elem.percentage + '%' + drillDown");
					}
				  }
				});
				//load JSON data.
				barChart.loadJSON(json);
				

				//end
				
				/*
				var list = $jit.id('id-list'),
					button = $jit.id('update'),
					orn = $jit.id('switch-orientation');
				//update json on click 'Update Data'
				$jit.util.addEvent(button, 'click', function() {
				  var util = $jit.util;
				  if(util.hasClass(button, 'gray')) return;
				  util.removeClass(button, 'white');
				  util.addClass(button, 'gray');
				  barChart.updateJSON(json2);
				});
				*/
				//dynamically add legend to list
				var list = $jit.id('legend'+chartId);
				var legend = barChart.getLegend(),
					cols = (typeof SUGAR == 'undefined' || typeof SUGAR.mySugar == 'undefined') ? 8 : 4,
					rows = Math.ceil(legend["name"].length/cols),
					table = "<table cellpadding='0' cellspacing='0' align='left'>";
				var j = 0;
				for(i=0;i<rows;i++) {
					table += "<tr>"; 
					for(td=0;td<cols;td++) {
						
						table += '<td width=\'16\' valign=\'top\'>';
						if(legend["name"][j] != undefined) {
							table += '<div class=\'query-color\' style=\'background-color:'
							  + legend["color"][j] +'\'>&nbsp;</div>';
						}
						  
						table += '</td>';
						table += '<td class=\'label\' valign=\'top\'>';
						if(legend["name"][j] != undefined) {
							table += legend["name"][j];
						}
						  
						table += '</td>';
						j++;
						}
					table += "</tr>"; 
				}
				
					table += "</table>";
				list.innerHTML = table;
				
				
				//save canvas to image for pdf consumption
				$jit.util.saveImageTest(chartId,jsonFilename,chartConfig["imageExportType"]);
		    	
		    	var firstLoad = (SUGAR.isIE) ? true: false,
		    	orgWindowWidth = document.body.offsetWidth,
		    	orgContainerDivWidth = document.getElementById(chartId).offsetWidth;
		    	
				var refreshGraph = function() {
					var newWindowWidth = document.body.offsetWidth;
					var diff = Math.abs(newWindowWidth - orgWindowWidth);		
					if(diff>20 && !firstLoad){
						barChart.resizeGraph(json,orgWindowWidth,orgContainerDivWidth,pageCols);
					}
					firstLoad = false;	
				}
				
				//refresh graph on window resize
    
				var doRefresh = function() {
					setTimeout(function() {refreshGraph()}, delay);	
				}
				
				YAHOO.util.Event.addListener(window, 'resize', function() {doRefresh()});
				
					}
				}
						
				var callback =
				{
				  success:handleSuccess,
				  failure:handleFailure,
				  argument: { foo:'foo', bar:''}
				};
				
				var request = YAHOO.util.Connect.asyncRequest('GET', jsonFilename + "?r=" + new Date().getTime(), callback);
				break;
				
			case "lineChart":
				var handleFailure = function(o){
				alert('fail');
					if(o.responseText !== undefined){
						alert('failed');
					}
				}	
				var handleSuccess = function(o){

					if(o.responseText !== undefined && o.responseText != "No Data"){	
					var json = eval('('+o.responseText+')');

				var properties = $jit.util.splat(json.properties)[0];	
				//init Linecahrt
				var lineChart = new $jit.LineChart({
				  //id of the visualization container
				  injectInto: chartId,
				  //whether to add animations
				  animate: false,
				  renderBackground: chartConfig['imageExportType'] == "jpg" ? true: false,
				  backgroundColor: 'rgb(255,255,255)',
				  colorStop1: 'rgba(255,255,255,.8)',
				  colorStop2: 'rgba(255,255,255,0)',
				  selectOnHover: false,
				  Title: {
					text: properties['title'],
					size: 16,
					color: '#444444',
					offset: 20
				  },
				  Subtitle: {
					text: properties['subtitle'],
					size: 11,
					color: css["color"],
					offset: 20
				  },
				  Ticks: {
					enable: true,
					color: css["gridLineColor"]
				  },
				  //visualization offset
				  Margin: {
					top:20,
					left: 40,
					right: 40,
					bottom: 20
				  },
				  Events: {
					enable: true,
					onClick: function(node) {  
					if(!node || SUGAR.util.isTouchScreen()) return;  
					if(node.link == 'undefined' || node.link == '') return;
					window.location.href=node.link;
					}
				  },
				  //labels offset position
				  labelOffset: 5,
				  //bars style
				  type: useGradients? chartConfig["lineType"]+':gradient' : chartConfig["lineType"],
				  //whether to show the aggregation of the values
				  showAggregates:true,
				  //whether to show the labels for the bars
				  showLabels:true,
				  //labels style
				  Label: {
					type: labelType, //Native or HTML
					size: 12,
					family: css["font-family"],
					color: css["color"],
					colorAlt: "#ffffff"
				  },
				  //add tooltips
				  Tips: {
					enable: true,
					onShow: function(tip, elem) {
					  if(elem.link != 'undefined' && elem.link != '') {
						drillDown = (SUGAR.util.isTouchScreen()) ? "<br><a href='"+ elem.link +"'>Click to drilldown</a>" : "<br>Click to drilldown";
					  } else {
						drillDown = "";
					  }

					  if(elem.valuelabel != 'undefined' && elem.valuelabel != undefined && elem.valuelabel != '') {
						var value = "elem.valuelabel";
					  } else {
						var value = "elem.value";
					  }
					  
					  if(elem.collision) {
					  	eval("var name = elem."+chartConfig["tip"]+";");
					  	var content = '<table>';
					  	
					  	for(var i=0; i<name.length; i++) {
					  		content += '<tr><td><b>' + name[i] + '</b>:</td><td> ' + elem.value[i] + ' - ' + elem.percentage[i] + '%' + '</td></tr>';
					  	}
					  	content += '</table>';
					  	tip.innerHTML = content;
					  } else {
					  	eval("tip.innerHTML = '<b>' + elem."+chartConfig["tip"]+" + '</b>: ' + "+value+" + ' - ' + elem.percentage + '%' + drillDown");	
					  }
					}
				  }
				});
				//load JSON data.
				lineChart.loadJSON(json);
				//end
				
				/*
				var list = $jit.id('id-list'),
					button = $jit.id('update'),
					orn = $jit.id('switch-orientation');
				//update json on click 'Update Data'
				$jit.util.addEvent(button, 'click', function() {
				  var util = $jit.util;
				  if(util.hasClass(button, 'gray')) return;
				  util.removeClass(button, 'white');
				  util.addClass(button, 'gray');
				  barChart.updateJSON(json2);
				});
				*/
				//dynamically add legend to list
				var list = $jit.id('legend'+chartId);
				var legend = lineChart.getLegend(),
					cols = (typeof SUGAR == 'undefined' || typeof SUGAR.mySugar == 'undefined') ? 8 : 4,
					rows = Math.ceil(legend["name"].length/cols),
					table = "<table cellpadding='0' cellspacing='0' align='left'>";
				var j = 0;
				for(i=0;i<rows;i++) {
					table += "<tr>"; 
					for(td=0;td<cols;td++) {
						
						table += '<td width=\'16\' valign=\'top\'>';
						if(legend["name"][j] != undefined) {
							table += '<div class=\'query-color\' style=\'background-color:'
							  + legend["color"][j] +'\'>&nbsp;</div>';
						}
						  
						table += '</td>';
						table += '<td class=\'label\' valign=\'top\'>';
						if(legend["name"][j] != undefined) {
							table += legend["name"][j];
						}
						  
						table += '</td>';
						j++;
						}
					table += "</tr>"; 
				}
				
					table += "</table>";
				list.innerHTML = table;
				
				
				//save canvas to image for pdf consumption
				$jit.util.saveImageTest(chartId,jsonFilename,chartConfig["imageExportType"]);

		    	var firstLoad = (SUGAR.isIE) ? true: false,
		    	orgWindowWidth = document.body.offsetWidth,
		    	orgContainerDivWidth = document.getElementById(chartId).offsetWidth;
		    	
				var refreshGraph = function() {
					var newWindowWidth = document.body.offsetWidth;
					var diff = Math.abs(newWindowWidth - orgWindowWidth);		
					if(diff>20 && !firstLoad){
						lineChart.resizeGraph(json,orgWindowWidth,orgContainerDivWidth,pageCols);
					}
					firstLoad = false;	
				}
				
				//refresh graph on window resize
    
				var doRefresh = function() {
					setTimeout(function() {refreshGraph()}, delay);	
				}
				
				YAHOO.util.Event.addListener(window, 'resize', function() {doRefresh()});
				
					}
				}
						
				var callback =
				{
				  success:handleSuccess,
				  failure:handleFailure,
				  argument: { foo:'foo', bar:''}
				};
				
				var request = YAHOO.util.Connect.asyncRequest('GET', jsonFilename + "?r=" + new Date().getTime(), callback);
				break;
			
				
			case "pieChart":

				var handleFailure = function(o){
				alert('fail');
					if(o.responseText !== undefined){
						alert('failed');
					}
				}	
				var handleSuccess = function(o){

					if(o.responseText !== undefined){			
					var json = eval('('+o.responseText+')');
					var properties = $jit.util.splat(json.properties)[0];	

						//init BarChart
				var pieChart = new $jit.PieChart({
				  //id of the visualization container
				  injectInto: chartId,
				  //whether to add animations
				  animate: false,
				  renderBackground: chartConfig['imageExportType'] == "jpg" ? true: false,
				  backgroundColor: 'rgb(255,255,255)',
				  colorStop1: 'rgba(255,255,255,.8)',
				  colorStop2: 'rgba(255,255,255,0)',	
				  labelType: properties['labels'],
				  hoveredColor: false,
				  //offsets
				  offset: 50,
				  sliceOffset: 0,
				  labelOffset: 30,
				  //slice style
				  type: useGradients? chartConfig["pieType"]+':gradient' : chartConfig["pieType"],
				  //whether to show the labels for the slices
				  showLabels:true,
				  Title: {
					text: properties['title'],
					size: 16,
					color: '#444444',
					offset: 20
				  },
				  Subtitle: {
					text: properties['subtitle'],
					size: 11,
					color: css["color"],
					offset: 20
				  },
				  Margin: {
					top:20,
					left: 20,
					right: 20,
					bottom: 20
				  },
				  Events: {
					enable: true,
					onClick: function(node) {  
					if(!node || SUGAR.util.isTouchScreen()) return;  
					if(node.link == 'undefined' || node.link == '') return;
					window.location.href=node.link;
					}
				  },
				  //label styling
				  Label: {
					type: labelType, //Native or HTML
					size: 12,
					family: css["font-family"],
					color: css["color"]
				  },
				  //enable tips
				  Tips: {
					enable: true,
					onShow: function(tip, elem) {
					  if(elem.link != 'undefined' && elem.link != '') {
						drillDown = (SUGAR.util.isTouchScreen()) ? "<br><a href='"+ elem.link +"'>Click to drilldown</a>" : "<br>Click to drilldown";
					  } else {
						drillDown = "";
					  }
					  
					  if(elem.valuelabel != 'undefined' && elem.valuelabel != undefined && elem.valuelabel != '') {
						value = "elem.valuelabel";
					  } else {
						value = "elem.value";
					  }
					   eval("tip.innerHTML = '<b>' + elem.label + '</b>: ' + "+ value +" + ' - ' + elem.percentage + '%' + drillDown");
					}
				  }
				});
				//load JSON data.
				pieChart.loadJSON(json);
				//end
				//dynamically add legend to list
				var list = $jit.id('legend'+chartId);
				var legend = pieChart.getLegend(),
					cols = (typeof SUGAR == 'undefined' || typeof SUGAR.mySugar == 'undefined') ? 8 : 4,
					rows = Math.ceil(legend["name"].length/cols);
					table = "<table cellpadding='0' cellspacing='0' align='left'>";
				var j = 0;
				for(i=0;i<rows;i++) {
					table += "<tr>"; 
					for(td=0;td<cols;td++) {
						
						table += '<td width=\'16\' valign=\'top\'>';
						if(legend["name"][j] != undefined) {
							table += '<div class=\'query-color\' style=\'background-color:'
							  + legend["color"][j] +'\'>&nbsp;</div>';
						}
						  
						table += '</td>';
						table += '<td class=\'label\' valign=\'top\'>';
						if(legend["name"][j] != undefined) {
							table += legend["name"][j];
						}
						  
						table += '</td>';
						j++;
						}
					table += "</tr>"; 
				}
				
					table += "</table>";
				list.innerHTML = table;
				
								
				//save canvas to image for pdf consumption
				$jit.util.saveImageTest(chartId,jsonFilename,chartConfig["imageExportType"]);
			
		    	var firstLoad = (SUGAR.isIE) ? true: false,
		    	orgWindowWidth = document.body.offsetWidth,
		    	orgContainerDivWidth = document.getElementById(chartId).offsetWidth;
		    	
				var refreshGraph = function() {
					var newWindowWidth = document.body.offsetWidth;
					var diff = Math.abs(newWindowWidth - orgWindowWidth);		
					if(diff>20 && !firstLoad){
						pieChart.resizeGraph(json,orgWindowWidth,orgContainerDivWidth,pageCols);
					}
					firstLoad = false;	
				}
				
				//refresh graph on window resize
    
				var doRefresh = function() {
					setTimeout(function() {refreshGraph()}, delay);	
				}
				
				YAHOO.util.Event.addListener(window, 'resize', function() {doRefresh()});
				
				
				}
			}
								
				var callback =
				{
				  success:handleSuccess,
				  failure:handleFailure,
				  argument: { foo:'foo', bar:''}
				};
				
				var request = YAHOO.util.Connect.asyncRequest('GET', jsonFilename + "?r=" + new Date().getTime(), callback);
							
				break;
				
				
			case "funnelChart":

				var handleFailure = function(o){
				alert('fail');
					if(o.responseText !== undefined){
						alert('failed');
					}
				}	
				var handleSuccess = function(o){

					if(o.responseText !== undefined && o.responseText != "No Data"){	
					var json = eval('('+o.responseText+')');

				var properties = $jit.util.splat(json.properties)[0];	

				//init Funnel Chart
				var funnelChart = new $jit.FunnelChart({
				  //id of the visualization container
				  injectInto: chartId,
				  //whether to add animations
				  animate: false,
				  renderBackground: chartConfig['imageExportType'] == "jpg" ? true: false,
				  backgroundColor: 'rgb(255,255,255)',
				  colorStop1: 'rgba(255,255,255,.8)',
				  colorStop2: 'rgba(255,255,255,0)',	
				  //orientation setting should not be changed
				  orientation: "vertical",
				  hoveredColor: false,
				  Title: {
					text: properties['title'],
					size: 16,
					color: '#444444',
					offset: 20
				  },
				  Subtitle: {
					text: properties['subtitle'],
					size: 11,
					color: css["color"],
					offset: 20
				  },
				  //segment separation
				  segmentOffset: 20,
				  //visualization offset
				  Margin: {
					top:20,
					left: 20,
					right: 20,
					bottom: 20
				  },
				  Events: {
					enable: true,
					onClick: function(node) {  
					if(!node || SUGAR.util.isTouchScreen()) return;  
					if(node.link == 'undefined' || node.link == '') return;
					window.location.href=node.link;
					}
				  },
				  //labels offset position
				  labelOffset: 10,
				  //bars style
				  type: useGradients? chartConfig["funnelType"]+':gradient' : chartConfig["funnelType"],
				  //whether to show the aggregation of the values
				  showAggregates:true,
				  //whether to show the labels for the bars
				  showLabels:true,
				  //labels style
				  Label: {
					type: labelType, //Native or HTML
					size: 12,
					family: css["font-family"],
					color: css["color"],
					colorAlt: "#ffffff"
				  },
				  //add tooltips
				  Tips: {
					enable: true,
					onShow: function(tip, elem) {
					  if(elem.link != 'undefined' && elem.link != '') {
						drillDown = (SUGAR.util.isTouchScreen()) ? "<br><a href='"+ elem.link +"'>Click to drilldown</a>" : "<br>Click to drilldown";
					  } else {
						drillDown = "";
					  }

					  if(elem.valuelabel != 'undefined' && elem.valuelabel != undefined && elem.valuelabel != '') {
						value = "elem.valuelabel";
					  } else {
						value = "elem.value";
					  }
					  eval("tip.innerHTML = '<b>' + elem."+chartConfig["tip"]+" + '</b>: ' + "+value+"  + ' - ' + elem.percentage + '%' +  drillDown");
					}
				  }
				});
				//load JSON data.
				funnelChart.loadJSON(json);
				//end
				
				/*
				var list = $jit.id('id-list'),
					button = $jit.id('update'),
					orn = $jit.id('switch-orientation');
				//update json on click 'Update Data'
				$jit.util.addEvent(button, 'click', function() {
				  var util = $jit.util;
				  if(util.hasClass(button, 'gray')) return;
				  util.removeClass(button, 'white');
				  util.addClass(button, 'gray');
				  barChart.updateJSON(json2);
				});
				*/
				//dynamically add legend to list
				var list = $jit.id('legend'+chartId);
				var legend = funnelChart.getLegend(),
					cols = (typeof SUGAR == 'undefined' || typeof SUGAR.mySugar == 'undefined') ? 8 : 4,
					rows = Math.ceil(legend["name"].length/cols);
					table = "<table cellpadding='0' cellspacing='0' align='left'>";
				var j = 0;
				for(i=0;i<rows;i++) {
					table += "<tr>"; 
					for(td=0;td<cols;td++) {
						
						table += '<td width=\'16\' valign=\'top\'>';
						if(legend["name"][j] != undefined) {
							table += '<div class=\'query-color\' style=\'background-color:'
							  + legend["color"][j] +'\'>&nbsp;</div>';
						}
						  
						table += '</td>';
						table += '<td class=\'label\' valign=\'top\'>';
						if(legend["name"][j] != undefined) {
							table += legend["name"][j];
						}
						  
						table += '</td>';
						j++;
						}
					table += "</tr>"; 
				}
				
					table += "</table>";
				list.innerHTML = table;
								
				//save canvas to image for pdf consumption
				$jit.util.saveImageTest(chartId,jsonFilename,chartConfig["imageExportType"]);
				
		    	var firstLoad = (SUGAR.isIE) ? true: false,
		    	orgWindowWidth = document.body.offsetWidth,
		    	orgContainerDivWidth = document.getElementById(chartId).offsetWidth;
		    	
				var refreshGraph = function() {
					var newWindowWidth = document.body.offsetWidth;
					var diff = Math.abs(newWindowWidth - orgWindowWidth);		
					if(diff>20 && !firstLoad){
						funnelChart.resizeGraph(json,orgWindowWidth,orgContainerDivWidth,pageCols);
					}
					firstLoad = false;	
				}
				
				//refresh graph on window resize
    
				var doRefresh = function() {
					setTimeout(function() {refreshGraph()}, delay);	
				}
				
				YAHOO.util.Event.addListener(window, 'resize', function() {doRefresh()});
				
				
				}
			}
						
				var callback =
				{
				  success:handleSuccess,
				  failure:handleFailure,
				  argument: { foo:'foo', bar:''}
				};
				
				var request = YAHOO.util.Connect.asyncRequest('GET', jsonFilename + "?r=" + new Date().getTime(), callback);
				break;
				
				
				
			case "gaugeChart":

				var handleFailure = function(o){
				alert('fail');
					if(o.responseText !== undefined){
						alert('failed');
					}
				}	
				var handleSuccess = function(o){

					if(o.responseText !== undefined){			
					var json = eval('('+o.responseText+')');
					var properties = $jit.util.splat(json.properties)[0];	

						//init Gauge Chart
				var gaugeChart = new $jit.GaugeChart({
				  //id of the visualization container
				  injectInto: chartId,
				  //whether to add animations
				  animate: false,
				  renderBackground: chartConfig['imageExportType'] == "jpg" ? true: false,
				  backgroundColor: 'rgb(255,255,255)',
				  colorStop1: 'rgba(255,255,255,.8)',
				  colorStop2: 'rgba(255,255,255,0)',
				  labelType: properties['labels'],
				  hoveredColor: false,
				  Title: {
					text: properties['title'],
					size: 16,
					color: '#444444',
					offset: 20
				  },
				  Subtitle: {
					text: properties['subtitle'],
					size: 11,
					color: css["color"],
					offset: 5
				  },
				  //offsets
				  offset: 20,
				  gaugeStyle: {
					backgroundColor: '#aaaaaa',
					borderColor: '#999999',
					needleColor: 'rgba(255,0,0,.8)',
					borderSize: 4,
					positionFontSize: 24,
					positionOffset: 2
				  },
				  //slice style
				  type: useGradients? chartConfig["gaugeType"]+':gradient' : chartConfig["gaugeType"],
				  //whether to show the labels for the slices
				  showLabels:true,
				  Events: {
					enable: true,
					onClick: function(node) {  
					if(!node || SUGAR.util.isTouchScreen()) return;  
					if(node.link == 'undefined' || node.link == '') return;
					window.location.href=node.link;
					}
				  },
				  //label styling
				  Label: {
					type: labelType, //Native or HTML
					size: 12,
					family: css["font-family"],
					color: css["color"]
				  },
				  //enable tips
				  Tips: {
					enable: true,
					onShow: function(tip, elem) {
					  if(elem.link != 'undefined' && elem.link != '') {
						drillDown = (SUGAR.util.isTouchScreen()) ? "<br><a href='"+ elem.link +"'>Click to drilldown</a>" : "<br>Click to drilldown";
					  } else {
						drillDown = "";
					  }
					  if(elem.valuelabel != 'undefined' && elem.valuelabel != undefined && elem.valuelabel != '') {
						value = "elem.valuelabel";
					  } else {
						value = "elem.value";
					  }
					   eval("tip.innerHTML = '<b>' + elem.label + '</b>: ' + "+ value +" + drillDown");
					}
				  }
				});
				//load JSON data.
				gaugeChart.loadJSON(json);

				
				var list = $jit.id('legend'+chartId);
				var legend = gaugeChart.getLegend(),
					cols = (typeof SUGAR == 'undefined' || typeof SUGAR.mySugar == 'undefined') ? 8 : 4,
					rows = Math.ceil(legend["name"].length/cols);
					table = "<table cellpadding='0' cellspacing='0' align='left'>";
				var j = 1;
				for(i=0;i<rows;i++) {
					table += "<tr>"; 
					for(td=0;td<cols;td++) {
						
						table += '<td width=\'16\' valign=\'top\'>';
						if(legend["name"][j] != undefined) {
							table += '<div class=\'query-color\' style=\'background-color:'
							  + legend["color"][j] +'\'>&nbsp;</div>';
						}
						  
						table += '</td>';
						table += '<td class=\'label\' valign=\'top\'>';
						if(legend["name"][j] != undefined) {
							table += legend["name"][j];
						}
						  
						table += '</td>';
						j++;
						}
					table += "</tr>"; 
				}
				
					table += "</table>";
				list.innerHTML = table;
				
								
				//save canvas to image for pdf consumption
				$jit.util.saveImageTest(chartId,jsonFilename,chartConfig["imageExportType"]);
				
		    	var firstLoad = (SUGAR.isIE) ? true: false,
		    	orgWindowWidth = document.body.offsetWidth,
		    	orgContainerDivWidth = document.getElementById(chartId).offsetWidth;
		    	
				var refreshGraph = function() {
					var newWindowWidth = document.body.offsetWidth;
					var diff = Math.abs(newWindowWidth - orgWindowWidth);		
					if(diff>20 && !firstLoad){
						gaugeChart.resizeGraph(json,orgWindowWidth,orgContainerDivWidth,pageCols);
					}
						firstLoad = false;	
				}
				
				//refresh graph on window resize
    
				var doRefresh = function() {
					setTimeout(function() {refreshGraph()}, delay);	
				}
				
				YAHOO.util.Event.addListener(window, 'resize', function() {doRefresh()});
				
				
				}
			}
								
				var callback =
				{
				  success:handleSuccess,
				  failure:handleFailure,
				  argument: { foo:'foo', bar:''}
				};
				
				var request = YAHOO.util.Connect.asyncRequest('GET', jsonFilename + "?r=" + new Date().getTime(), callback);
							
				break;
				
			}
		}