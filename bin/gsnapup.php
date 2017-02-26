<?php

use \Symfony\Component\Console\Application;
use \Symfony\Component\Console\Input\InputOption;
use \WSIServices\GSnapUp\Console\Command\Init;
use \WSIServices\GSnapUp\Console\Command\InstanceAdd;
use \WSIServices\GSnapUp\Console\Command\InstanceAvailable;
use \WSIServices\GSnapUp\Console\Command\InstanceDisable;
use \WSIServices\GSnapUp\Console\Command\InstanceEnable;
use \WSIServices\GSnapUp\Console\Command\InstanceList;
use \WSIServices\GSnapUp\Console\Command\InstanceRemove;
use \WSIServices\GSnapUp\Console\Command\InstanceUpdate;
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

if(is_readable(__DIR__.'/version.txt')) {
	$version = file_get_contents(__DIR__.'/version.txt') ?: 'dev';
} else {
	$version = 'dev';
}

// Setup application constants
define('APPLICATION_NAME', 'GSnapUp');
define('CONFIGURATION_NAME', strtolower(APPLICATION_NAME).'.json');
define('APPLICATION_VERSION', $version);

// Initialize application
$application = new Application(APPLICATION_NAME, APPLICATION_VERSION);

// Define application wide CLI option
$application->getDefinition()
	->addOption(new InputOption(
		'working-dir',
		'd',
		InputOption::VALUE_REQUIRED,
		'If specified, use the given directory as working directory',
		getcwd()
	));

// Configure available commands
$application->add(new Init);
$application->add(new InstanceAdd);
$application->add(new InstanceAvailable);
$application->add(new InstanceDisable);
$application->add(new InstanceEnable);
$application->add(new InstanceList);
$application->add(new InstanceRemove);
$application->add(new InstanceUpdate);
$application->add(new Snapshot);

// Start application
$application->run();
