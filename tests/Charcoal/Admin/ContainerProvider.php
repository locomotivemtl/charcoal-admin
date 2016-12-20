<?php

namespace Charcoal\Admin\Tests;

use \PDO;

use \Zend\Permissions\Acl\Acl;

use \Pimple\Container;

use \Mockery;

use \Psr\Log\NullLogger;
use \Cache\Adapter\Void\VoidCachePool;

use \Charcoal\Factory\GenericFactory as Factory;

use \Charcoal\App\AppConfig;
use \Charcoal\App\Template\WidgetBuilder;

use \Charcoal\Model\Service\MetadataLoader;
use \Charcoal\Source\DatabaseSource;

// Module `charcoal-base` dependencies
use \Charcoal\User\Authenticator;
use \Charcoal\User\Authorizer;

use \Charcoal\Admin\Config as AdminConfig;

use \Charcoal\Ui\Dashboard\DashboardBuilder;
use \Charcoal\Ui\Dashboard\DashboardInterface;
use \Charcoal\Ui\Layout\LayoutBuilder;
use \Charcoal\Ui\Layout\LayoutFactory;

use \Charcoal\Email\Email;
use \Charcoal\Email\EmailConfig;

use \League\CLImate\CLImate;
use \League\CLImate\Util\System\Linux;
use \League\CLImate\Util\Output;
use \League\CLImate\Util\Reader\Stdin;
use \League\CLImate\Util\UtilFactory;

/**
 *
 */
class ContainerProvider
{

    public function registerConfig(Container $container)
    {
        $container['config'] = function (Container $container) {
            return new AppConfig();
        };
    }
    public function registerBaseUrl(Container $container)
    {
        $container['base-url'] = function (Container $container) {
            return '';
        };
    }

    public function registerLayoutFactory(Container $container)
    {
        /**
         * @param Container $container A Pimple DI container.
         * @return LayoutFactory
         */
        $container['layout/factory'] = function (Container $container) {

            $layoutFactory = new LayoutFactory();
            return $layoutFactory;
        };
    }

    public function registerLayoutBuilder(Container $container)
    {
        $this->registerLayoutFactory($container);
        /**
         * @param Container $container A Pimple DI container.
         * @return LayoutBuilder
         */
        $container['layout/builder'] = function (Container $container) {
            $layoutFactory = $container['layout/factory'];
            $layoutBuilder = new LayoutBuilder($layoutFactory, $container);
            return $layoutBuilder;
        };
    }

    public function registerDashboardFactory(Container $container)
    {
        $this->registerLogger($container);
        $this->registerWidgetBuilder($container);
        $this->registerLayoutBuilder($container);
        /**
         * @param Container $container A Pimple DI container.
         * @return LayoutFactory
         */
        $container['dashboard/factory'] = function (Container $container) {
            return new Factory([
                'arguments'          => [[
                    'container'      => $container,
                    'logger'         => $container['logger'],
                    'widget_builder' => $container['widget/builder'],
                    'layout_builder' => $container['layout/builder']
                ]],
                'resolver_options'   => [
                    'suffix' => 'Dashboard'
                ]
            ]);
        };
    }

    public function registerDashboardBuilder(Container $container)
    {
        $this->registerDashboardFactory($container);

        /**
         * @param Container $container A Pimple DI container.
         * @return LayoutBuilder
         */
        $container['dashboard/builder'] = function (Container $container) {
            $dashboardFactory = $container['dashboard/factory'];
            $dashboardBuilder = new DashboardBuilder($dashboardFactory, $container);
            return $dashboardBuilder;
        };
    }

    public function registerWidgetFactory(Container $container)
    {
        $this->registerLogger($container);
        $container['widget/factory'] = function (Container $container) {
            return new Factory([
                'resolver_options' => [
                    'suffix' => 'Widget'
                ],
                'arguments' => [[
                    'container' => $container,
                    'logger' => $container['logger']
                ]]
            ]);
        };
    }

    public function registerWidgetBuilder(Container $container)
    {
        $this->registerWidgetFactory($container);
        $container['widget/builder'] = function (Container $container) {
            return new WidgetBuilder($container['widget/factory'], $container);
        };
    }
    public function registerClimate(Container $container)
    {
        $container['climate/system'] = function (Container $container) {
            $system = Mockery::mock(Linux::class);
            $system->shouldReceive('hasAnsiSupport')->andReturn(true);
            $system->shouldReceive('width')->andReturn(80);

            return $system;
        };
        $container['climate/output'] = function (Container $container) {
            $output = Mockery::mock(Output::class);
            $output->shouldReceive('persist')->andReturn($output);
            $output->shouldReceive('sameLine')->andReturn($output);
            $output->shouldReceive('write');

            return $output;
        };
        $container['climate/reader'] = function (Container $container) {
            $reader = Mockery::mock(Stdin::class);
            $reader->shouldReceive('line')->andReturn('line');
            $reader->shouldReceive('char')->andReturn('char');
            $reader->shouldReceive('multiLine')->andReturn('multiLine');
            return $reader;
        };
        $container['climate/util'] = function (Container $container) {
            return new UtilFactory($container['climate/system']);
        };
        $container['climate'] = function (Container $container) {

            $climate = new CLImate();

            $climate->setOutput($container['climate/output']);
            $climate->setUtil($container['climate/util']);
            $climate->setReader($container['climate/reader']);

            return $climate;
        };
    }

    public function registerAdminConfig(Container $container)
    {
        $this->registerConfig($container);
        $container['admin/config'] = function (Container $container) {
            return new AdminConfig();
        };
    }

    public function registerLogger(Container $container)
    {
        $container['logger'] = function (Container $container) {
            return new NullLogger();
        };
    }

    public function registerCache(Container $container)
    {
        $container['cache'] = function (Container $container) {
            return new VoidCachePool;
        };
    }

    public function registerDatabase(Container $container)
    {
        $container['database'] = function (Container $container) {
            $pdo = new PDO('sqlite::memory:');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        };
    }

    public function registerAcl(Container $container)
    {
        $container['admin/acl'] = function (Container $container) {
            return new Acl();
        };
    }

    public function registerMetadataLoader(Container $container)
    {
        $this->registerLogger($container);
        $this->registerCache($container);
        $container['metadata/loader'] = function (Container $container) {
            return new MetadataLoader([
                'logger'=>$container['logger'],
                'cache' => $container['cache'],
                'base_path' => __DIR__.'/../../..',
                'paths'=>[
                    'metadata',
                    'vendor/locomotivemtl/charcoal-base/metadata'
                ]
            ]);
        };
    }

    public function registerSourceFactory(Container $container)
    {
        $this->registerLogger($container);
        $this->registerDatabase($container);
        $container['source/factory'] = function (Container $container) {
            return new Factory([
                'map'=>[
                    'database'=>DatabaseSource::class
                ],
                'arguments' => [[
                    'logger' => $container['logger'],
                    'pdo'    => $container['database']
                ]]
            ]);
        };
    }

    public function registerPropertyFactory(Container $container)
    {
        $this->registerDatabase($container);
        $this->registerLogger($container);
        $container['property/factory'] = function (Container $container) {
            return new Factory([
                'resolver_options'=>[
                    'prefix' => '\Charcoal\Property\\',
                    'suffix' => 'Property'
                ],
                'arguments' => [[
                    'container' => $container,
                    'database'  => $container['database'],
                    'logger'    => $container['logger']
                ]]
            ]);
        };
    }

    public function registerModelFactory(Container $container)
    {
        $this->registerLogger($container);
        $this->registerMetadataLoader($container);
        $this->registerPropertyFactory($container);
        $this->registerSourceFactory($container);
        $container['model/factory'] = function (Container $container) {
            return new Factory([
                'arguments' => [[
                    'container' => $container,
                    'logger'=>$container['logger'],
                    'metadata_loader' => $container['metadata/loader'],
                    'property_factory' => $container['property/factory'],
                    'source_factory'=> $container['source/factory']
                ]]
            ]);
        };
    }

    public function registerAuthenticator(Container $container)
    {
        $this->registerLogger($container);
        $this->registerModelFactory($container);
        $container['admin/authenticator'] = function (Container $container) {
            return new Authenticator([
                'logger'        => $container['logger'],
                'user_type'     => 'charcoal/admin/user',
                'user_factory'  => $container['model/factory'],
                'token_type'    => 'charcoal/admin/user/auth-token',
                'token_factory' => $container['model/factory']
            ]);
        };
    }

    public function registerAuthorizer(Container $container)
    {
        $this->registerLogger($container);
        $this->registerAcl($container);
        $container['admin/authorizer'] = function (Container $container) {
            return new Authorizer([
                'logger'    => $container['logger'],
                'acl'       => $container['admin/acl'],
                'resource'  => 'admin'
            ]);
        };
    }

    public function registerCollectionLoader(Container $container)
    {
        $this->registerLogger($container);
        $this->registerModelFactory($container);
        $container['model/collection/loader'] = function (Container $container) {
            return new \Charcoal\Loader\CollectionLoader([
                'logger'  => $container['logger'],
                'factory' => $container['model/factory']
            ]);
        };
    }

    public function registerEmailFactory(Container $container)
    {
        $container['email/factory'] = function (Container $container) {
            return new Factory([
                'map' => [
                    'email' => Email::class
                ]
            ]);
        };
    }
}
