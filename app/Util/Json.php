<?php
/**
 * Dienstklasse mit JSON-Methoden
 * @author		Eric Borovcnik
 * @version		2018-12-16
 */
abstract class Util_Json {
	
	/**
	 * Erzeugt eine JSON-Nachricht aufgrund der Datenstruktur
	 * @param mixed $data
	 */
	public static function encode($data) {
		try {
			$result = json_encode($data);
		} catch(Exception $e) {
			$result = '{"ok":"0","error":"'.str_replace('"',"'",$e->getMessage()).'"}';
		}
		return $result;
	}
	
	/**
	 * Erzeugt eine Fehlermeldung in JSON-Notation
	 * @param string $erro								Klartext-Fehlermeldung
	 * @param mixed $data									Optionale Inhalte für Antwort
	 */
	public static function error($error='', $data=null) {
		$rc = array(
			'ok'		=>	0,
			'error'	=>	$error
		);
		if($data)		$rc['data'] = $data;
		return self::encode($rc);
	}
	
}