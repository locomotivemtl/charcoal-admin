<?php

use \Charcoal\App\App;
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppContainer;

use \Charcoal\Admin\Config as AdminConfig;

session_start();

// Composer autoloader for Charcoal's psr4-compliant Unit Tests
$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('Charcoal\\Admin\\', __DIR__.'/../src/');
$autoloader->add('Charcoal\\Admin\\Tests\\', __DIR__);

$config = new AppConfig([
    'base_path' => (dirname(__DIR__) . '/'),
    'modules'=>[
        'admin'=>[]
    ],
    'admin' => [

    ]
]);

$adminConfig = new AdminConfig();

$GLOBALS['container'] = new AppContainer([
    'config' => $config,
    'charcoal/admin/config' => $adminConfig
]);


// Charcoal / Slim is the main app
$GLOBALS['app'] = App::instance($GLOBALS['container']);
$GLOBALS['app']->setLogger(new \Psr\Log\NullLogger());

