/**
 * Remote-Query als Objekt oder direkter Aufruf
 * @author		Eric Borovcnik
 * @copyright	reamis red ag
 * @version		2018-01-31	eb	api
 * @version		2018-04-04	eb	field
 * @version		2018-04-10	eb	store
 * @version		2018-04-12	eb	autoquery, autoresponse, lastresopnse
 */

/**
 * Erzeugt aus einer offenen API-Spezifikation ein normiertes Anfrageobjekt bzw. führt die Anfrage direkt aus
 * @param cfg														'module::action'
 * 																			{api,params,fn,...}
 * @param scope													Kontext für fn
 */
var cfg = function(cfg, scope) {
	
	/**
	 * Erzeugt eine Server-URL anhand der rc-Spezifikatin
	 */
	var getURL = function() {
		var url = app.URL + '?module='+query.module+'&action='+query.action;
		var params = query.params;
		if(typeof(params) == 'function')		params = params();
		for(var i in params) {
			if(!params.hasOwnProperty(i))		continue;
			url += '&'+i+'='+params[i];
		}
		return url;
	}
	
	/**
	 * Führt den Remote-Query aus und verarbeitet die Rückmeldung
	 */
	var run = function(fn) {
		if(!query.valid) {
			var r = {
				'success':		false,
				'msg':				'',
				'error':			'invalid API-specification'
			};
			query.lastresponse = r;
			if(typeof(query.fn) == 'function') 	query.fn.call(query.scope, r);
			if(typeof(fn) == 'function')				fn.call(query.scope, r);
			if(query.autoresponse && !r.success && query.fn == undefined && fn == undefined && r.error) {
				console.log(r.error);
			}
			return;
		}
		var params;
		if(typeof(query.params) == 'function') {
			params = query.params();
		} else {
			params = query.params;
		}
		params.module = query.module;
		params.action = query.action;
		Ext.Ajax.request({
			params:					params,
			timeout:				60000,
			callback:				function(oOptions, success, response) {
				var result = {};
				if(success) {
					result = Ext.util.JSON.decode(response.responseText);
					//	languagekeys?
					if(result.lang != undefined) {
						lib.language.register(result.lang);
						delete(result.lang);
					}
					query.lastresponse = result;
				} else {
					if(response.status == '403') {
						window.location.reload();
						return;
					} else if(response.status == '0' && response.statusText == '') {
						if(query.ignoretimeout) {
							result.success = true;
							result.error = '';
						} else {
							result.success = false;
							result.error = 'Timeout';
						}
					} else {
						result.success = false;
						result.error = response.status+': '+response.statusText;
					}
				}
				query.lastresponse = result;
				if(typeof(query.fn) == 'function')		query.fn.call(query.scope, result);
				if(typeof(fn) == 'function')					fn.call(query.scope, result);
				if(query.autoresponse && !result.success && query.fn == undefined && fn == undefined && result.error) {
					console.log(result.error);
				}
			},
			url:						app.URL,
			scope:					query.scope
		});
	}

	/**
	 * Initialisiert die Konfiguration und erzeugt die Zielstruktur
	 */
	var initCfg = function() {
		var rc = {
			api:            '',
			module:					'',
			action:					'',
			autoquery:			true,
			ignoretimeout:	false,
			autoresponse:		true,
			params:					{},
			lastresponse:		{},
			valid:					false,
			scope:					scope
		};
		if(typeof(cfg) == 'string') {
			var comp = cfg.split('::');
			rc.module = comp[0];
			rc.action	= comp[1] == undefined ? 'run' : comp[1];
		}
		if(cfg != null && typeof(cfg) == 'object') {
			if(cfg.api)       {
				
				var comp = cfg.api.toString().split('::');
				rc.module = comp[0];
				rc.action = comp[1] == undefined ? 'run' : comp[1];
			}
			if(cfg.module				!= undefined)	rc.module = cfg.module;
			if(cfg.action				!= undefined)	rc.action = cfg.action;
			if(cfg.params				!= undefined)	rc.params = cfg.params;
			if(cfg.fn						!= undefined)	rc.fn = cfg.fn;
			if(cfg.autoquery 		!= undefined)	rc.autoquery = cfg.autoquery;
			if(cfg.autoresponse	!= undefined)	rc.autoresponse = cfg.autoresponse;
		}
		if(rc.module && rc.action) {
			rc.api = rc.module+'::'+rc.action;
			rc.valid = true;
		} else {
			rc.api = '';
			rc.valid = false;
		}
		return rc;
	}
	
	var query = initCfg();
	query.run = run;
	query.getURL = getURL;
	if(query.autoquery)			query.run();
	return query;
}

Ext.apply(lib, {
	query:					cfg
});
cfg = undefined;
delete cfg;