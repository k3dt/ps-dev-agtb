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
describe('Opportunities.Routes', function() {
    var app;
    var buildKeyStub;
    var getStub;
    var setStub;

    beforeEach(function() {
        app = SugarTest.app;
        app.controller.loadAdditionalComponents(app.config.additionalComponents);
        buildKeyStub = sinon.collection.stub(app.user.lastState, 'buildKey');
        getStub = sinon.collection.stub(app.user.lastState, 'get');
        setStub = sinon.collection.stub(app.user.lastState, 'set');

        SugarTest.loadFile('../modules/Opportunities/clients/base/routes', 'routes', 'js', function(d) {
            eval(d);
            app.routing.start();
        });
    });

    afterEach(function() {
        sinon.collection.restore();
        app.router.stop();
    });

    describe('Routes', function() {
        var mockKey = 'foo:key';
        var oldIsSynced;

        beforeEach(function() {
            oldIsSynced = app.isSynced;
            app.isSynced = true;
            sinon.collection.stub(app.controller, 'loadView');

            sinon.collection.stub(app.router, 'hasAccessToModule').returns(true);
            sinon.collection.stub(app.api, 'isAuthenticated').returns(true);
            sinon.collection.stub(app, 'sync');
            buildKeyStub.returns(mockKey);
        });

        afterEach(function() {
            app.isSynced = oldIsSynced;
        });

        it('should load the pipeline view', function() {
            app.router.navigate('Opportunities/pipeline', {trigger: true});
            expect(app.controller.loadView).toHaveBeenCalledWith({
                module: 'Opportunities',
                layout: 'pipeline-records'
            });
        });
    });
});
