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

describe('Base.View.TabbedDashboardView', function() {
    var view;

    beforeEach(function() {
        view = SugarTest.createView('base', 'Home', 'tabbed-dashboard');
    });

    afterEach(function() {
        sinon.collection.restore();
        view = null;
    });

    describe('initialize', function() {
        it('should have tabs based on the given metadata', function() {
            var tabs = [
                {name: 'tab 0'},
                {
                    name: 'tab 1',
                    badges: [{cssClass: 'label-important', text: '5'}],
                },
            ];
            view.initialize({
                meta: {
                    activeTab: 1,
                    tabs: tabs,
                },
            });

            expect(view.activeTab).toEqual(1);
            expect(view.tabs).toEqual(tabs);
        });
    });

    describe('tabClicked', function() {
        var triggerStub;

        beforeEach(function() {
            triggerStub = sinon.collection.stub(view.context, 'trigger');
            sinon.collection.stub(view, '$').withArgs('tab 1').returns({
                data: sinon.collection.stub().withArgs('index').returns(1)
            });
        });

        it('should trigger tabbed-dashboard:switch-tab on the context if the active tab changed', function() {
            view.activeTab = 0;

            view.tabClicked({currentTarget: 'tab 1'});

            expect(triggerStub).toHaveBeenCalledWith('tabbed-dashboard:switch-tab', 1);
        });

        it('should not do anything if the active tab did not change', function() {
            view.activeTab = 1;

            view.tabClicked({currentTarget: 'tab 1'});

            expect(triggerStub).not.toHaveBeenCalled();
        });
    });

    describe('events', function() {
        it('should re-render on tabbed-dashboard:update', function() {
            var renderStub = sinon.collection.stub(view, 'render');
            view.context.trigger('tabbed-dashboard:update');
            expect(renderStub).toHaveBeenCalled();
        });
    });
});