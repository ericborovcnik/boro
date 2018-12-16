<?php
/**
 * Generische Controller-Klasse
 * @author		Eric Borovcnik
 * @version		2018-12-16	eb
 */
abstract class IO_Abstract {

	const CHECK_LOGIN = true;							//	true erzwingt eine angemeldete Sitzung
	const CHECK_DEVELOPER = false;				//	true erzwingt einen Benutzer mit Developer-Status
	const ACCESS = '';										//	Minimal gefordertes Leserecht
	
	/**
	 * Die an den Controller übermittelten Parameter als stdClass aufbereitet
	 * @var stdClass $params
	 */
	public $params;
	
	/**
	 * Konstruktor
	 * @param stdClass $params						Aufbereitete stdClass (->server.php)
	 */
	public function __construct($params) {
		$this->params = $params;
	}
	
}