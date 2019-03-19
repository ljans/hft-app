<?php

// Service setup
if(PHP_SAPI != 'cli') exit;
chdir(__DIR__); // sys32 by default

// Load dependencies
require '../code/controller.php';
require '../code/service.php';
require '../code/cache.php';

// Construct controller and cache
$controller = new Controller();
$cache = new Cache($controller);

// Setup the service
$service = new Service([
	'service' => 'hft-app',
	'display' => 'HFT App Aktualisierungsdienst',
	'description' => utf8_decode('Lädt aktuelle Daten von verschiedenen Quellen in den lokalen Cache.'),
	'params' => __FILE__.' run',
], [$cache, 'cycle']);

// Process argument
switch(count($argv) == 2 ? $argv[1] : NULL) {
	
	// Install the service
	case 'install': {
		print $service->install();
		break;
	}
	
	// Uninstall the service
	case 'uninstall': {
		print $service->uninstall();
		break;
	}
	
	// Run the service
 	case 'run': {
		$service->run();
		break;
	}
	
	// Invalid argument
	default: throw new InvalidArgumentException();
}