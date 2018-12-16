<?php
/**
 * Einstiegspunkt für sämtliche Server-Anfragen
 * @author		Eric Borovcnik
 * @version		2018-12-15	eb
 */

if($argv) {
	//	###		KONSOLE		#############################################################################
	//	php server.php "class::method&p1=1&p2=2&p3=3"
	//	php server.php	class::method p1=1 p2=2 p3=3
	define('APP_PATH', dirname($argv[0]).DIRECTORY_SEPARATOR);
	$params = array();
	for($i=1; $i<count($argv); $i++) {
		$paramArr = explode('&',$argv[$i]);
		$params = array_merge($params,$paramArr);
	}
	foreach($params as $key => $value) {
		$p = strpos($value, '=');
		if($p) {
			$params[substr($value,0,$p)] = substr($value,$p+1);
		} else {
			$params[$value] = '';
		}
		unset($params[$key]);
	}
} else {
	//	###		BROWSER		#############################################################################
	//	https://...io.php?class::method&p1=1&p2=2&p3=3
	define('APP_PATH', realpath(dirname($_SERVER['SCRIPT_FILENAME'])).DIRECTORY_SEPARATOR);
	$params = array_merge($_POST, $_GET);
}

//	###		INIT CONFIG		###########################################################################
require_once APP_PATH.'config.php';
ini_set('error_reporting',						E_ALL ^ E_NOTICE);
ini_set('error_log',									APP_PATH.'debug.log');
ini_set('max_execution_time',					0);
ini_set('output_handler',							'ob_gzhandler');
ini_set('memory_limit',								'1024M');
ini_set('post_max_size',							SYS_MAX_UPLOAD);
ini_set('upload_max_filesize',				SYS_MAX_MEMORY);

spl_autoload_register(function($class) {
	$file = APP_PATH.'app'.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $class).'.php';
	if(file_exists($file))		include	$file;
});

//	###		EXECUTE COMMAND		#######################################################################
/**
 * Erzeugt die konkrete PHP-Objektinstanz und liefert das Ergebnis zurück
 * @param array $params 
 */
function processRequest($params=null) {
	if(!$params)		return;
	$baseClass = '';
	$method = '';
	foreach($params as $param => $value) {
		if($value === '') {
			if($baseClass === '') {
				$arrClass = explode('::', $param);
				$baseClass = $arrClass[0];
				$method = $arrClass[1] ? $arrClass[1] : 'run';
				unset($params[$param]);
			} else {
				$params[$param] = true;
			}
		}
	}
	$class = Util::getClass($baseClass);
	//
	//	Klasse existiert?
	if(!$class)																		return Util_Json::error("[$baseClass] - invalid class.");
	//
	//	Klasse ist IO_Abstract?
	if(!is_subclass_of($class, 'IO_Abstract'))		return Util_Json::error("[$baseClass] - no subclass of IO_Abstract.");
	//
	//	Methode unterstützt?
	if(!method_exists($class, $method))						return Util_Json::error("[$baseClass::$method] - method not implemented.");
// 	//
// 	//	Klasse erfordert Login-Status?
// 	if($class::ACCESS) {
// 		if(!User::canRead($class::ACCESS)) {
// 			Log::debug('['.$class::ACCESS.'] - access denied');
// 			return Util::jsonErrorAccessDenied();
// 		}
// 	}
// 	/*
// 	 * Class enforces user to be admin
// 	 */
// 	if($class::CHECK_DEVELOPER) {
// 		if(!User::isDeveloper()) {
// 			Log::debug($class.' needs developer');
// 			return Util::jsonErrorAccessDenied();
// 		}
// 	}
	
	//
	//	Erzeuge die Controller-Klasse und führe die Methode aus
	try {
		$obj = new $class(Util::arrayToObject($params));
		$rc = @$obj->$method();
		return $rc;
	} catch(Exception $e) {
		Log::out('Exception raised');
		Log::err($e->getMessage(), $e->getTraceAsString());
		return Util_Json::error($e->getMessage());
	}
	
}

echo processRequest($params);
