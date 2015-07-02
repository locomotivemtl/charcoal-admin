<?php

use \Slim\Environment as SlimEnvironment;

use \Charcoal\Admin\AdminModule as AdminModule;
use \Charcoal\Charcoal as Charcoal;

session_start();

// Composer autoloader for Charcoal's psr4-compliant Unit Tests
$autoloader = require __DIR__ . '/../vendor/autoload.php';
$autoloader->add('Charcoal\\Admin\\', __DIR__.'/../src/');
$autoloader->add('Charcoal\\Admin\\Tests\\', __DIR__);

// This var needs to be set automatically, for now
Charcoal::init();

// Set up the environment so that Slim can route
Charcoal::app()->environment = SlimEnvironment::mock([
    'REQUEST_METHOD'=>'get'
]);


$admin_module = new AdminModule();
$admin_module->setup_routes();

