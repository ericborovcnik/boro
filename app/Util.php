<?php
/**
 * Allgemeine Dienstklasse
 * @author		Eric Borovcnik
 * @version		2018-12-15	eb
 */
abstract class Util {
	
	//	###		DATENTYPEN / KONVERSIONEN		###########################################################

	/**
	 * Konvertiert ein assoziatives Array in ein Objekt
	 * @param array $array								Array
	 * @return stdClass										zur stdClass konvertiertes Array
	 */
	public static function arrayToObject($array) {
		$data = new stdClass();
		foreach($array as $key => $value) {
			$data->{$key} = $value;
		}
		return $data;
	}
	
	/**
	 * Erzeugt eine Zeichenkette aus Array- und Object-Strukturen für Analyse-Dumps
	 * @param mixed $msg									Inhalt
	 * @param integer $indent							Einrückungs-Level
	 * @param string $newline							Newline-Sequenz
	 * @return string
	 */
	public static function dump($msg=null, $indent=0, $newline="\n") {
		$tabsize = 3;
		$rc = '';
		$tab = str_repeat(' ', $indent*$tabsize);
		if(is_array($msg)) {																		//	ARRAY
			$rc .= 'array('.count($msg).') {'.$newline;
			$indent++;
			$tab = str_repeat(' ', $indent*$tabsize);
			//	Maxmale Länge des Attributeschlüssels ermitteln
			$max = 0;
			foreach($msg as $key => $item) {
				if(strlen($key) > $max) $max = strlen($key);
			}
			//	Alle Elemente ausgeben...
			foreach($msg  as $key => $item) {
				$rc .= str_pad($tab.'['.$key.']', $max).' =>  ';
				if(is_array($item) || is_object($item)) {
					$rc .= self::dump($item, $indent, $newline);
				} elseif(is_bool($item)) {
					$rc .= ($item ? 'true' : 'false').$newline;
				} else {
					$rc .= $item.$newline;
				}
			}
			$indent--;
			$tab = str_repeat(' ', $indent*$tabsize);
			$rc .= $tab .'}'.$newline;
		} elseif(is_object($msg)) {															//	OBJECT
			$class = get_class($msg);
			$properties = get_object_vars($msg);
			$rc .= $class.'('.count($properties).') {'.$newline;
			$indent++;
			$tab = str_repeat(' ', $indent*$tabsize);
			//	Maximale Länge des Attributeschlüssels bestimmen
			$max = 0;
			foreach($properties as $key => $item) {
				if(strlen($key) > $max) $max = strlen($key);
			}
			foreach($properties as $key => $item) {
				$rc .= str_pad($tab.'->'.$key, $max).' =  ';
				if(is_array($item) || is_object($item)) {
					$rc .= self::dump($item, $indent, $newline);
				} elseif(is_bool($item)) {
					$rc .= ($item ? 'true' : 'false').$newline;
				} else {
					$rc .= $item.$newline;
				}
			}
			$methods = get_class_methods($class);
			foreach($methods as $method) {
				$rc .= $tab.'->'.$method.'()'.$newline;
			}
			$indent--;
			$tab = str_repeat(' ',$indent*$tabsize);
			$rc .= $tab.'}'.$newline;
		} elseif(is_bool($var)) {																//	BOOLEAN
			$rc .= $tab.($msg ? 'true' : 'false'); 
		} else {																								//	DEFAULT
			$rc .= $tab.$msg;
		}
		$rc .= $newline;
		return $rc;
	}
	
	//	###		INDITIVDUALISIERUNG		#################################################################
	
	/**
	 * Ermittelt eine Klasse auf Basis von $baseClass und prüft eine allfällige Spezialisierung
	 * @param stirng $baseClass						Basisklasse
	 * @param string $customer						Optionaler Kunden-Tag für Spezialisierung
	 * @return string											Zielklasse oder null, wenn Klasse nicht verfügbar
	 */
	public static function getClass($baseClass, $customer='') {
		if($customer) {
			$class = 'Cust_'.$customer.'_'.$baseClass;
			try {
				if(@class_exists($class))			return $class;
			} catch(Exception $e) {}
		}
		try {
			if(@class_exists($baseClass))		return $baseClass;
		} catch(Exception $e) {}
	}
	
}