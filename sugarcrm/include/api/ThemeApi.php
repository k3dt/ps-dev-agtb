<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('include/lessphp/lessc.inc.php');

class ThemeApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'downloadTheme' => array(
                'reqType' => 'GET',
                'path' => array('bootstrap.css'),
                'pathVars' => array(''),
                'method' => 'generateBootstrapCss',
                'shortHelp' => 'Generate the bootstrap.css file',
                'longHelp' => 'include/api/html/module_relate_help.html',
                'noLoginRequired' => true,
            ),
            'getCustomThemeVars' => array(
                'reqType' => 'GET',
                'path' => array('theme'),
                'pathVars' => array(''),
                'method' => 'getCustomThemeVars',
                'shortHelp' => 'Update the less files with custom colors',
                'longHelp' => 'include/api/html/module_relate_help.html',
                'noLoginRequired' => true,
            ),
            'updateCustomTheme' => array(
                'reqType' => 'POST',
                'path' => array('theme'),
                'pathVars' => array(''),
                'method' => 'updateCustomTheme',
                'shortHelp' => 'Update the less files with custom colors',
                'longHelp' => 'include/api/html/module_relate_help.html',
            ),
        );
    }

    /**
     * Generate bootstrap.css
     * @param $api
     * @param $args
     */
    public function generateBootstrapCss($api, $args)
    {
        // Validating arguments
        $platform = $args['platform'] ? $args['platform'] : "base";
        $custom = $args['custom'] ? $args['custom'] : null;

        $variables = $this->get_less_vars($this->getThemeVarsLocation());

        if (isset($args['platform']) || isset($args['custom'])) {
            $customTheme = $this->get_less_vars($this->getThemeVarsLocation($platform, $custom));
            $variables = array_merge($variables, $customTheme);
        }

        if (isset($args['preview'])) {
            $variables = array_merge($variables, $args);
        }

        try {
            $css = $this->compileBootstrapCss($variables, $args["min"]);
            header("Content-type: text/css");
            exit($css);

        } catch (exception $ex) {
            exit('lessc fatal error:<br />' . $ex->getMessage());
        }
    }

    /**
     * Parses variables.less and returns a collection of objects {"name": varName, "value": value}
     * @param $api
     * @param $args
     * @return array
     */
    public function getCustomThemeVars($api, $args)
    {
        try {
            $output = array();

            // Validating arguments
            $platform = $args['platform'] ? $args['platform'] : "base";
            $custom = $args['custom'] ? $args['custom'] : null;

            if (file_exists($this->getThemeVarsLocation($platform, $custom))) {
                $variablesLess = file_get_contents($this->getThemeVarsLocation($platform, $custom));
            } else {
                $variablesLess = file_get_contents($this->getThemeVarsLocation());
            }

            // Parses the hex colors     @varName:      #aaaaaa;
            $output["hex"] = $this->parse_file("/@([^:|@]+):(\s+)(\#.*?);/", $variablesLess, true);
            // Parses the rgba colors     @varName:      rgba(0,0,0,0);
            $output["rgba"] = $this->parse_file("/@([^:|@]+):(\s+)(rgba\(.*?\));/", $variablesLess, true);
            // Parses the related colors     @varName:      @relatedVar;
            $output["rel"] = $this->parse_file("/@([^:|@]+):(\s+)(@.*?);/", $variablesLess, true);
            // Parses the backgrounds     @varNamePath:      "./path/to/img.jpg";
            $output["bg"] = $this->parse_file("/@([^:|@]+Path):(\s+)\"(.*?)\";/", $variablesLess, true);

            return $output;
        } catch (exception $ex) {
            exit('unable to override variables.less:<br />' . $ex->getMessage());
        }
    }

    /**
     * Updates variables.less with the values given in the request.
     * @param $api
     * @param $args
     * @return mixed|string
     * @throws SugarApiExceptionMissingParameter
     */
    public function updateCustomTheme($api, $args)
    {
        if (empty($args)) {
            throw new SugarApiExceptionMissingParameter('Missing colors');
        }

        // Validating arguments
        $platform = $args['platform'] ? $args['platform'] : "base";
        $custom = $args['custom'] ? $args['custom'] : null;

        // Gets the themes files
        $myTheme = $this->getThemeLocation($platform, $custom);
        $myThemeVars = $this->getThemeVarsLocation($platform, $custom);
        $myThemeCss = $this->getThemeCssLocation($platform, $custom);
        $defaultThemeVars = $this->getThemeVarsLocation();

        try {
            // if reset=true is passed
            // Override variables.less with the default theme
            if ($args["reset"] && $args["reset"] == true) {

                $this->write_file($myThemeVars, file_get_contents($defaultThemeVars));

            } else {
                // else
                // Override the custom variables.less with the given vars

                $myThemeVarsFileContents = file_get_contents($myThemeVars);
                foreach ($args as $lessVar => $lessValue) {
                    // escape the args that are not less variables
                    if ($lessVar == "platform" || $lessVar == "custom" || $lessVar == "preview") continue;

                    // override the variables
                    $lessValue = html_entity_decode($lessValue);
                    $myThemeVarsFileContents = preg_replace("/@$lessVar:(.*);/", "@$lessVar: $lessValue;", $myThemeVarsFileContents);
                }
                $myThemeVarsFileContents = str_replace('\n', '', $myThemeVarsFileContents);

                // overwrite the theme
                $this->write_file($myThemeVars, $myThemeVarsFileContents);
            }

            // Write the bootstrap.css file
            $variables = $this->get_less_vars($defaultThemeVars);
            $customTheme = $this->get_less_vars($myThemeVars);
            $variables = array_merge($variables, $customTheme);
            $css = $this->compileBootstrapCss($variables);
            $this->write_file($myThemeCss, $css);

            // saves the bootstrap.css URL in the portal settings
            $GLOBALS ['system_config']->saveSetting('portal', "css", json_encode($GLOBALS['sugar_config']['site_url'] . "/" . $myThemeCss));
            exit();

        } catch (exception $ex) {
            exit('unable to override $myTheme:<br />' . $ex->getMessage());
        }
    }

    /**
     * Compiles the bootstrap.less file with custom variables
     * @param $variables to be given to lessphp compiler
     * @param bool $min minify or not the CSS
     * @return string CSS
     */
    private function compileBootstrapCss($variables, $min = true)
    {
        $less = new lessc("include/styleguide/less/bootstrap.less");
        if ($min === true) {
            $less->setFormatter("compressed");
        }
        return $less->parse($variables);
    }

    /**
     * Does a preg_match_all on a variables.less file and returns an array with varname/value
     * @param $regex
     * @param $input contents of variables.less
     * @param bool $formatAsCollection if true, returns an array of objects, if false, returns a hash
     * @return array
     */
    private function parse_file($regex, $input, $formatAsCollection = false)
    {
        $output = array();
        preg_match_all($regex, $input, $match, PREG_PATTERN_ORDER);
        foreach ($match[1] as $key => $lessVar) {
            if ($formatAsCollection) {
                $output[] = array("name" => $lessVar, "value" => $match[3][$key]);
            } else {
                $output[$lessVar] = $match[3][$key];
            }
        }
        return $output;
    }


    /**
     * Parses a less file and returns the array of variables
     * @param $filename
     * @return array
     */
    private function get_less_vars($filename)
    {
        $output = array();
        $variablesLess = file_get_contents($filename);

        if ($variablesLess) {
            // Parses the hex colors     @varName:      #aaaaaa;
            $output = array_merge($output, $this->parse_file("/@([^:|@]+):(\s+)(\#.*?);/", $variablesLess));
            // Parses the rgba colors     @varName:      rgba(0,0,0,0);
            $output = array_merge($output, $this->parse_file("/@([^:|@]+):(\s+)(rgba\(.*?\));/", $variablesLess));
            // Parses the related colors     @varName:      @relatedVar;
            $output = array_merge($output, $this->parse_file("/@([^:|@]+):(\s+)(@.*?);/", $variablesLess));
            // Parses the backgrounds     @varNamePath:      "./path/to/img.jpg";
            $output = array_merge($output, $this->parse_file("/@([^:|@]+Path):(\s+)(\".*?\");/", $variablesLess));
        }

        return $output;
    }

    /**
     * Builds the URL of the theme folder
     * @param string $platform
     * @param null $custom
     * @param bool $onlyPath
     * @return string
     */
    private function getThemeLocation($platform = "base", $custom = null)
    {
        if (!$custom) {
            return "themes/clients/$platform/";
        } else {
            return "custom/themes/clients/$platform/$custom/";
        }
    }

    /**
     * Builds the URL of the variables.less file of the theme
     * @param string $platform
     * @param null $custom
     * @return string
     */
    private function getThemeVarsLocation($platform = "base", $custom = null)
    {
        return $this->getThemeLocation($platform, $custom) . "variables.less";
    }

    /**
     * Builds the URL of the bootstrap.css file of the theme
     * @param string $platform
     * @param null $custom
     * @return string
     */
    private function getThemeCssLocation($platform = "base", $custom = null)
    {
        return $this->getThemeLocation($platform, $custom) . "bootstrap.css";
    }

    private function write_file($file, $contents) {
        if ( !file_put_contents( $file , $contents ) ) {
        	$paths = explode ('/', $file);
        	$root = '';
        	foreach ($paths as $key => $dir) {
        		if ($key == sizeof($paths)-1) break;
        		$root .= $dir . '/';
        		if (!is_dir($root)) mkdir($root);
        	}
            file_put_contents( $file , $contents );
        }
    }
}
