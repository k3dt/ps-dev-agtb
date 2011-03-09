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
require_once("include/Expressions/Actions/AbstractAction.php");

class VisibilityAction extends AbstractAction{
	protected $targetField = array();
	protected $expression = "";
	
	function VisibilityAction($params) {
        $this->params = $params;
		$this->targetField = $params['target'];
		$this->expression = $params['value'];
		$this->view = isset($params['view']) ? $params['view'] : "";
	}
	
	/**
	 * Returns the javascript class equavalent to this php class
	 *
	 * @return string javascript.
	 */
	static function getJavascriptClass() {
		return "
		SUGAR.forms.VisibilityAction = function(target, expr, view)
		{
			this.target = target;
			this.expr	= 'cond(' + expr + ', \"\", \"none\")';
			this.view = view;
			if (!SUGAR.forms.VisibilityAction.initialized)
			{
			    var head = document.getElementsByTagName('head')[0];
                var cssdef = 'td.vis_action_hidden * { visibility:hidden}'
			    var newStyle = document.createElement('style');
                newStyle.setAttribute('type', 'text/css');
				if (newStyle.styleSheet)
			        newStyle.styleSheet.cssText = cssdef;
			    else
			        newStyle.innerHTML = cssdef;
			    head.appendChild(newStyle);
                SUGAR.forms.VisibilityAction.initialized = true;
			}
		}
		
		/**
		 * Triggers this dependency to be re-evaluated again.
		 */
		SUGAR.util.extend(SUGAR.forms.VisibilityAction, SUGAR.forms.AbstractAction, {
		
			/**
			 * Triggers the style dependencies.
			 */
			exec: function(context)
			{
				if (typeof(context) == 'undefined')
                    context = this.context;
                try {
                    var Dom = YAHOO.util.Dom;
                    var exp = this.evalExpression(this.expr, context);
					var hide =  exp == 'none' || exp == 'hidden';
					var target = SUGAR.forms.AssignmentHandler.getElement(this.target);
					if (target != null) {
					    var inv_class = 'vis_action_hidden';
						var inputTD = Dom.getAncestorByTagName(target, 'TD');
						var labelTD = Dom.getPreviousSiblingBy(inputTD, function(e){
							if (e.tagName == 'TD') return true;
							return false;
						});
						this.wrapContent(labelTD);
						this.wrapContent(inputTD);
						var wasHidden = Dom.hasClass(labelTD, inv_class);
						if (hide)
						{
						    Dom.addClass(labelTD, inv_class);
						    Dom.addClass(inputTD, inv_class);
						}
						else
						{
						    Dom.removeClass(labelTD, inv_class);
						    Dom.removeClass(inputTD, inv_class);
						    if (wasHidden && this.view == 'EditView')
						        SUGAR.forms.FlashField(target);
						}
					}
				} catch (e) {if (console && console.log) console.log(e);}
			},
			//we need to wrap plain text nodes in a span in order to hide the contents without hiding the TD itesef
			wrapContent: function(el)
			{
			    if (el && this.containsPlainText(el))
			    {
			        var span = document.createElement('SPAN');
			        var nodes = [];
			        for(var i = 0; i < el.childNodes.length ; i++)
                    {
                        nodes[i] = el.childNodes[i];
                    }
                    for(var i = 0 ; i < nodes.length; i++)
                    {
                        span.appendChild(nodes[i]);
                    }
			        el.appendChild(span);
			    }
			},
			containsPlainText: function(el)
			{
                for(var i = 0; i < el.childNodes.length; i++)
                {
                    var node = el.childNodes[i];
                    if (node.nodeName == '#text' && YAHOO.lang.trim(node.textContent) != '') {
                        return true;
                    }
                }
			    return false;
			}

		});";
	}

	/**
	 * Returns the javascript code to generate this actions equivalent. 
	 *
	 * @return string javascript.
	 */
	function getJavascriptFire() {
		return "new SUGAR.forms.VisibilityAction('{$this->targetField}','{$this->expression}', '{$this->view}')";
	}
	
	/**
	 * Applies the Action to the target.
	 *
	 * @param SugarBean $target
	 */
	function fire(&$target) {
		require_once("include/Expressions/Expression/AbstractExpression.php");
		$result = Parser::evaluate($this->expression, $target)->evaluate();
		if ($result === AbstractExpression::$FALSE) {
			$target->field_defs[$this->targetField]['hidden'] = true;
		} else 
		{
			$target->field_defs[$this->targetField]['hidden'] = false;
		}
	}
	
	static function getActionName() {
		return "SetVisibility";
	}
	
}