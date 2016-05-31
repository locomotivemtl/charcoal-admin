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
    'base_path' => (dirname(__DIR__).'/'),
    'modules'=>[
        'admin'=>[]
    ],
    'admin' => [

    ]
]);

$adminConfig = new AdminConfig();

$logger = new \Psr\Log\NullLogger();
$metadataLoader = new \Charcoal\Model\MetadataLoader([
    'base_path' => '',
    'paths' => [],
    'logger' => $logger,
    'cache' => new \Stash\Pool(),
    'config' => $config
]);

$GLOBALS['container'] = new AppContainer([
    'config' => $config,
    'charcoal/admin/config' => $adminConfig,
    'metadata/loader' => $metadataLoader,
    'model/factory' => new \Charcoal\Factory\GenericFactory([
        'arguments' => [[
            'logger' => $logger,
            'metadata_loader' => $metadataLoader
        ]]
    ]),
    'model/collection/loader' => new \Charcoal\Loader\CollectionLoader([
        'logger' => $logger,
        'factory' => new \Charcoal\Factory\GenericFactory()
    ])
]);
$GLOBALS['container']->register(new \Charcoal\Ui\ServiceProvider\UiServiceProvider());


// Charcoal / Slim is the main app
$GLOBALS['app'] = App::instance($GLOBALS['container']);
$GLOBALS['app']->setLogger(new \Psr\Log\NullLogger());
