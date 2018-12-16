<?php
/**
 * Protokoll-Klasse
 * Erzeugt Ausgaben ins Protokoll
 * @author		Eric Borovcnik
 * @version		2018-12-15	eb
 */
abstract class Log {

	const TRACE_ALLWAYS = true;						//	true zeigt bei allen Ausgaben den Trace
	
	/**
	 * Erzeugt eine Protokollausgabe
	 * @param *														Alle Parameter werden in die Protokollausgabe überführt
	 */
	public static function out() {
		if(TRACE_ALLWAYS)		static::_outTrace();
		foreach(func_get_args() as $arg) {
			static::_out(Util::dump($arg));
		}
	}

	/**
	 * Erzeugt eine Fehlermeldung in das Protokoll
	 * @param *														Alle Parameter werden in die Protokollausgabe überführt
	 */
	public static function err() {
		static::_out('ERROR');
		static::_outTrace();
		foreach(func_get_args() as $arg) {
			static::_out(Util::dump($arg));
		}
	}

	/**
	 * Erzeugt eine Protokollausgabe, sofern APP_DEBUG gesetzt ist
	 * @param *														Alle Parameter werden in die Protokollausgabe überführt
	 */
	public static function debug() {
		if(!SYS_DEBUG)		return;
		static::_outTrace();
		foreach(func_get_args() as $arg) {
			static::_out(Util::dump($arg));
		}
	}

	/**
	 * Erzeugt eine Ausgabe in die Log-Datei
	 * @param string $message							Ausgabemeldung
	 */
	private static function _out($message) {
		$fh = fopen(APP_PATH.'debug.log', 'a');
		if(!$fh)		return;
		fwrite($fh, $message);
		fclose($fh);
	}

	/**
	 * Ermittelt den Backtrace-String
	 */
	private static function _outTrace() {
		$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$line = $bt[1]['line'];
		for($i=1;$i<=2;$i++) {
			array_shift($bt);
		}
		$first = true;
		$trace = '';
		foreach($bt as $item) {
			if(!$first) 		$trace .= ' <<< ';
			$trace .= $item['class'].$item['type'].$item['function'].'('.$line.')';
			$line = $item['line'];
			$first = false;
		}
		static::_out($trace."\n");
	}

}