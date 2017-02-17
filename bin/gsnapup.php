#!/usr/bin/env php
<?php

use \Symfony\Component\Console\Application;
use \Symfony\Component\Console\Input\InputOption;
use \WSIServices\GSnapUp\Console\Command\Init;
use \WSIServices\GSnapUp\Console\Command\InstanceAdd;
use \WSIServices\GSnapUp\Console\Command\InstanceAvailable;
use \WSIServices\GSnapUp\Console\Command\InstanceList;
use \WSIServices\GSnapUp\Console\Command\InstanceRemove;
use \WSIServices\GSnapUp\Console\Command\InstanceUpdate;

// Verify API is command line interface
if(PHP_SAPI !== 'cli') {
	echo 'Warning: GSnapUp should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI'.PHP_EOL;
	exit();
}

// Load application autoload
require_once __DIR__.'/../vendor/autoload.php';

// Setup application constants
define('APPLICATION_NAME', 'GSnapUp');
define('CONFIGURATION_NAME', strtolower(APPLICATION_NAME).'.json');

// Initialize application
$application = new Application(APPLICATION_NAME, '0.0.1');

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
$application->add(new InstanceList);
$application->add(new InstanceRemove);
$application->add(new InstanceUpdate);

// Start application
$application->run();
