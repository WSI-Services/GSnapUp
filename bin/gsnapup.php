<?php

use \WSIServices\GSnapUp\Console\Application;
use \WSIServices\GSnapUp\Console\Command\Init;
use \WSIServices\GSnapUp\Console\Command\InstanceAdd;
use \WSIServices\GSnapUp\Console\Command\InstanceAvailable;
use \WSIServices\GSnapUp\Console\Command\InstanceDisable;
use \WSIServices\GSnapUp\Console\Command\InstanceEnable;
use \WSIServices\GSnapUp\Console\Command\InstanceList;
use \WSIServices\GSnapUp\Console\Command\InstanceRemove;
use \WSIServices\GSnapUp\Console\Command\InstanceUpdate;
use \WSIServices\GSnapUp\Console\Command\Scheduled;
use \WSIServices\GSnapUp\Console\Command\Snapshot;

// Verify API is command line interface
if(PHP_SAPI !== 'cli') {
	echo 'Warning: GSnapUp should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI'.PHP_EOL;
	exit();
}

// Load application autoload
foreach([
	__DIR__ . '/../vendor/autoload.php',	// Path in application context (project base)
	__DIR__ . '/../../../autoload.php'		// Path in vendor context (dependency)
] as $path) {
	if(file_exists($path)) {
		require_once $path;
		break;
	}
}

// Initialize application
$application = new Application('GSnapUp');

// Load version information
$application->setVersionFromFile(__DIR__.'/version.txt');

// Configure available commands
$application->add(new Init);
$application->add(new InstanceAdd);
$application->add(new InstanceAvailable);
$application->add(new InstanceDisable);
$application->add(new InstanceEnable);
$application->add(new InstanceList);
$application->add(new InstanceRemove);
$application->add(new InstanceUpdate);
$application->add(new Scheduled);
$application->add(new Snapshot);

// Start application
$application->run();
