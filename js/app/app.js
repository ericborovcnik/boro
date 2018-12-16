/**
 * Main-Application
 * Initialisiert die Ext-Umgebung und startet den Layout-Renderer
 * @author		Eric Borovcnik
 * @version		2018-12-13
 */

var app = {
	
	init:						function() {
		
	},
	
	/**
	 * Ermittelt die Basis-URL
	 */
	getURL:					function() {
		
	}
	
}


Ext.onReady(function() {
	Ext.QuickTips.init();
	//Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
	app.init();
});
