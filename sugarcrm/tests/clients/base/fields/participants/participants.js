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
describe('View.Fields.Base.ParticipantsField', function() {
    var app, context, field, fieldDef, fixture, model, module, participants, sandbox;

    module = 'Meetings';

    participants = [
        {_module: 'Users', id: '1', name: 'Jim Brennan', accept_status_meetings: 'accept'},
        {_module: 'Users', id: '2', name: 'Will Weston', accept_status_meetings: 'decline'},
        {_module: 'Contacts', id: '3', name: 'Jim Gallardo', accept_status_meetings: 'tentative'},
        {_module: 'Leads', id: '4', name: 'Sallie Talmadge', accept_status_meetings: 'none'}
    ];

    fieldDef = {
        name: 'invitees',
        source: 'non-db',
        type: 'collection',
        vname: 'LBL_INVITEES',
        links: ['contacts', 'leads', 'users'],
        order_by: 'name:asc',
        fields: [
            {
                name: 'name',
                type: 'name',
                label: 'LBL_SUBJECT'
            },
            'accept_status_meetings',
            'picture'
        ]
    };

    fixture = {
        _hash: '12345678910',
        fields: {
            id: {
                name: 'id',
                type: 'id'
            },
            first_name: {
                name: 'first_name',
                type: 'varchar',
                len: 20
            },
            last_name: {
                name: 'last_name',
                type: 'varchar'
            },
            full_name: {
                name: 'full_name',
                type: 'varchar',
                concat: ['first_name', 'last_name']
            }
        }
    };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.updateModuleMetadata('Users', _.extend({}, fixture, {isBwcEnabled: true}));
        SugarTest.testMetadata.updateModuleMetadata('Contacts', {isBwcEnabled: false});
        SugarTest.testMetadata.updateModuleMetadata('Leads', _.extend({}, fixture, {isBwcEnabled: false}));
        SugarTest.loadHandlebarsTemplate('participants', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('participants', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('participants', 'field', 'base', 'timeline-header.partial');
        SugarTest.loadHandlebarsTemplate('participants', 'field', 'base', 'search-result.partial');
        SugarTest.loadComponent('base', 'field', 'participants');
        SugarTest.declareData('base', module, true, false);
        SugarTest.loadPlugin('EllipsisInline');
        SugarTest.loadPlugin('VirtualCollection');
        SugarTest.loadPlugin('Tooltip');
        SugarTest.testMetadata.set();
        app.data.declareModelClass('Users', null, 'base', fixture);
        app.data.declareModelClass('Leads', null, 'base', fixture);
        SugarTest.app.data.declareModels();

        context = app.context.getContext({module: module});
        context.prepare(true);
        model = context.get('model');

        sandbox = sinon.sandbox.create();
        sandbox.stub(app.api, 'call', function(method, url, data, callbacks, options) {
            if (callbacks.success) {
                callbacks.success({});
            }
        });
        sandbox.stub(app.data, 'getRelatedModule');
        app.data.getRelatedModule.withArgs('Meetings', 'users').returns('Users');
        app.data.getRelatedModule.withArgs('Meetings', 'contacts').returns('Contacts');
        app.data.getRelatedModule.withArgs('Meetings', 'leads').returns('Leads');
    });

    afterEach(function() {
        sandbox.restore();

        if (field) {
            field.dispose();
        }

        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('accessing the field value', function() {
        beforeEach(function() {
            sandbox.stub(model, 'isNew').returns(false);
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
            // skip rendering
            field.model.off('change:' + field.name);
        });

        it('should return the collection', function() {
            field.model.set(field.name, participants);
            expect(field.getFieldValue().length).toBe(participants.length);
        });

        it('should throw an exception when the field value has not been set', function() {
            expect(function() {
                var value = field.getFieldValue();
            }).toThrow();
        });
    });

    describe('when creating a new meeting', function() {
        it('should add the current user to the collection', function() {
            app.user.set({_module: 'Users', id: '1', name: 'Jim Brennan'});
            sandbox.stub(model, 'isNew').returns(true);
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'edit',
                fieldDef,
                module,
                model,
                context
            );
            expect(field.getFieldValue().length).toBe(1);
            app.user.attributes = {};
        });
    });

    describe('when the participants field is in detail mode', function() {
        beforeEach(function() {
            sandbox.stub(model, 'isNew').returns(false);
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
            field.model.set(field.name, participants);
        });

        it('should render one row for each participant', function() {
            field.render();
            expect(field.$('div.row.participant').length).toBe(participants.length);
        });

        it('should preview the participant when the preview button is clicked', function() {
            var previewBtn, spy;
            spy = sandbox.spy();
            app.events.on('preview:render', spy, this);
            field.render();
            previewBtn = field.$('button[data-action=previewRow]:not(.disabled)').first();
            previewBtn.click();
            expect(spy).toHaveBeenCalled();
            app.events.off('preview:render', this);
        });
    });

    describe('when the participants field is in edit mode', function() {
        beforeEach(function() {
            sandbox.stub(model, 'isNew').returns(false);
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'edit',
                fieldDef,
                module,
                model,
                context
            );
            field.action = 'edit';
            field.model.set(field.name, participants);
        });

        it('should hide the select initially', function() {
            field.render();
            expect(field.$('[name=newRow]').css('display')).toEqual('none');
        });

        it('should only have one plus button', function() {
            field.render();
            expect(field.$('button[data-action=addRow]').length).toBe(1);
        });

        it('should show the select and hide the plus button when the plus button is clicked', function() {
            field.render();
            field.$('button[data-action=addRow]').click();
            expect(field.$('[name=newRow]').css('display')).toEqual('table');
            expect(field.$('button[data-action=addRow]').css('display')).toEqual('none');
        });

        it("should hide the select and show the plus button when the select row's minus button is clicked", function() {
            field.render();
            field.$('button[data-action=addRow]').click();
            field.$('button[data-action=removeRow]').last().click();
            expect(field.$('[name=newRow]').css('display')).toEqual('none');
            expect(field.$('button[data-action=addRow]').css('display')).not.toEqual('none');
        });

        it("should remove a participant when that participant's minus button is clicked", function() {
            var spy = sandbox.spy(field.getFieldValue(), 'remove');
            field.render();
            field.$('button[data-action=removeRow]').first().click();
            expect(spy).toHaveBeenCalled();
            expect(field.$('div.row.participant').length).toBe(participants.length - 1);
        });

        it("should disable a participant's delete button on create when the participant is the current user and not the assigned user", function() {
            var appUserId = app.user.id;
            app.user.id = '1';
            model.isNew.restore();
            sandbox.stub(model, 'isNew').returns(true);
            field.render();
            expect(field.$('button[data-action=removeRow][data-id=1]').hasClass('disabled')).toBe(true);
            if (appUserId) {
                app.user.id = appUserId;
            }
        });

        it("should not disable a participant's delete button on update when the participant is the current user and not the assigned user", function() {
            var appUserId = app.user.id;
            app.user.id = '1';
            field.render();
            expect(field.$('button[data-action=removeRow][data-id=1]').hasClass('disabled')).toBe(false);
            if (appUserId) {
                app.user.id = appUserId;
            }
        });

        it("should disable a participant's delete button when the participant is the assigned user", function() {
            field.model.set('assigned_user_id', '1');
            field.render();
            expect(field.$('button[data-action=removeRow][data-id=1]').hasClass('disabled')).toBe(true);
        });

        it('should add a participant when a new participant is selected', function() {
            var spy = sandbox.spy(field.getFieldValue(), 'add');
            field.render();
            field.getFieldElement().select2('data', {
                id: '5',
                text: 'George Walton',
                attributes: {_module: 'Contacts', id: '5', name: 'George Walton'}
            }, true);
            expect(spy).toHaveBeenCalled();
            expect(field.$('div.row.participant').length).toBe(participants.length + 1);
        });

        it('should search for more participants and add them to the options', function() {
            var data, query;

            query = {
                term: 'George',
                callback: sandbox.spy()
            };

            sandbox.stub(field.getFieldValue(), 'search', function(options) {
                var records = app.data.createMixedBeanCollection([
                    {_module: 'Contacts', id: '5', name: 'George Walton'}
                ]);
                options.success(records);
                options.complete();
            });

            field.search(query);
            data = query.callback.getCall(0).args[0];
            expect(data.more).toBe(false);
            expect(data.results.length).toBe(1);
        });

        it('should not include participants that are already invited', function() {
            var query = {
                term: 'Jim',
                callback: sandbox.spy()
            };

            sandbox.stub(field.getFieldValue(), 'search', function(options) {
                var records = app.data.createMixedBeanCollection([
                    {_module: 'Users', id: '1', name: 'Jim Brennan'},
                    {_module: 'Contacts', id: '3', name: 'Jim Gallardo'},
                    {_module: 'Leads', id: '6', name: 'Jim Long'}
                ]);
                options.success(records);
                options.complete();
            });

            field.search(query);
            expect(query.callback.getCall(0).args[0].results.length).toBe(1);
        });

        it('should produce no results when an exception is thrown', function() {
            var query = {
                term: 'Jim',
                callback: sandbox.spy()
            };

            sandbox.stub(field, 'getFieldValue').throws();
            field.search(query);
            expect(query.callback.getCall(0).args[0].results.length).toBe(0);
        });
    });

    describe('formatting the view model for render', function() {
        beforeEach(function() {
            sandbox.stub(model, 'isNew').returns(false);
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
        });

        it('should return an empty array when an exception is thrown', function() {
            field.model.set(field.name, participants);
            sandbox.stub(field, 'getFieldValue').throws();
            expect(field.format(undefined).length).toBe(0);
        });

        it('should only return an array of 4 participants', function() {
            var collection;
            field.model.set(field.name, participants);
            collection = field.getFieldValue();
            collection.add([
                {_module: 'Contacts', id: '5', name: 'George Walton'},
                {_module: 'Contacts', id: '6', name: 'Jim Long'}
            ]);
            collection.remove(['2', '4']);
            expect(field.format(field.model.get(field.name)).length).toBe(4);
        });

        it('should set the last property to true for only the final participant', function() {
            var isLast;
            field.model.set(field.name, participants);
            isLast = _.findWhere(field.format(field.model.get(field.name)), {last: true});
            expect(isLast.name).toEqual('Sallie Talmadge');
        });

        it('should only include an avatar property when the participant has a picture field', function() {
            var hasAvatar;
            sandbox.stub(field, '_render');
            field.model.set(field.name, [
                {_module: 'Contacts', id: '5', name: 'George Walton', picture: '5'},
                {_module: 'Contacts', id: '6', name: 'Jim Long'}
            ]);
            hasAvatar = _.filter(field.format(field.model.get(field.name)), function(participant) {
                return !_.isUndefined(participant.avatar);
            });
            expect(hasAvatar.length).toBe(1);
        });

        it('should set the accept status appropriately', function() {
            var formatted;
            field.model.set(field.name, participants);
            field.getFieldValue().add([
                {_module: 'Contacts', id: '5', name: 'George Walton', accept_status_meetings: ''}
            ]);
            formatted = field.format(field.model.get(field.name));
            expect(formatted[0].accept_status.label).toEqual('LBL_CALENDAR_EVENT_RESPONSE_ACCEPT');
            expect(formatted[0].accept_status.css_class).toEqual('success');
            expect(formatted[1].accept_status.label).toEqual('LBL_CALENDAR_EVENT_RESPONSE_DECLINE');
            expect(formatted[1].accept_status.css_class).toEqual('important');
            expect(formatted[2].accept_status.label).toEqual('LBL_CALENDAR_EVENT_RESPONSE_TENTATIVE');
            expect(formatted[2].accept_status.css_class).toEqual('warning');
            expect(formatted[3].accept_status.label).toEqual('LBL_CALENDAR_EVENT_RESPONSE_NONE');
            expect(formatted[3].accept_status.css_class).toEqual('');
            expect(formatted[4].accept_status.label).toEqual('LBL_CALENDAR_EVENT_RESPONSE_NONE');
            expect(formatted[4].accept_status.css_class).toEqual('');
        });
    });

    describe('rendering the timeline', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
            field.model.set(field.name, participants);
            field.model.off();
            field.model.set('date_start', '2014-08-27T08:45');
            field.model.set('date_end', '2014-08-27T10:15');

            sandbox.stub(field, 'getTimeFormat', function() {
                return 'ha';
            });
        });

        it('should render the header starting 4 hours before the start date and end 5 hours after', function() {
            field.render();

            expect(field.$('[data-render=timeline-header] .timeblock span').first().text()).toBe('4am');
            expect(field.$('[data-render=timeline-header] .timeblock span').last().text()).toBe('12pm');
        });

        it('should render the header alternating in colors', function() {
            field.render();

            expect(field.$('[data-render=timeline-header] .timeblock').filter(':nth-child(odd)').hasClass('alt')).toBe(true);
            expect(field.$('[data-render=timeline-header] .timeblock').filter(':nth-child(even)').hasClass('alt')).toBe(false);
        });

        it('should mark the timeblocks that make up the meeting', function() {
            var $blocks, $scheduledBlocks, start, end;

            field.render();
            $blocks = field.getTimelineBlocks('Contacts', '3');
            $scheduledBlocks = $blocks.filter('.schedule');
            start = $blocks.index($scheduledBlocks.first());
            end = $blocks.index($scheduledBlocks.last());

            expect(start).toBe(19);
            expect(end).toBe(24);
        });

        it('should mark the first and the last timeblocks that make up the meeting', function() {
            var $blocks, $scheduledBlocks;

            field.render();
            $blocks = field.getTimelineBlocks('Contacts', '3');
            $scheduledBlocks = $blocks.filter('.schedule');

            expect($scheduledBlocks.first().hasClass('start')).toBe(true);
            expect($scheduledBlocks.last().hasClass('end')).toBe(true);
        });

        it('should mark the timeblock as start and end when the meeting is 15 minutes long', function() {
            var $blocks, $scheduledBlocks;

            field.model.set('date_start', '2014-08-27T08:45:00-04:00');
            field.model.set('date_end', '2014-08-27T09:00:00-04:00');
            field.render();
            $blocks = field.getTimelineBlocks('Contacts', '3');
            $scheduledBlocks = $blocks.filter('.schedule');

            expect($scheduledBlocks.length).toBe(1);
            expect($scheduledBlocks.hasClass('start')).toBe(true);
            expect($scheduledBlocks.hasClass('end')).toBe(true);
        });

        it('should mark the timeblock to have the same start and end time when the meeting is 0 minutes long', function() {
            var $blocks, $scheduledBlocks;

            field.model.set('date_start', '2014-08-27T08:45:00-04:00');
            field.model.set('date_end', '2014-08-27T08:45:00-04:00');
            field.render();
            $blocks = field.getTimelineBlocks('Contacts', '3');
            $scheduledBlocks = $blocks.filter('.start-end');

            expect($scheduledBlocks.length).toBe(1);
            expect($blocks.index($scheduledBlocks.first())).toBe(19);
        });
    });

    describe('rendering the free/busy information', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
            field.model.off();
            field.getFieldValue().reset(participants);
            field.model.set('date_start', '2014-08-27T08:45:00-04:00');
            field.model.set('date_end', '2014-08-27T10:15:00-04:00');

            sandbox.stub(field, 'getTimeFormat', function() {
                return 'ha';
            });
        });

        it('should only fetch information for Users', function() {
            var callBulkApiSpy = sandbox.spy(field, 'callBulkApi');

            field.render();

            expect(callBulkApiSpy.args[0][0]).toEqual([{
                url: app.api.buildURL('Users', 'freebusy', {id: '1'}).substring(4)
            }, {
                url: app.api.buildURL('Users', 'freebusy', {id: '2'}).substring(4)
            }]);
        });

        it('should not fetch information for a user if free/busy information has been cached for that user', function() {
            var callBulkApiSpy = sandbox.spy(field, 'callBulkApi');

            field.cacheFreeBusyInformation({
                id: '1',
                module: 'Users',
                freebusy: []
            });
            field.render();

            expect(callBulkApiSpy.args[0][0]).toEqual([{
                url: app.api.buildURL('Users', 'freebusy', {id: '2'}).substring(4)
            }]);
        });

        it('should mark busy indicators on timeslots that are taken up by other meetings', function() {
            var $blocks, $busyBlocks;

            field.render();
            field.fillInFreeBusyInformation({
                id: '1',
                module: 'Users',
                freebusy: [{
                    start: '2014-08-27T08:00:00-04:00',
                    end: '2014-08-27T08:30:00-04:00'
                }, {
                    start: '2014-08-27T10:30:00-04:00',
                    end: '2014-08-27T11:00:00-04:00'
                }]
            });

            $blocks = field.getTimelineBlocks('Users', '1');
            $busyBlocks = $blocks.filter('.busy');

            expect($busyBlocks.length).toBe(4);
            expect($blocks.index($busyBlocks.eq(0))).toBe(16);
            expect($blocks.index($busyBlocks.eq(1))).toBe(17);
            expect($blocks.index($busyBlocks.eq(2))).toBe(26);
            expect($blocks.index($busyBlocks.eq(3))).toBe(27);
        });

        it('should not show any busy timeslots if other meetings are outside the displayed timeline range', function() {
            var $blocks, $busyBlocks;

            field.render();
            field.fillInFreeBusyInformation({
                id: '1',
                module: 'Users',
                freebusy: [{
                    start: '2014-08-27T03:30:00-04:00',
                    end: '2014-08-27T04:00:00-04:00'
                }, {
                    start: '2014-08-27T13:00:00-04:00',
                    end: '2014-08-27T13:30:00-04:00'
                }]
            });

            $blocks = field.getTimelineBlocks('Users', '1');
            $busyBlocks = $blocks.filter('.busy');

            expect($busyBlocks.length).toBe(0);
        });
    });

    describe('caching the free/busy information', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
        });

        it('should save the information to be retrieved again', function() {
            var data = {
                id: '123',
                module: 'Users',
                freebusy: []
            };

            field.cacheFreeBusyInformation(data);

            expect(field.getFreeBusyInformationFromCache('Users', '123')).toBe(data);
        });

        it('should return nothing if data is not found for the particular user', function() {
            expect(field.getFreeBusyInformationFromCache('Users', '123')).toBeUndefined();
        });

        it('should only have the last data if it has been saved more than once for the same user', function() {
            var data1 = {
                    id: '123',
                    module: 'Users',
                    freebusy: [{
                        start: 'foo'
                    }]
                },
                data2 = {
                    id: '123',
                    module: 'Users',
                    freebusy: [{
                        start: 'bar'
                    }]
                };

            field.cacheFreeBusyInformation(data1);
            field.cacheFreeBusyInformation(data2);

            expect(field._freeBusyCache.length).toBe(1);
            expect(field.getFreeBusyInformationFromCache('Users', '123').freebusy[0].start).toBe('bar');
        });
    });

    describe('parseModuleAndIdFromUrl', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                fieldDef.name,
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
        });

        it('should parse module and ID from freebusy URL', function() {
            expect(field.parseModuleAndIdFromUrl('/v10/Users/123/freebusy')).toEqual({
                module: 'Users',
                id: '123'
            });
        });

        it('should return an empty object if module and id has not been found', function() {
            expect(field.parseModuleAndIdFromUrl('/v10/freebusy')).toEqual({});
        });
    });

    describe('formatSearchResult', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'invitees',
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context
            );
        });

        it('should only show module and name when no highlighted field', function() {
            var searchResultTemplateStub = sandbox.stub(field, 'searchResultTemplate'),
                result = app.data.createBean('Baz', {
                    full_name: 'Foo Bar'
                });
            result.module = 'Baz';
            result.searchInfo = {};
            field.formatSearchResult(result);
            expect(searchResultTemplateStub.lastCall.args).toEqual([{
                module: 'Baz',
                name: 'Foo Bar'
            }]);
        });

        it('should show module, name, and highlighted field when highlighted field is given', function() {
            var searchResultTemplateStub = sandbox.stub(field, 'searchResultTemplate'),
                result = app.data.createBean('Baz', {
                    full_name: 'Foo Bar'
                });

            result.module = 'Baz';
            result.searchInfo = {
                highlighted: [{
                    label: 'LBL_ACCOUNT_NAME',
                    module: 'Baz',
                    text: 'Bar Enterprises Inc.'
                }]
            };

            field.formatSearchResult(result);
            expect(searchResultTemplateStub.lastCall.args).toEqual([{
                module: 'Baz',
                name: 'Foo Bar',
                field_name: 'LBL_ACCOUNT_NAME',
                field_value: 'Bar Enterprises Inc.'
            }]);
        });
    });
});
