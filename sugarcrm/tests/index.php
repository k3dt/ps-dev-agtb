<html>
<head>
    <!-- required classes for tests -->
    <script type="text/javascript" src="../sidecar/lib/backbone/underscore.js"></script>
    <script type="text/javascript" src="../sidecar/lib/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="../sidecar/lib/backbone/backbone.js"></script>
    <script type="text/javascript" src="../sidecar/lib/handlebars/handlebars-1.0.rc.1.js"></script>
    <script type="text/javascript" src="../sidecar/lib/sugarapi/sugarapi.js"></script>
    <script type="text/javascript" src="../sidecar/minified/sidecar.min.js"></script>
    <!-- For sugar7 the plan is to generate a sugar.min.js .. in the meantime load each file -->
    <script type="text/javascript" src="../include/javascript/sugar7/field.js"></script>
    <script type="text/javascript" src="../include/javascript/sugar7/alert.js"></script>
    <script type="text/javascript" src="../include/javascript/sugar7/bwc.js"></script>
    <script type="text/javascript" src="../include/javascript/sugar7/plugins/dropdown.js"></script>
    <script type="text/javascript" src="../include/javascript/sugar7/plugins/error-decoration.js"></script>
    <script type="text/javascript" src="../include/javascript/sugar7/plugins/quicksearchfilter.js"></script>
    <script type="text/javascript" src="../include/javascript/sugar7/plugins/taggable.js"></script>
    <script type="text/javascript" src="../include/javascript/sugar7/plugins/GridBuilder.js"></script>
    <script type="text/javascript" src="../include/javascript/sugar7/plugins/list-disable-sort.js"></script>
    <script type="text/javascript" src="../include/javascript/sugar7/plugins/editable.js"></script>

    <!-- customized beans -->
    <script type="text/javascript" src="../modules/Contacts/clients/base/lib/bean.js"></script>
<?php
// For sugar7 the plan is to generate a /sugarcrm/config.js .. in the meantime fallback to sidecar config.js
if (file_exists('../config.js')) {
    echo '<script src="../config.js" type="text/javascript"></script>';
} else {
    echo '<script src="../sidecar/tests/config.js" type="text/javascript"></script>';
}
?>
    <script type="text/javascript" src="../include/javascript/jquery/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../styleguide/assets/js/bootstrap-collapse.js"></script>
    <script type="text/javascript" src="../styleguide/assets/js/bootstrap-dropdown.js"></script>
    <script type="text/javascript" src="../include/javascript/select2-release-3.3.2/select2.js"></script>

    <!-- jasmine and sinon core files -->
    <script type="text/javascript" src="../sidecar/lib/sinon/sinon.js"></script>
    <script type="text/javascript" src="../sidecar/lib/jasmine/jasmine.js"></script>
    <script type="text/javascript" src="../sidecar/lib/jasmine/jasmine-html.js"></script>
    <script type="text/javascript" src="../sidecar/lib/jasmine-sinon/jasmine-sinon.js"></script>
    <script type="text/javascript" src="../sidecar/lib/jasmine-jquery/jasmine-jquery.js"></script>
    <script type="text/javascript" src="../sidecar/lib/jasmine-ci/jasmine-reporters/jasmine.phantomjs-reporter.js"></script>
    <script type="text/javascript" src="../sidecar/tests/spec-helper.js"></script>
    <script type="text/javascript" src="jshelpers/spec-helper.js"></script>
    <script type="text/javascript" src="jshelpers/component-helper.js"></script>
    <link rel="stylesheet" href="../sidecar/lib/jasmine/jasmine.css"/>

    <!-- Fixtures -->
    <script type="text/javascript" src='../sidecar/tests/fixtures/api.js'></script>
    <script type="text/javascript" src='../sidecar/tests/fixtures/metadata.js'></script>
    <script type="text/javascript" src='../sidecar/tests/fixtures/language.js'></script>
    <script type="text/javascript" src='../tests/modules/Forecasts/fixtures/forecastsMetadata.js'></script>

    <!-- Portal extensions -->
    <script type="text/javascript" src='../portal2/user.js'></script>

    <!-- Begin test files -->
<?php

/**
 * jasmine test generator. This file will recursively search the test directory for .js test files and include them.
 */

    $exclude = array("jshelpers", "jssource", "PHPUnit", "/ci/");

    $path = '.';
    if(isset($_GET['module'])) {
        $module = $_GET['module'];
        if(is_dir('modules/' . $module)) {
            $path = 'modules/' . $module;
        }
    }
    $dirItr = new RecursiveDirectoryIterator($path);
    $itrItr = new RecursiveIteratorIterator($dirItr);
    foreach($itrItr as $path => $file) {
        if (substr(basename($path), -3) != ".js")
            continue;
        $skip = false;
        foreach($exclude as $ex){
            if (strpos($path, $ex) !== false) {
                $skip = true;
                break;
            }
        }
        if ($skip) continue;

        echo "<script type='text/javascript' src='$path'></script>\n";
    }
?>
    <!-- End test files -->
    <script type="text/javascript">
        //Reset the local storage to prevent random failures
        SUGAR.App.cache.cutAll();
        SUGAR.App.config.syncConfig = false;
        (function () {
            var jasmineEnv = jasmine.getEnv();
            jasmineEnv.updateInterval = 1000;

            var trivialReporter = new jasmine.TrivialReporter();

            jasmineEnv.addReporter(trivialReporter);

            // Allows us to create report in JUnit XML Report format for CI.
            // I've observed no "slow down" when ran from browser ;=)
            jasmineEnv.addReporter(new jasmine.PhantomJSReporter());

            jasmineEnv.specFilter = function (spec) {
                return trivialReporter.specFilter(spec);
            };

            var currentWindowOnload = window.onload;

            window.onload = function () {
                if (currentWindowOnload) {
                    currentWindowOnload();
                }
                execJasmine();
            };

            function execJasmine() {
                jasmineEnv.execute();
            }
        })();
    </script>
</head>
<body>
<div></div>
</body>
</html>
