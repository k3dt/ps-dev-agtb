//FILE SUGARCRM flav=pro ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
if (!(fixtures)) {
    var fixtures = {};
}
// Make this play nice if fixtures has already been defined for other tests
// so we dont overwrite data
if(!_.has(fixtures, 'metadata')) {
    fixtures.metadata = {};
}
fixtures.metadata.currencies = {
    "-99": {
        id: '-99',
        symbol: "$",
        conversion_rate: "1.0",
        iso4217: "USD"
    },
    //Because obviously everyone loves 1970's Jackson5 hits
    "abc123": {
        id: 'abc123',
        symbol: "€",
        conversion_rate: "0.9",
        iso4217: "EUR"
    }
}
describe("RevenueLineItems.Base.View.CreateActions", function() {
    var app, view, options;

    beforeEach(function() {
        options = {
            meta: {
                panels: [{
                    fields: [{
                        name: "commit_stage"
                    }]
                }]
            }
        };

        app = SugarTest.app;
        SugarTest.seedMetadata(true, './fixtures');
        app.user.setPreference('decimal_precision', 2);
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'create');
        SugarTest.loadComponent('base', 'view', 'create-actions');

        view = SugarTest.createView('base', 'RevenueLineItems', 'create-actions', options.meta, null, true);

        //view = SugarTest.loadFile("../modules/RevenueLineItems/clients/base/views/create-actions", "create-actions", "js", function(d) { return eval(d); });
    });

    describe("initialization", function() {
        beforeEach(function() {
            sinon.stub(app.view.views.BaseCreateView.prototype, "initialize");

            sinon.stub(app.metadata, "getModule", function () {
                return {
                    is_setup: true,
                    buckets_dom: "commit_stage_binary_dom"
                }
            })
            sinon.stub(view, "_parsePanelFields");

        });

        afterEach(function() {
            view._parsePanelFields.restore();
            app.metadata.getModule.restore();
            app.view.views.BaseCreateView.prototype.initialize.restore();
        });
    });

    describe("_parsePanelFields method", function() {
        it("should replace commit_stage with a spacer", function() {
            sinon.stub(app.metadata, "getModule", function () {
                return {
                    is_setup: false
                }
            });
            view._parsePanelFields(options.meta.panels);
            expect(options.meta.panels[0].fields).toEqual([{ name : 'spacer', span : 6, readonly : true }]);
            app.metadata.getModule.restore();
        });
    });

})
