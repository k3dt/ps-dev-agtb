describe('Notifications', function() {
    var app, view,
        moduleName = 'Notifications',
        viewName = 'notifications';

    beforeEach(function() {
        app = SugarTest.app;
    });

    describe('Initialization with default values', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', moduleName, viewName);

        });

        afterEach(function() {
            sinon.collection.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should bootstrap', function() {
            var _initOptions = sinon.collection.stub(view, '_initOptions', $.noop()),
                _initCollection = sinon.collection.stub(view, '_initCollection', $.noop()),
                _initReminders = sinon.collection.stub(view, '_initReminders', $.noop());

            view._bootstrap();

            expect(_initOptions).toHaveBeenCalledOnce();
            expect(_initCollection).toHaveBeenCalledOnce();
            expect(_initReminders).toHaveBeenCalledOnce();
        });

        it('should initialize options with default values', function() {
            view._initOptions();

            expect(view.delay / 60 / 1000).toBe(view._defaultOptions.delay);
            expect(view.limit).toBe(view._defaultOptions.limit);
        });

        it('should initialize collection options with default values', function() {
            var createBeanCollection = sinon.collection.stub(app.data, 'createBeanCollection', function() {
                return {
                    options: {},
                    off: function() {
                    }
                };
            });

            view._initCollection();

            expect(view.collection.options).toEqual({
                params: {
                    order_by: 'date_entered:desc'
                },
                limit: view.limit,
                myItems: true,
                fields: [
                    'date_entered',
                    'id',
                    'is_read',
                    'name',
                    'severity'
                ],
                apiOptions: {
                    skipMetadataHash: true
                }
            });
        });

        describe('should bind listeners on app:socket events', function () {
            beforeEach(function () {
                app.events.on = sinon.spy();
                view.socketOn = sinon.spy();
                view.socketOff = sinon.spy();
            });

            it('should bind socketOn on app app:socket:connect', function () {

                view.initialize({});

                sinon.assert.called(app.events.on);
                sinon.assert.calledWith(app.events.on, 'app:socket:connect');

                for (var i = 0; i < app.events.on.callCount; i++) {
                    var info = app.events.on.getCall(i);
                    if (info.args[0] != 'app:socket:connect') {
                        continue;
                    }
                    expect(info.args[1]).toBeDefined();
                    sinon.assert.notCalled(view.socketOn);

                    info.args[1]();
                    sinon.assert.called(view.socketOn);
                }
            });

            it('should bind socketOff on app app:socket:disconnect', function () {

                view.initialize({});

                sinon.assert.called(app.events.on);
                sinon.assert.calledWith(app.events.on, 'app:socket:disconnect');

                for (var i = 0; i < app.events.on.callCount; i++) {
                    var info = app.events.on.getCall(i);
                    if (info.args[0] != 'app:socket:disconnect') {
                        continue;
                    }
                    expect(info.args[1]).toBeDefined();
                    sinon.assert.notCalled(view.socketOff);

                    info.args[1]();
                    sinon.assert.called(view.socketOff);
                }
            });

        });
    });

    describe('Initialization with metadata overridden values', function() {
        var customOptions = {
            delay: 10,
            limit: 8
        };

        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.testMetadata.addViewDefinition(viewName, customOptions, moduleName);
            SugarTest.testMetadata.set();

            view = SugarTest.createView('base', moduleName, viewName);
        });

        afterEach(function() {
            sinon.collection.restore();
            SugarTest.testMetadata.dispose();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should initialize options with metadata overridden values', function() {
            view._initOptions();

            expect(view.delay / 60 / 1000).toBe(customOptions.delay);
            expect(view.limit).toBe(customOptions.limit);
        });

        it('should initialize collection options with metadata overridden values', function() {
            var createBeanCollection = sinon.collection.stub(app.data, 'createBeanCollection', function() {
                return {
                    options: {},
                    off: function() {
                    }
                };
            });

            view._initCollection();

            expect(view.collection.options).toEqual({
                params: {
                    order_by: 'date_entered:desc'
                },
                limit: view.limit,
                myItems: true,
                fields: [
                    'date_entered',
                    'id',
                    'is_read',
                    'name',
                    'severity'
                ],
                apiOptions: {
                    skipMetadataHash: true
                }
            });
        });
    });

    describe('Pulling mechanism', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', moduleName, viewName);
        });

        afterEach(function() {
            sinon.collection.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should not pull notifications if disposed', function() {
            // not calling dispose() directly due to it setting inherently the
            // collection to null
            view.disposed = true;
            view.pull();

            expect(view.collection.fetch).not.toHaveBeenCalled();
            view.disposed = false;
        });

        it('should not pull notifications if disposed after fetch', function() {
            var fetch = sinon.collection.stub(view.collection, 'fetch', function(o) {
                // not calling dispose() directly due to it setting inherently the
                // collection to null
                view.disposed = true;
                o.success();
            });

            view.pull();

            expect(fetch).toHaveBeenCalledOnce();
            expect(view.render).not.toHaveBeenCalled();
            view.disposed = false;
        });

        it('should not pull notifications if open', function() {
            var isOpen = sinon.collection.stub(view, 'isOpen', function() {
                return true;
            });

            view.pull();

            expect(view.collection.fetch).not.toHaveBeenCalled();
        });

        it('should not pull notifications if open after fetch', function() {
            var fetch = sinon.collection.stub(view.collection, 'fetch', function(o) {
                var isOpen = sinon.collection.stub(view, 'isOpen', function() {
                    return true;
                });

                o.success();
            });

            view.pull();

            expect(fetch).toHaveBeenCalledOnce();
            expect(view.render).not.toHaveBeenCalled();
        });

        it('should set timeout twice once on multiple start pulling calls', function() {
            var pull = sinon.collection.stub(view, 'pull', $.noop()),
                setTimeout = sinon.collection.stub(window, 'setTimeout', $.noop());

            view.startPulling().startPulling();

            expect(pull).toHaveBeenCalledOnce();
            expect(setTimeout).toHaveBeenCalledTwice();
        });

        it('should clear intervals on stop pulling', function() {
            var pull = sinon.collection.stub(view, 'pull', $.noop()),
                _pullReminders = sinon.collection.stub(view, '_pullReminders', $.noop()),
                setTimeout = sinon.collection.stub(window, 'setTimeout', function() {
                    return intervalId;
                }),
                clearTimeout = sinon.collection.stub(window, 'clearTimeout', $.noop()),
                intervalId = 1;

            view.startPulling().stopPulling();

            expect(clearTimeout).toHaveBeenCalledOnce();
            expect(view._intervalId).toBeNull();
        });

        it('should stop pulling on dispose', function() {
            var stopPulling = sinon.collection.stub(view, 'stopPulling', $.noop());

            view.dispose();

            expect(stopPulling).toHaveBeenCalledOnce();
        });

        it('should stop reminders on dispose', function () {
            view._remindersIntervalId = 'SomeRemindersIntervalId';
            clearTimeout = sinon.collection.stub(window, 'clearTimeout', $.noop()),

                view.dispose();

            expect(view._remindersIntervalId).toBeNull();
            expect(clearTimeout).toHaveBeenCalledOnce();
        });

        it('should stop pulling if authentication expires', function() {
            var isAuthenticated = sinon.collection.stub(app.api, 'isAuthenticated', function() {
                    return false;
                }),
                pull = sinon.collection.stub(view, 'pull', $.noop()),
                _pullReminders = sinon.collection.stub(view, '_pullReminders', $.noop()),
                setTimeout = sinon.collection.stub(window, 'setTimeout', function(fn) {
                    fn();
                }),
                stopPulling = sinon.collection.stub(view, 'stopPulling', $.noop());

            view.startPulling();

            expect(pull).toHaveBeenCalledOnce();
            expect(setTimeout).toHaveBeenCalledTwice();
            expect(isAuthenticated).toHaveBeenCalledTwice();
            expect(stopPulling).toHaveBeenCalledTwice();
        });

        it('should stop pulling if connected to socket', function () {
            var isAuthenticated = sinon.collection.stub(app.api, 'isAuthenticated', function () {
                    return true;
            }),
            pull = sinon.collection.stub(view, 'pull', $.noop()),
            setTimeout = sinon.collection.stub(window, 'setTimeout', function (fn) {
                    fn();
            });

            view.isSocketConnected = true;
            view._pullAction();

            expect(pull).not.toHaveBeenCalled();
            expect(setTimeout).not.toHaveBeenCalled();
        });
    });

    describe('Socket mechanism', function () {
        beforeEach(function () {
            view = SugarTest.createView('base', moduleName, viewName);
        });

        afterEach(function () {
            sinon.collection.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('socket off', function () {
            view.isSocketConnected = true;
            view.startPulling = sinon.collection.stub();
            app.socket.off = sinon.collection.stub();
            view.catchNotification = sinon.collection.stub();

            view.socketOff();

            expect(view.isSocketConnected).toBeFalsy();

            sinon.assert.called(view.startPulling);
            sinon.assert.called(app.socket.off);
            sinon.assert.calledWith(app.socket.off, 'notification');

            app.socket.off.getCall(0).args[1]();
            sinon.assert.called(view.catchNotification);
        });

        it('socket on', function () {
            view.isSocketConnected = false;
            view.stopPulling = sinon.collection.stub();
            view.catchNotification = sinon.collection.stub();
            app.socket.on = sinon.collection.stub();

            view.socketOn();

            expect(view.isSocketConnected).toBeTruthy();

            sinon.assert.called(view.stopPulling);
            sinon.assert.called(app.socket.on);
            sinon.assert.calledWith(app.socket.on, 'notification');

            app.socket.on.getCall(0).args[1]();
            sinon.assert.called(view.catchNotification);
        });

        it('catchNotification', function () {
            var catchedNotif = 'catched Notif';
            view.transferToCollection = sinon.collection.stub();
            view._buffer = [];

            view.catchNotification(catchedNotif);

            expect(view._buffer[0]).toBe(catchedNotif);
            sinon.assert.called(view.transferToCollection);
        });

        describe('transferToCollection', function () {
            beforeEach(function () {
                sinon.stub(view, 'reRender');
                sinon.stub(app.data, 'createBean');
            });

            afterEach(function () {
                view.reRender.restore();
                app.data.createBean.restore();
                view.collection = null;
            });

            it('check calling transferToCollection before bootstrap', function () {
                view._buffer = [
                    {data: 'someData1', _module: 'module'},
                    {data: 'someData2', _module: 'module'}
                ];

                view.collection = null;

                view.transferToCollection();

                sinon.assert.notCalled(view.reRender);
            });

            it('check calling transferToCollection after bootstrap', function () {
                var buffer = [{data: 'someData1', _module: 'module'}, {data: 'someData2', _module: 'module'}],
                    models = ['Model1', 'Model2'];

                view._buffer = buffer;

                app.data.createBean
                    .withArgs(buffer[0]['_module'], _.clone(buffer[0])).returns(models[0])
                    .withArgs(buffer[1]['_module'], _.clone(buffer[1])).returns(models[1]);

                view.collection = {
                    add: sinon.spy()
                };
                view.transferToCollection();

                sinon.assert.called(view.reRender);
                sinon.assert.calledTwice(app.data.createBean);

                sinon.assert.calledTwice(view.collection.add);
                sinon.assert.calledWith(view.collection.add, models[0]);
                sinon.assert.calledWith(view.collection.add, models[1]);
            });

            it('check calling transferToCollection after bootstrap if empty buffer', function () {
                view._buffer = [];

                app.data.createBean

                view.collection = {
                    add: sinon.spy()
                };

                view.transferToCollection();

                sinon.assert.called(view.reRender);
                sinon.assert.notCalled(app.data.createBean);
                sinon.assert.notCalled(view.collection.add);
            });
        });
    });

    describe('Reminders', function() {
        beforeEach(function() {
            var meta = {
                remindersFilterDef: {
                    reminder_time: { $gte: 0},
                    status: {$equals: 'Planned'}
                },
                remindersLimit: 100
            };

            view = SugarTest.createView('base', moduleName, viewName, meta);
        });

        afterEach(function() {
            sinon.collection.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should initialize collections for Meetings and Calls', function() {

            sinon.collection.stub(app.data, 'createBeanCollection', function() {
                return {
                    options: {},
                    off: function() {
                    }
                };
            });
            sinon.collection.stub(app.lang, 'getAppListStrings', function() {
                return {
                    '60': '1 minute prior',
                    '300': '5 minutes prior',
                    '600': '10 minutes prior',
                    '900': '15 minutes prior',
                    '1800': '30 minutes prior',
                    '3600': '1 hour prior',
                    '7200': '2 hours prior',
                    '10800': '3 hours prior',
                    '18000': '5 hours prior',
                    '86400': '1 day prior'
                };
            });

            view.delay = 300000; // 5 minutes for each pull;
            view._initReminders();

            _.each(['Calls', 'Meetings'], function(module) {
                expect(view._alertsCollections[module].options).toEqual({
                    limit: 100,
                    fields: ['date_start', 'id', 'name', 'reminder_time', 'location', 'parent_name']
                });
            });

            expect(view.reminderMaxTime).toBe(86700); // 1 day + 5 minutes
        });


        describe('Check reminders', function() {

            var reminderModule = 'Meetings';

            beforeEach(function() {

                var meta = {
                    fields: [],
                    views: [],
                    layouts: []
                };
                app.data.declareModel(reminderModule, meta);

            });

            afterEach(function() {
                app.data.reset(reminderModule);
            });

            it('Shouldn\'t check reminders if authentication expires', function() {
                var isAuthenticated = sinon.collection.stub(app.api, 'isAuthenticated', function() {
                        return false;
                    }),
                    setTimeout = sinon.collection.stub(window, 'setTimeout', $.noop()),
                    stopPulling = sinon.collection.stub(view, 'stopPulling', $.noop());

                view.checkReminders();

                expect(setTimeout).not.toHaveBeenCalled();
                expect(isAuthenticated).toHaveBeenCalledOnce();
                expect(stopPulling).toHaveBeenCalledOnce();
            });

            it('Should show reminder if need', function() {

                var now = new Date('2013-09-04T22:45:56+02:00'),
                    dateStart = new Date('2013-09-04T23:15:16+02:00'),
                    clock = sinon.useFakeTimers(now.getTime(), 'Date'),
                    setTimeout = sinon.collection.stub(window, 'setTimeout', $.noop()),
                    _showReminderAlert = sinon.collection.stub(view, '_showReminderAlert'),
                    isAuthenticated = sinon.collection.stub(app.api, 'isAuthenticated', function() {
                        return true;
                    }),
                    model = new app.data.createBean(reminderModule, {
                        'id': '105b0b4a-1337-e0db-b448-522784b92270',
                        'name': 'Discuss pricing',
                        'date_modified': '2013-09-05T00:59:00+02:00',
                        'description': 'Meeting',
                        'date_start': dateStart.toISOString(),
                        'reminder_time': '1800'
                    });

                view._initReminders();
                view._alertsCollections[reminderModule].add(model);
                view.dateStarted = now.getTime();
                view._remindersIntervalStamp = view.dateStarted - 60000;
                view.checkReminders();

                expect(_showReminderAlert).toHaveBeenCalledWith(model);

                clock.restore();
            });
        });
    });

    describe('Notification favicon badge', function() {
        beforeEach(function() {

            // Library mock
            Favico = function() {
                return {
                    badge: $.noop,
                    reset: $.noop
                };
            };

            view = SugarTest.createView('base', moduleName, viewName);
        });

        afterEach(function() {
            sinon.collection.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;

            // remove Libarary mock
            delete Favico;
        });

        using('different counts', [
                [23, -1, 23],
                [7, 7, '7+']
            ], function(length, offset, expected) {
                it('should update favicon badge with the correct unread notifications', function() {
                    view._bootstrap();

                    var badge = sinon.collection.stub(view.favicon, 'badge');
                    view.collection.length = length;
                    view.collection.next_offset = offset;
                    view.collection.trigger('reset');

                    expect(badge).toHaveBeenCalledWith(expected);
                });
            }
        );

        it('should reset favicon badge if authentication expires or user logout', function() {
            view._bootstrap();

            var resetStub = sinon.collection.stub(view.favicon, 'reset');
            sinon.collection.stub(app.api, 'isAuthenticated', function() {
                return false;
            });

            view.render();

            expect(resetStub).toHaveBeenCalledOnce();
        });
    });
});
