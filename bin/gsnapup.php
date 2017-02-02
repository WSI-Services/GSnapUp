#!/usr/bin/env php
<?php

use \Symfony\Component\Console\Application;
use \Symfony\Component\Console\Input\InputOption;
use \WSIServices\GSnapUp\Console\Command\Init;

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

// Start application
$application->run();
