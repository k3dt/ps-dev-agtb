<script src='clients/forecasts/config.js'></script>
<script src='clients/forecasts/helper/hbt-helpers.js'></script>
<script src='clients/forecasts/lib/ClickToEdit.js'></script>
<script src='clients/forecasts/layouts/forecasts/forecasts-layout.js'></script>
<script src='clients/forecasts/views/forecastsWorksheet/forecastsWorksheet.js'></script>
<script src='clients/forecasts/views/tree/tree.js'></script>
<script src='clients/forecasts/views/chartOptions/chartOptions.js'></script>
<script src='clients/forecasts/views/forecastsCommitted/forecastsCommitted.js'></script>
<script src='clients/forecasts/views/forecastsSubnav/forecastsSubnav.js'></script>
<script src='clients/forecasts/views/progress/progress.js'></script>
<script src='clients/forecasts/views/chart/chart.js'></script>
<script src='clients/forecasts/views/alert/alert-view.js'></script>
<script src='modules/Forecasts/tpls/SidecarView.js'></script>
<div class="view-forecastsSubnav subnav"></div>
<div id="alert" class="alert-top"></div>
<div id="core-module">
    <div id="forecasts" style="" >
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span2" id="drawer">
                    <a class="drawerTrig btn btn-mini pull-right"><i class="icon-chevron-left icon-sm"></i></a>
                    <div class="bordered">
                        <div class="view-forecastsFilter"></div>
                        <div class="view-chartOptions"></div>
                        <div class="view-tree"></div>
                    </div>
                </div>
                <div id="charts" class="span10">
                    <div class="row-fluid">
                        <div class="span6">
                            <div class="view-chart"></div>
                        </div>
                        <div class="span6">
                            <div class="tab-pane active" id="overview">
                                <div class="block" id="moduleTwitter">
                                    <div class="view-progress"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row-fluid">
                        <div class="span12">
                            <hr/>
                        </div>
                    </div>
                    <div class="row-fluid">
                        <div class="topline thumbnail span12">
                          <div class="row-fluid view-forecastsCommitted">
                          </div>
                          <hr>
                          <div>
                              <div id="view-sales-rep" style="display:none">
                                  <div class="view-forecastsWorksheet"></div>
                                  <div class="view-summary"></div>
                              </div>
                              <div id="view-manager" style="display:none">
                                  <div class="view-forecastsWorksheetManager"></div>
                                  <div class="view-summary"></div>
                              </div>
                          </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content"></div>

{literal}
<script language="javascript">
    var syncResult, view, layout, html;

    SUGAR.App.sugarAuthStore.set('AuthAccessToken', {/literal}'{$token}'{literal});

    (function(app) {
         app.augment("forecasts", {
            initForecast: function(authAccessToken) {
                app.AUTH_ACCESS_TOKEN = authAccessToken;
                app.AUTH_REFRESH_TOKEN = authAccessToken;
                app.init({
                    el: "forecasts",
                    contentEl: ".content"
                    //keyValueStore: app.sugarAuthStore //override the keyValueStore
                });
                return app;
            }
         });
     })(SUGAR.App);

     //Call initForecast with the session id as token
     var App = SUGAR.App.forecasts.initForecast({/literal}'{$token}'{literal});

    App.config.showBuckets = {/literal}'{$forecast_opportunity_buckets}' == '1'?true:false;{literal}
    App.viewModule = {/literal}'{$module}';{literal}

    // get default selections for filter and category
    App.defaultSelections = {};
    $.ajax(App.config.serverUrl + '/Forecasts/filters', {
        dataType: "json"
    }).done(function(data){
            App.defaultSelections.timeperiod_id = {
                'id': data.timeperiod_id.default
            };
            App.defaultSelections.category = {
                'id': data.category.default
            };
        });
    // get default selections for group_by and dataset
    $.ajax(App.config.serverUrl + '/Forecasts/chartoptions', {
        dataType: "json"
    }).done(function(data){
        App.defaultSelections.group_by = {
            'id': data.group_by.default
        };
        App.defaultSelections.dataset = {
            'id': data.dataset.default
        };
    });

    // should already be logged in to sugar, don't need to log in to sidecar.
    App.api.isAuthenticated = function() {

        // Grab user data
        var userData = $.ajax(App.config.serverUrl + '/Forecasts/me', {
            dataType: "json"
        }).done(function(data){
            //  Set current User data
            App.user.set(data.current_user);
        });

        return true;
    };


    App.api.debug = App.config.debugSugarApi;
    App.start();
</script>
{/literal}
