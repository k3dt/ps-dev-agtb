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
import {seedbed} from '@sugarcrm/seedbed';
import * as request from 'request-promise';

export const updateOpportunityConfig = async (data) => {

    let config = {
        opps_view_by: data['opps_view_by'],
        opps_closedate_rollup: data['opps_close_date']
    };

    let reqOptions = seedbed.api._buildOptions(
        'POST',
        'Opportunities/config',
        false,
        config);

    await request(reqOptions);
};

export const updateForecastConfig = async (data) => {

    let config = {
        is_setup: 1,
        is_upgrade: 0,
        has_commits: 1,
        timeperiod_type: 'chronological',
        timeperiod_interval: 'Annual',
        timeperiod_leaf_interval: 'Quarter',
        timeperiod_start_date: '2018-01-01',
        timeperiod_shown_forward: 2,
        timeperiod_shown_backward: 2,
        forecast_ranges: 'show_binary',
        buckets_dom: 'commit_stage_binary_dom',
        show_binary_ranges: {
            include: {
                min: parseInt(data['show_binary_ranges.include.min'], 10),
                max: parseInt(data['show_binary_ranges.include.max'], 10),
            },
            exclude: {
                min: parseInt(data['show_binary_ranges.exclude.min'], 10),
                max: parseInt(data['show_binary_ranges.exclude.max'], 10),
            }
        },
        show_buckets_ranges: {
            include: {
                min: 85,
                max: 100
            },
            upside: {
                min: 70,
                max: 84
            },
            exclude: {
                min: 0,
                max: 69
            }
        },
        sales_stage_won: [
            'Closed Won'
        ],
        sales_stage_lost: [
            'Closed Lost'
        ],
        show_worksheet_likely: 1,
        show_worksheet_best: 1,
        show_worksheet_worst: 1,
        show_projected_likely: 1,
        show_projected_best: 1,
        show_projected_worst: 0,
        show_forecasts_commit_warnings: 1,
        worksheet_columns: [
            'commit_stage',
            'parent_name',
            'opportunity_name',
            'account_name',
            'date_closed',
            'sales_stage',
            'worst_case',
            'likely_case',
            'best_case'
        ]
    };

    let reqOptions = seedbed.api._buildOptions(
        'POST',
        'Forecasts/config',
        false,
        config);

    await request(reqOptions);
};

/**
 * Representations of pressable keys that aren't text.  These are stored in
 * the Unicode PUA (Private Use Area) code points, 0xE000-0xF8FF.  Refer to
 * http://www.google.com.au/search?&q=unicode+pua&btnG=Search
 *
 * @enum {string}
 */
export const enum KeyCodes {
    NULL =         '\uE000',
    CANCEL =       '\uE001',  // ^break
    HELP =         '\uE002',
    BACK_SPACE =   '\uE003',
    TAB =          '\uE004',
    CLEAR =        '\uE005',
    RETURN =       '\uE006',
    ENTER =        '\uE007',
    SHIFT =        '\uE008',
    CONTROL =      '\uE009',
    ALT =          '\uE00A',
    PAUSE =        '\uE00B',
    ESCAPE =       '\uE00C',
    SPACE =        '\uE00D',
    PAGE_UP =      '\uE00E',
    PAGE_DOWN =    '\uE00F',
    END =          '\uE010',
    HOME =         '\uE011',
    ARROW_LEFT =   '\uE012',
    LEFT =         '\uE012',
    ARROW_UP =     '\uE013',
    UP =           '\uE013',
    ARROW_RIGHT =  '\uE014',
    RIGHT =        '\uE014',
    ARROW_DOWN =   '\uE015',
    DOWN =         '\uE015',
    INSERT =       '\uE016',
    DELETE =       '\uE017',
    SEMICOLON =    '\uE018',
    EQUALS =       '\uE019',

    NUMPAD0 =      '\uE01A',  // number pad keys
    NUMPAD1 =      '\uE01B',
    NUMPAD2 =      '\uE01C',
    NUMPAD3 =      '\uE01D',
    NUMPAD4 =      '\uE01E',
    NUMPAD5 =      '\uE01F',
    NUMPAD6 =      '\uE020',
    NUMPAD7 =      '\uE021',
    NUMPAD8 =      '\uE022',
    NUMPAD9 =      '\uE023',
    MULTIPLY =     '\uE024',
    ADD =          '\uE025',
    SEPARATOR =    '\uE026',
    SUBTRACT =     '\uE027',
    DECIMAL =      '\uE028',
    DIVIDE =       '\uE029',

    F1 =           '\uE031',  // function keys
    F2 =           '\uE032',
    F3 =           '\uE033',
    F4 =           '\uE034',
    F5 =           '\uE035',
    F6 =           '\uE036',
    F7 =           '\uE037',
    F8 =           '\uE038',
    F9 =           '\uE039',
    F10 =          '\uE03A',
    F11 =          '\uE03B',
    F12 =          '\uE03C',

    COMMAND =      '\uE03D',  // Apple command key
}

/**
 *  Update Admin Cookie Consent
 */
export const updateAdminCookieConsent = async () => {
    const options = {
        record:{
            module: 'Users',
            id: 1,
            fields:{
                cookie_consent: 1,
            },
        }
    };
    await seedbed.api.update(options);
};
