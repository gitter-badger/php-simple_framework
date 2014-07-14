<?php

namespace core\autoLoad;

abstract class AutoLoader {
	
	static private $routes = [];
	
	static public function loadClass($className)
	{
		$fileName = static::loadRoutine($className);
		self::includeClass($fileName);
	}
	
	static public function addCodeRoute($part)
	{
		if (!in_array($part, self::$routes)) {
			$fileName = implode(DIRECTORY_SEPARATOR, [dirname(dirname(dirname(__FILE__))), 'core', 'autoLoad', "{$part}AutoLoader"]) . '.php';
			if ( self::includeClass($fileName) ) {
				spl_autoload_register(__NAMESPACE__ . "\\{$part}AutoLoader::loadClass");
				array_push(self::$routes, $part);
			}
		}
	}
	
	static abstract protected function loadRoutine($className);
	
	static function includeClass($fileName) 
	{
		$returnValue = false;
		if ( file_exists($fileName) ) {
			require_once $fileName;
			$returnValue = true;
		}
		
		return $returnValue;
	}
}