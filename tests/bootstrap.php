<?php

use \Charcoal\App\App;
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppContainer;

use \Charcoal\Admin\Config as AdminConfig;
use \Charcoal\Config\GenericConfig;

session_start();

// Composer autoloader for Charcoal's psr4-compliant Unit Tests
$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('Charcoal\\Admin\\', __DIR__.'/../src/');
$autoloader->add('Charcoal\\Admin\\Tests\\', __DIR__);

$config = new AppConfig([
    'base_path' => (dirname(__DIR__).'/'),
    'databases' => [
        'default' => [
            'database' => 'charcoal_test',
            'username' => 'root',
            'password' => ''
        ]
    ],
    'modules'   => [
        'admin' => []
    ],
    'admin' => []
]);

$appEnv = getenv('APPLICATION_ENV');
if ($appEnv !== 'testing') {
    $configPath = realpath(__DIR__ . '/../../../../config/config.php');

    if (file_exists($configPath)) {
        $localConfig = new GenericConfig($configPath);
        $config['databases'] = $localConfig['databases'];
    }
}

$adminConfig = new AdminConfig();

$logger = new \Psr\Log\NullLogger();
$metadataLoader = new \Charcoal\Model\MetadataLoader([
    'base_path' => '',
    'paths'     => [],
    'logger'    => $logger,
    'cache'     => new \Stash\Pool(),
    'config'    => $config
]);
$modelFactory = new \Charcoal\Factory\GenericFactory([
    'arguments' => [[
        'logger'          => $logger,
        'metadata_loader' => $metadataLoader
    ]]
]);

$GLOBALS['container'] = new AppContainer([
    'config'                => $config,
    'admin/config'          => $adminConfig,
    'charcoal/admin/config' => $adminConfig,
    'metadata/loader'       => $metadataLoader,
    'model/factory'         => $modelFactory,
    'model/collection/loader' => new \Charcoal\Loader\CollectionLoader([
        'logger'  => $logger,
        'factory' => new \Charcoal\Factory\GenericFactory()
    ]),
    'admin/authenticator' => new \Charcoal\User\Authenticator([
        'logger'          => $logger,
        'user_type'       => 'charcoal/admin/user',
        'user_factory'    => $modelFactory,
        'token_type'      => 'charcoal/admin/object/auth-token',
        'token_factory'   => $modelFactory
    ]),
    'admin/authorizer' => function (AppContainer $container) use ($logger) {
        return new \Charcoal\User\Authorizer([
            'logger'    => $logger,
            'acl'       => $container['admin/acl'],
            'resource'  => 'admin'
        ]);
    }
]);
$GLOBALS['container']->register(new \Charcoal\Admin\ServiceProvider\AclServiceProvider());
$GLOBALS['container']->register(new \Charcoal\Ui\ServiceProvider\UiServiceProvider());


// Charcoal / Slim is the main app
$GLOBALS['app'] = App::instance($GLOBALS['container']);
$GLOBALS['app']->setLogger($logger);
