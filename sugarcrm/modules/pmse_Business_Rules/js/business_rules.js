var decision_table,
    brName,
    brModule;

function addDecisionTable(data) {
    var module = 'pmse_Business_Rules';
    $.extend(true, data, {
        language: {
            SINGLE_HIT: translate('LBL_PMSE_BUSINESSRULES_LABEL_SINGLEHIT', module),
            MULTIPLE_HIT: translate('LBL_PMSE_BUSINESSRULES_LABEL_MULTIPLEHIT', module),
            CONDITIONS: translate('LBL_PMSE_LABEL_CONDITIONS', module),
            CONCLUSIONS: translate('LBL_PMSE_LABEL_CONCLUSIONS', module),
            ADD_ROW: translate('LBL_PMSE_TOOLTIP_ADD_ROW', module),
            REMOVE_ROW: translate('LBL_PMSE_BUSINESSRULES_LABEL_REMOVEROW', module),
            ERROR_CONCLUSION_VAR_DUPLICATED: translate('LBL_PMSE_BUSINESSRULES_ERROR_CONCLUSIONVARDUPLICATED', module),
            ERROR_EMPTY_RETURN_VALUE: translate('LBL_PMSE_BUSINESSRULES_ERROR_EMPTYRETURNVALUE', module),
            ERROR_EMPTY_ROW: translate('LBL_PMSE_BUSINESSRULES_ERROR_EMPTYROW', module),
            ERROR_NOT_EXISTING_FIELDS: translate('LBL_PMSE_MESSAGE_REQUIRED_FIELDS_BUSINESSRULES', module),
            ERROR_INCORRECT_BUILD: translate('LBL_PMSE_BUSINESSRULES_ERROR_INCORRECT_BUILD', module),
            MSG_DELETE_ROW: translate('LBL_PMSE_BUSINESSRULES_LABEL_MSGDELETEROW',module),
            LBL_RETURN: translate('LBL_PMSE_LABEL_RETURN', module),
            ERROR_NO_VARIABLE_SELECTED: translate('LBL_PMSE_BUSINESSRULES_ERROR_NOVARIABLE_SELECTED', module),
            ERROR_INVALID_EXPRESSION: translate('LBL_PMSE_BUSINESSRULES_ERROR_INVALIDEXPRESSION', module),
            ERROR_MISSING_EXPRESSION_OR_OPERATOR: translate('LBL_PMSE_BUSINESSRULES_ERROR_MISSINGEXPRESSIONOROPERATOR', module),
            LBL_VARIABLES: translate('LBL_PMSE_ADAM_UI_LBL_VARIABLES', module),
            LBL_CONSTANTS: translate('LBL_PMSE_ADAM_UI_LBL_CONSTANTS', module),
            LBL_ADD_CONDITION: translate('LBL_PMSE_BUSINESSRULES_LABEL_ADD_CONDITION', module),
            LBL_ADD_CONCLUSION: translate('LBL_PMSE_TOOLTIP_ADD_CONCLUSION', module),
            MIN_ROWS: translate('LBL_PMSE_BUSINESSRULES_ERROR_MIN_ROWS', module),
            MIN_CONDITIONS_COLS: translate('LBL_PMSE_BUSINESSRULES_ERROR_MIN_CONDITIONS_COLS', module),
            MIN_CONCLUSIONS_COLS: translate('LBL_PMSE_BUSINESSRULES_ERROR_MIN_CONCLUSIONS_COLS', module)
        }
    });

    decision_table = new DecisionTable(data);

    if (!decision_table.correctlyBuilt) {
        $('#save').hide();
    }

    decision_table.onDirty = function (state) {
        if (state) {
            updateName = brName + " *";
        } else {
            updateName = brName;
        }
        $(".brTitle").text(updateName);
    };

    decision_table.onAddColumn = function () {
        updateDimensions();
    };

    decision_table.onAddRow = function () {
        updateDimensions();
    };

    decision_table.onRemoveColumn = function () {
        updateDimensions()
    };

    decision_table.onRemoveRow = function () {
        updateDimensions()
    };

    $('#businessruledesigner').prepend(decision_table.getHTML());
}

function saveBR(router) {
    var json,
        base64encoded,
        validation = decision_table.isValid();

    if (decision_table && validation.valid) {
        json = decision_table.getJSON();
        base64encoded = JSON.stringify(json);
        url = App.api.buildURL('pmse_Business_Rules', null, {id: decision_table.id});
        attributes = {rst_source_definition: base64encoded};

        App.alert.show('upload', {level: 'process', title: 'LBL_SAVING', autoclose: false});

        App.api.call('update', url, attributes, {
            success: function (data) {
                if (router) {
                    App.alert.dismiss('upload');
                    decision_table.setIsDirty(false, true);
                    goBack(router);
                } else {
                    decision_table.setIsDirty(false);
                    App.alert.dismiss('upload');
                    App.alert.show('br-saving-success', {
                        level: 'success',
                        messages: brName + ' was saved correctly',
                        autoClose: true
                    });
                }
            },
            error: function (err) {
                App.alert.dismiss('upload');
            }
        });
    } else {
        App.alert.show('br-save-error', {
            level: 'error',
            messages: validation.location + ": " + validation.message,
            autoClose: true
        });
    }
};

function updateBRContainer(state) {
    updateDimensions();
}

function updateDimensions() {
    //Calculating -12px because we have this div with padding = 5px and border = 1px
    var w = $(".businessrules").width() - 12;
    updateTableDimension(decision_table, w);
}

function updateTableDimension(table, w) {

    var width = w || $('#decision-tables').width();
    table.setWidth("auto");

    if ($(table.getHTML()).outerWidth() > width) {
        table.setWidth(width);
    }
}

function init(params) {
    var data;

    brName = params.data.name;
    brModule = params.data.rst_module;

    //errorLog = $('#error-log');

    $(window).on("resize", updateDimensions);

    if (params.data && params.data.rst_source_definition) {
        data = JSON.parse(params.data.rst_source_definition);
    } else {
        data = {
            "saveedit":"1",
            "btnSubmitEdit":"Save and Edit",
            "id":params.data.id,
            "name":params.data.name,
            "base_module":params.data.rst_module,
            "type":"single",
            "columns":{
                "conditions":[],
                "conclusions":[]
            },
            "ruleset":[
                {
                    "conditions":[],
                    "conclusions":[]
                }
            ]
        }
    }
    updateBRHeader(brName, brModule);
    addDecisionTable(data);
    updateDimensions();
    decision_table.setIsDirty(false);

}

function goBack(router) {
    //TODO Find a way to avoid go back to the create view
    router.goBack();
}

function cancelAction(router) {
    if (decision_table.getIsDirty()) {
        App.alert.show('dirty-br-confirmation', {
            level: 'confirmation',
            //TODO Add Label with the message when BR is dirty
            messages: 'Do you want to exit without save the changes in the Business Rule?',
            onCancel: function () {
                return;
            },
            onConfirm: function () {
                goBack(router);
            }
        });
    } else {
        goBack(router);
    }
}

function updateBRHeader(name, module) {
    $(".brTitle").text(name);
    $(".brModule").text(module);
}

function renderBusinessRule(uid, layout) {

    //Defining callback when sidebar is closed or opened
    layout.on('sidebar:state:changed', updateBRContainer, this);

    var params = {
        br_uid: uid
    };
    App.api.call("read", App.api.buildURL("pmse_Business_Rules", null, {id: uid }), {}, {
        success: function (response) {
            params.data = response;
            init(params);
        }
    });
}
