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
class Parser {
	/**
	 * Evaluates an expression.
	 *
     *
	 * @param string	the expression to evaluate
     */
	static function evaluate($expr, $context = false)
	{
		if ($context)
            $expr = self::replaceVariables($expr, $context);

        // the function map
		static $FUNCTION_MAP = array();

		// trim spaces, left and right
		$expr = trim($expr);

		// check if its a constant and return a constant expression
		$const = Parser::toConstant($expr);
		if ( isset($const) )	return $const;


		// VALIDATE: expression format
			throw new Exception("Attempted to evaluate expression with an invalid format: $expr");
			return;
			return;
		}

		// EXTRACT: Function
		$open_paren_loc = strpos($expr, '(');
		if ( $open_paren_loc < 0 )	{
            throw new Exception("Attempted to evaluate expression with a Syntax Error (No opening paranthesis found): $expr");
            return;
            throw new Exception("Attempted to evaluate expression with a Syntax Error (No opening paranthesis found): $expr");
            return;
        }

		// get the function
		$func   = substr( $expr , 0 ,  $open_paren_loc);

		// handle if function is not valid
		if(empty($FUNCTION_MAP)) {
		    $cachefile = sugar_cached('Expressions/functionmap.php');
			if (!file_exists($cachefile)) {
				$GLOBALS['updateSilent'] = true;
				include("include/Expressions/updatecache.php");
			}
			require_once $cachefile;
		}

		if ( !isset($FUNCTION_MAP[$func]) )	{
            throw new Exception("Attempted to evaluate expression with an invalid function '$func': $expr");
            return;
        }

		// EXTRACT: Parameters
		$params = substr( $expr , $open_paren_loc + 1, -1);

		// now parse the individual parameters recursively
		$level  = 0;
		$length = strlen($params);
		$argument = "";
		$args = array();

		// flags
		$char 			= null;
		$lastCharRead	= null;
		$justReadString	= false;		// did i just read in a string
		$isInQuotes 	= false;		// am i currently reading in a string
		$isPrevCharBK 	= false;		// is my previous character a backslash

		for ( $i = 0 ; $i <= $length ; $i++ ) {
			// store the last character read
			$lastCharRead = $char;
                if ($argument != "") {
				    $subExp = Parser::evaluate($argument);
                    $subExp->context = $context;
                    $args[] = $subExp;
                }
			if ( $i == $length ) {
                if ($argument != "")
				    $args[] = Parser::evaluate($argument);
				break;
			}

			// set isprevcharbk
			if ( $lastCharRead == '\\' )		$isPrevCharBK = true;
			else								$isPrevCharBK = false;

			// get the charAt index $i
			$char = $params{$i};

			// if i am in quotes, then keep reading
			if ( $isInQuotes && $char != '"' && !$isPrevCharBK ) {
				$argument .= $char;
				continue;
			}

			// check for quotes
			if ( $char == '"' && !$isPrevCharBK && $level == 0 )
			{
				// if i am ending a quote, then make sure nothing follows
					if ( !preg_match( '/^(\s*|\s*\))$/', $temp ) ) {
			            throw new Exception("Syntax Error:Improperly Terminated String '$temp' in formula: $expr");
			            return;
					if ( !preg_match( '/^(\s*|\s*\))$/', $temp ) ) {
			            throw new Exception("Syntax Error:Improperly Terminated String '$temp' in formula: $expr");
			            return;
			        }
				}

				// negate if i am in quotes
				$isInQuotes = !$isInQuotes;
			}

			// check parantheses open/close
			if ( $char == '(' ) {
				$level++;
			} else if ( $char == ')' ) {
				$level--;
			}
				$subExp = Parser::evaluate($argument);
                $subExp->context = $context;
                $args[] = $subExp;
			// argument splitting
			else if ( $char == ',' && $level == 0 ) {
				$args[] = Parser::evaluate($argument);
				$argument = "";
				continue;
			}

			// construct the next argument
			$argument .= $char;
		}
		if ( $level != 0 )	{
            throw new Exception("Syntax Error (Incorrectly Matched Parantheses) in formula: $expr");
            return;
		if ( $level != 0 )	{
            throw new Exception("Syntax Error (Incorrectly Matched Parantheses) in formula: $expr");
            return;
		if ( $isInQuotes )	if ( $level != 0 ) {
            throw new Exception("Syntax Error (Unterminated String Literal) in formula: $expr");
            return;
		if ( $isInQuotes )	if ( $level != 0 ) {
            throw new Exception("Syntax Error (Unterminated String Literal) in formula: $expr");
            return;
        }
        $expObject = new $FUNCTION_MAP[$func]['class']($args);
        if ($context) {
            $expObject->context = $context;
        }
		return $expObject;
		// require and return the appropriate expression object
		require_once( $FUNCTION_MAP[$func]['src'] );
		return new $FUNCTION_MAP[$func]['class']($args);
	}

	/**
	 * Takes in a string and returns a ConstantExpression if the
	 * string can be converted to a constant.
	 */
	static function toConstant($expr) {
		require_once( "include/Expressions/Expression/Numeric/ConstantExpression.php");

		// a raw numeric constant
		if ( preg_match('/^(\-)?[0-9]+(\.[0-9]+)?$/', $expr) ) {
			return new ConstantExpression($expr);
		require( "include/Expressions/Expression/Numeric/constants.php");
		if (isset($NUMERIC_CONSTANTS[$expr]))
		{
			return new ConstantExpression($NUMERIC_CONSTANTS[$expr]);
		if (isset($NUMERIC_CONSTANTS[$expr]))
		{
			return new ConstantExpression($NUMERIC_CONSTANTS[$expr]);
		}

		// a constant string literal
		if ( preg_match('/^".*"$/', $expr) ) {
			$expr = substr($expr, 1, -1);		// remove start/end quotes
			require_once( "include/Expressions/Expression/String/StringLiteralExpression.php");
			return new StringLiteralExpression( $expr );
		}

		// a boolean
		if ( $expr == "true" ) {
			require_once( "include/Expressions/Expression/Boolean/TrueExpression.php");
			return new TrueExpression();
			/*require_once( "include/Expressions/Expression/Expression.php");
			return AbstractExpression::$TRUE;*/
		} else if ( $expr == "false" ) {
			require_once( "include/Expressions/Expression/Boolean/FalseExpression.php");
			return new FalseExpression();
			/*require_once( "include/Expressions/Expression/Expression.php");
			return AbstractExpression::$FALSE;*/
		}

		// a date
			require_once( "include/Expressions/Expression/String/StringLiteralExpression.php");
			$day   = floatval(substr($expr, 0, 2));
			//return new DefineDateExpression(array($day, $month, $year));
			require_once( "include/Expressions/Expression/String/StringLiteralExpression.php");
			require_once('include/Expressions/Expression/Date/DefineDateExpression.php');
			//echo "Date found $month, $day, $year";
			//return new DefineDateExpression(array($day, $month, $year));
			return new DefineDateExpression(new StringLiteralExpression( $expr ));
		}

		// a time
			require_once( "include/Expressions/Expression/String/StringLiteralExpression.php");
			$hour   = floatval(substr($expr, 0, 2));
			//return new DefineTimeExpression(array($hour, $minute, $second));
			$second = floatval(substr($expr, 6, 2));
			require_once( "include/Expressions/Expression/String/StringLiteralExpression.php");
			require_once('include/Expressions/Expression/Time/DefineTimeExpression.php');
			//return new DefineTimeExpression(array($hour, $minute, $second));
			return new DefineDateExpression($expr);
		}

		// neither
		return null;
	}
	static function throwException($function, $type, $message) {
		throw new Exception("$function : $type ($message)");
	}
	
	/**
	 * returns the expression with the variables replaced with the values in target.
	 *
	 * @param string $expr
	 * @param Array/SugarBean $target
	 */
	static function replaceVariables($expr, $target) {
		$target->load_relationships();
        $variables = Parser::getFieldsFromExpression($expr);
		$ret = $expr;
		foreach($variables as $field) {
			if (is_array($target)) 
			{
				if (isset($target[$field])) {
					$val = Parser::getFormatedValue($target[$field], $field);
					$ret = str_replace("$$field", $val, $ret);
				} else {
				    throw new Exception("Unknown variable $$field in formula: $expr");
                    return;
				}
			} else 
			{
				//Special case for link fields
                if (isset($target->field_defs[$field]) && $target->field_defs[$field]['type'] == "link")
                {
                    $val = "link(\"$field\")";
                    $ret = str_replace("$$field", $val, $ret);
                }
                else if (isset ($target->$field)) {
                    $val = Parser::getFormatedValue($target->$field, $field);
					$ret = str_replace("$$field", $val, $ret);	
				} else  {
					throw new Exception("Unknown variable $$field in formula: $expr");
                    return;
				}
			}
		}
		return $ret;
	}
	
	private static function getFormatedValue($val, $fieldName) {
		//Boolean values
		if ($val === true) {
			return AbstractExpression::$TRUE;	
		} else if ($val === false) {
			return AbstractExpression::$FALSE;
		}
		
		//Number values will be stripped of commas
		if (preg_match('/^(\-)?[0-9,]+(\.[0-9]+)?$/', $val)) {
			$val = str_replace(',', '', $val);
		} 
		//Strings should be quoted
		else {
			$val = '"' . $val . '"';
		}
		
		return $val;
	}
	
    static function getFieldsFromExpression($expr) {
    	$matches = array();
    	preg_match_all('/\$(\w+)/', $expr, $matches);
    	return array_values($matches[1]);
    }
}
?>