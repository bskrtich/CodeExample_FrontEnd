"use strict";

var apiEndPoint = 'ajax.php';

/*
method is called with params as input, with the result
being fed to the apiCallback function on success, errors
are written to the debug output
*/
function callAPI(action, apiCallback, params){
	if (typeof params == 'undefined' || !params) {
		params = new Object();
	}

	var request = new Object();

	request['action'] = action;
	request['params'] = params;

	$.ajax({
		'type' : 'POST',
		'url' : apiEndPoint,
		'data' : request,
		'complete' : function(jqXHR, textStatus) {
			parseAPIResult(jqXHR, textStatus, action, apiCallback);
		},
		'dataType' : 'json'
	});
}


// Ensures valid JSON was returned and feeds the result to the apiCallback function
function parseAPIResult(jqXHR, textStatus, action, apiCallback){
	try {
		var resultObject = JSON.parse(jqXHR['responseText']);
		writeDebug(JSON.stringify(resultObject, null, 4).replace(/\\n/g, "\n"));
	} catch(err) {
		if (jqXHR['responseText'] != '') {
			writeDebug('Invalid response from server.' + jqXHR['responseText'], 'debugError');
		}
		return;
	}

	if ('error' in resultObject &&
		typeof resultObject['error']['message'] != 'undefined' &&
		typeof resultObject['error']['name'] != 'undefined') {

		writeDebug('API Error: ' + resultObject['error']['message'], 'debugError');

		return;
	} else if (!('result' in resultObject) || jqXHR['status'] != 200) {
		writeDebug('Invalid response from server.' + jqXHR['responseText'], 'debugError');
		return;
	}

	apiCallback(resultObject['result']);
}


// Writes debug to the debug output section of the page, storing the last 10
function writeDebug(text, extraClass){
	var debugCount = 0;
	$('#debug div').each(function(){
		debugCount++;
		if (debugCount > 10)
		$(this).remove();
	});

	var now = new Date();
	var currTime = now.getHours() + ':' + (now.getMinutes() < 10 ? '0' : '') + now.getMinutes() + ':' + (now.getSeconds() < 10 ? '0' : '') + now.getSeconds();
	$('#debug').prepend('<div ' + (typeof extraClass != 'undefined' ? 'class="' + extraClass + '"' : '') + '><b>' + currTime  + '</b><br>' + text + '</div>');
}
