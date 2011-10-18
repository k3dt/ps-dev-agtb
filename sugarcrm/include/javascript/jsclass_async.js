/*
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: jsclass_async.js 51719 2009-10-22 17:18:00Z mitani $

//////////////////////////////////////////////////////////////////
// called on the return of a JSON-RPC async request,
// and calls the display() method on the widget registered
// in the registry at the request_id key returned by the server


//////////////////////////////////////////////////////////////////

/**
 * This is the callback function called from SugarRPCClient.prototype.call_method
 * found below.
 * @param o The response object returned by YUI2's ajax request.
 */
function method_callback (o) {
    var resp = YAHOO.lang.JSON.parse(o.responseText),
        request_id = o.tId,
        result = resp.result;

	if(result == null) {
		//BEGIN SUGARCRM flav=int ONLY
	    if (typeof(o) == 'object' && typeof (o.statusText) != 'undefined') {
			alert("Error from json server: " + o.statusText);
	    } else {
			alert("Error calling json server: " + o.statusText);
	    }
	   	//END SUGARCRM flav=int ONLY
	    return;
	}
    reqid = global_request_registry[request_id];
	if(typeof (reqid)  != 'undefined') {
	    widget = global_request_registry[request_id][0];
	    method_name = global_request_registry[request_id][1];
	    widget[method_name](result);
	}
}

//////////////////////////////////////////////////
// class: SugarVCalClient
// async retrieval/parsing of vCal freebusy info
//
//////////////////////////////////////////////////

SugarClass.inherit("SugarVCalClient","SugarClass");

function SugarVCalClient() {
	this.init();
}

SugarVCalClient.prototype.init = function() {}

SugarVCalClient.prototype.load = function(user_id, request_id) {
    this.user_id = user_id;

    // Bug 44239: Removed reliance on jsolait
    YAHOO.util.Connect.asyncRequest('GET', './vcal_server.php?type=vfb&source=outlook&user_id=' + user_id, {
        success: function (result) {
            if (typeof GLOBAL_REGISTRY.freebusy == 'undefined') {
                GLOBAL_REGISTRY.freebusy = new Object();
            }
            if (typeof GLOBAL_REGISTRY.freebusy_adjusted == 'undefined') {
                GLOBAL_REGISTRY.freebusy_adjusted = new Object();
            }
            // parse vCal and put it in the registry using the user_id as a key:
            GLOBAL_REGISTRY.freebusy[user_id] = SugarVCalClient.prototype.parseResults(result.responseText, false);
            // parse for current user adjusted vCal
            GLOBAL_REGISTRY.freebusy_adjusted[user_id] = SugarVCalClient.prototype.parseResults(result.responseText, true);
            // now call the display() on the widget registered at request_id:
            global_request_registry[request_id][0].display();
        },
        failure: function(result) { this.success(result); },
        argument: { result: result }
    });
}

// parse vCal freebusy info and return object
SugarVCalClient.prototype.parseResults = function(textResult, adjusted) {
    var match = /FREEBUSY.*?\:([\w]+)\/([\w]+)/g;
    //  datetime = new SugarDateTime();
    var result;
    var timehash = new Object();
    var dst_start;
    var dst_end;

    if (GLOBAL_REGISTRY.current_user.fields.dst_start == null)
        dst_start = '19700101T000000Z';
    else
        dst_start = GLOBAL_REGISTRY.current_user.fields.dst_start.replace(/ /gi, 'T').replace(/:/gi, '').replace(/-/gi, '') + 'Z';

    if (GLOBAL_REGISTRY.current_user.fields.dst_end == null)
        dst_end = '19700101T000000Z';
    else
        dst_end = GLOBAL_REGISTRY.current_user.fields.dst_end.replace(/ /gi, 'T').replace(/:/gi, '').replace(/-/gi, '') + 'Z';

    gmt_offset_secs = GLOBAL_REGISTRY.current_user.fields.gmt_offset * 60;
    // loop thru all FREEBUSY matches
    while (((result = match.exec(textResult))) != null) {
        var startdate;
        var enddate;
        if (adjusted) {// send back adjusted for current_user
            startdate = SugarDateTime.parseAdjustedDate(result[1], dst_start, dst_end, gmt_offset_secs);
            enddate = SugarDateTime.parseAdjustedDate(result[2], dst_start, dst_end, gmt_offset_secs);
        }
        else { // GMT
            startdate = SugarDateTime.parseUTCDate(result[1]);
            enddate = SugarDateTime.parseUTCDate(result[2]);
        }

        var startmins = startdate.getUTCMinutes();

        // pick the start slot based on the minutes
        if (startmins >= 0 && startmins < 15) {
            startdate.setUTCMinutes(0);
        }
        else if (startmins >= 15 && startmins < 30) {
            startdate.setUTCMinutes(15);
        }
        else if (startmins >= 30 && startmins < 45) {
            startdate.setUTCMinutes(30);
        }
        else {
            startdate.setUTCMinutes(45);
        }

        // starting at startdate, create hash of each busy 15 min
        // timeslot and store as a key
		while (startdate.valueOf() < enddate.valueOf()) {
			var hash = SugarDateTime.getUTCHash(startdate);
			if (typeof (timehash[hash]) == 'undefined') {
				timehash[hash] = 0;
			}
			timehash[hash] += 1;
			startdate = new Date(startdate.valueOf() + (15 * 60 * 1000));

		}
        return timehash;
    }


}

SugarVCalClient.parseResults = SugarVCalClient.prototype.parseResults;
//////////////////////////////////////////////////
// class: SugarRPCClient
// wrapper around async JSON-RPC client class
//
//////////////////////////////////////////////////
SugarRPCClient.allowed_methods = ['retrieve','query','save','set_accept_status','get_objects_from_module', 'email', 'get_user_array', 'get_full_list'];

SugarClass.inherit("SugarRPCClient","SugarClass");

function SugarRPCClient() {
	this.init();
}

/*
 * PUT NEW METHODS IN THIS ARRAY:
 */
SugarRPCClient.prototype.allowed_methods = ['retrieve','query','get_objects_from_module'];

SugarRPCClient.prototype.init = function() {
	this._showError= function (e){
		alert("ERROR CONNECTING to: ./index.php?entryPoint=json_server, ERROR:"+e);
	}
	this.serviceURL = './index.php?entryPoint=json_server';
}

/**
 * Note: This method used to depend on JSOlait which is now removed. It has been reworked to use YUI for the aynchronous call
 * and the synchronous call in sugar_3.js.
 * @param method
 * @param args
 * @param synchronous Pass in true if synchronous call is desired
 */
SugarRPCClient.prototype.call_method = function(method, args, synchronous) {
    var result,
        transaction,
        post_data = YAHOO.lang.JSON.stringify({method: method, id: 1, params: [args]});

    synchronous = synchronous || false;

    try {
        if (synchronous) {
            result = http_fetch_sync(this.serviceURL, post_data);
            result = YAHOO.lang.JSON.parse(result.responseText).result;
            return result;
        } else { // asynchronous call
            // note: Unfortunately we don't have a separate error handler and it is built into the method_callback. Maybe a future todo.
            transaction = YAHOO.util.Connect.asyncRequest('POST', this.serviceURL, {success: method_callback, failure: method_callback}, post_data);
            return transaction.tId;
        }
    } catch(e) { // error before calling server
        this._showError(e);
    }
}

var global_rpcClient =  new SugarRPCClient();