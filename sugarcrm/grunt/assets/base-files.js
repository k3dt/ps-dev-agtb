[
    {pattern: 'clients/**/*.hbs', included: false, served: true, watched: false},
    {pattern: 'modules/**/clients/**/*.hbs', included: false, served: true, watched: false},
    {pattern: 'tests/fixtures/*.json', included: false, served: true, watched: false},

    'sidecar/lib/backbone/underscore.js',
    'sidecar/lib/jquery/jquery.min.js',
    'sidecar/lib/backbone/backbone.js',
    'sidecar/lib/handlebars/handlebars.js',
    'sidecar/lib/sugarapi/sugarapi.js',
    'sidecar/minified/sidecar.min.js',
    'include/javascript/sugar7/hbs-helpers.js',
    'include/javascript/modernizr.js',
    'include/javascript/nprogress/nprogress.js',

    // For sugar7 the plan is to generate a sugar.min.js .. in the meantime load each file
    'include/javascript/sugar7/field.js',
    'include/javascript/sugar7/alert.js',
    'include/javascript/sugar7/bwc.js',
    'include/javascript/sugar7/utils.js',
    'include/javascript/sugar7/language.js',
    'include/javascript/sugar7/help.js',
    'include/javascript/sugar7/underscore-mixins.js',

    // TODO: decide if we're going to only serve or if we should have
    // them all by default
    {pattern: 'include/javascript/sugar7/plugins/*.js', included: true, served: true, watched: false},

    // FIXME: this should be included by the tests, not here
    'modules/Contacts/clients/base/lib/bean.js',

    'sidecar/tests/config.js',

    'include/javascript/jquery/jquery.dataTables.min.js',
    'include/javascript/twitterbootstrap/bootstrap-collapse.js',
    'include/javascript/twitterbootstrap/bootstrap-tooltip.js',
    'include/javascript/twitterbootstrap/bootstrap-tab.js',
    'include/javascript/twitterbootstrap/bootstrap-dropdown.js',
    'include/javascript/twitterbootstrap/bootstrap-datepicker.js',
    'include/javascript/jquery/jquery.timepicker.js',
    'include/javascript/select2/select2.js',
    'include/javascript/nvd3/lib/d3.min.js',
    'include/javascript/nvd3/nv.d3.min.js',

    // jasmine and sinon core files
    'sidecar/lib/sinon/sinon.js',
    'sidecar/lib/jasmine-sinon/jasmine-sinon.js',
    'sidecar/lib/jasmine-jquery/jasmine-jquery.js',
    'sidecar/lib/jasmine-ci/jasmine-reporters/jasmine.phantomjs-reporter.js',
    'sidecar/tests/spec-helper.js',
    'tests/jshelpers/spec-helper.js',
    'tests/jshelpers/component-helper.js',

    // Fixtures
    'sidecar/tests/fixtures/api.js',
    'sidecar/tests/fixtures/metadata.js',
    'sidecar/tests/fixtures/language.js',
    'tests/fixtures/metadata.js',
    'tests/modules/**/fixtures/*.js',

    // FIXME: this should be included by the tests, not here
    'portal2/user.js',

    // If we are emulating what the real app will see, we need to include our hacks
    'include/javascript/sugar7/hacks.js',

    'grunt/environment.js',

    {pattern: 'clients/**/*.js', included: false, served: true, watched: false},
    {pattern: 'modules/**/clients/**/*.js', included: false, served: true, watched: false}
]
