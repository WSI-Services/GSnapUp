#!/usr/bin/env php
<?php

use \Symfony\Component\Console\Application;
use \Symfony\Component\Console\Input\InputOption;

// Load application autoload
require_once '/../vendor/autoload.php';

// Initialize application
$application = new Application('GSnapUp', '0.0.1');

// Configure available commands
// $application->add();

// Start application
$application->run();
