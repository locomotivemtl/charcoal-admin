<?php

namespace Charcoal\Tests\Admin;

use PDO;

// From Mockery
use Mockery;

// From PSR-3
use Psr\Log\NullLogger;

// From Slim
use Slim\Http\Uri;

// From 'tedivm/stash' (PSR-6)
use Stash\Pool;

// From 'zendframework/zend-permissions-acl'
use Zend\Permissions\Acl\Acl;

// From Pimple
use Pimple\Container;

// From 'league/climate'
use League\CLImate\CLImate;
use League\CLImate\Util\System\Linux;
use League\CLImate\Util\Output;
use League\CLImate\Util\Reader\Stdin;
use League\CLImate\Util\UtilFactory;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-app'
use Charcoal\App\AppConfig;
use Charcoal\App\Template\WidgetBuilder;

// From 'charcoal-core'
use Charcoal\Source\DatabaseSource;
use Charcoal\Model\ServiceProvider\ModelServiceProvider;

// From 'charcoal-user'
use Charcoal\User\Authenticator;
use Charcoal\User\Authorizer;

// From 'charcoal-ui'
use Charcoal\Ui\Dashboard\DashboardBuilder;
use Charcoal\Ui\Dashboard\DashboardInterface;
use Charcoal\Ui\Layout\LayoutBuilder;
use Charcoal\Ui\Layout\LayoutFactory;

// From 'charcoal-email'
use Charcoal\Email\Email;
use Charcoal\Email\EmailConfig;

// From 'charcoal-view'
use Charcoal\View\ViewServiceProvider;

// From 'charcoal-translator'
use Charcoal\Translator\ServiceProvider\TranslatorServiceProvider;

// From 'charcoal-admin'
use Charcoal\Admin\Config as AdminConfig;
use Charcoal\Admin\User as AdminUser;
use Charcoal\Tests\Admin\Mock\AuthToken as AdminAuthToken;

/**
 *
 */
class ContainerProvider
{
    /**
     * Register the unit tests required services.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerDebug(Container $container)
    {
        if (!isset($container['debug'])) {
            $container['debug'] = false;
        }
    }

    /**
     * Register the unit tests required services.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerBaseServices(Container $container)
    {
        $this->registerDebug($container);
        $this->registerConfig($container);
        $this->registerDatabase($container);
        $this->registerLogger($container);
        $this->registerCache($container);
    }

    /**
     * Register the admin services.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerAdminServices(Container $container)
    {
        $this->registerBaseServices($container);
        $this->registerBaseUrl($container);
        $this->registerAdminConfig($container);
    }

    /**
     * Setup the application's base URI.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerBaseUrl(Container $container)
    {
        $container['base-url'] = function () {
            return Uri::createFromString('');
        };

        $container['admin/base-url'] = function () {
            return Uri::createFromString('admin');
        };
    }

    /**
     * Setup the application configset.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerConfig(Container $container)
    {
        $container['config'] = function () {
            return new AppConfig([
                'base_path'  => realpath(__DIR__.'/../../..'),
                'apis'       => [
                    'google' => [
                        'recaptcha' => [
                            'public_key'  => 'foobar',
                            'private_key' => 'bazqux',
                        ],
                    ],
                ],
                'locales'    => [
                    'en' => [
                        'locale' => 'en-US',
                    ],
                ],
                'translator' => [
                    'paths' => [],
                ],
                'metadata'   => [
                    'paths'  => [
                        'metadata',
                        'vendor/locomotivemtl/charcoal-object/metadata',
                        'vendor/locomotivemtl/charcoal-user/metadata',
                    ],
                ],
            ]);
        };

        /**
         * List of Charcoal module classes.
         *
         * Explicitly defined in case of a version mismatch with dependencies. This parameter
         * is normally defined by {@see \Charcoal\App\ServiceProvider\AppServiceProvider}.
         *
         * @var array
         */
        $container['module/classes'] = [];
    }

    /**
     * Setup the admin module configset.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerAdminConfig(Container $container)
    {
        $this->registerConfig($container);

        $container['admin/config'] = function () {
            return new AdminConfig();
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerElfinderConfig(Container $container)
    {
        $container['elfinder/config'] = function () {
            return [];
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerLayoutFactory(Container $container)
    {
        $container['layout/factory'] = function () {
            $layoutFactory = new LayoutFactory();
            return $layoutFactory;
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerLayoutBuilder(Container $container)
    {
        $this->registerLayoutFactory($container);

        $container['layout/builder'] = function (Container $container) {
            $layoutFactory = $container['layout/factory'];
            $layoutBuilder = new LayoutBuilder($layoutFactory, $container);
            return $layoutBuilder;
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerDashboardFactory(Container $container)
    {
        $this->registerLogger($container);
        $this->registerWidgetBuilder($container);
        $this->registerLayoutBuilder($container);

        $container['dashboard/factory'] = function (Container $container) {
            return new Factory([
                'arguments'          => [[
                    'container'      => $container,
                    'logger'         => $container['logger'],
                    'widget_builder' => $container['widget/builder'],
                    'layout_builder' => $container['layout/builder']
                ]],
                'resolver_options' => [
                    'suffix' => 'Dashboard'
                ]
            ]);
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerDashboardBuilder(Container $container)
    {
        $this->registerDashboardFactory($container);

        $container['dashboard/builder'] = function (Container $container) {
            $dashboardFactory = $container['dashboard/factory'];
            $dashboardBuilder = new DashboardBuilder($dashboardFactory, $container);
            return $dashboardBuilder;
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
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
                    'logger'    => $container['logger']
                ]]
            ]);
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerWidgetBuilder(Container $container)
    {
        $this->registerWidgetFactory($container);

        $container['widget/builder'] = function (Container $container) {
            return new WidgetBuilder($container['widget/factory'], $container);
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerClimate(Container $container)
    {
        $container['climate/system'] = function () {
            $system = Mockery::mock(Linux::class);
            $system->shouldReceive('hasAnsiSupport')->andReturn(true);
            $system->shouldReceive('width')->andReturn(80);

            return $system;
        };

        $container['climate/output'] = function () {
            $output = Mockery::mock(Output::class);
            $output->shouldReceive('persist')->andReturn($output);
            $output->shouldReceive('sameLine')->andReturn($output);
            $output->shouldReceive('write');

            return $output;
        };

        $container['climate/reader'] = function () {
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

    /**
     * Setup the application's logging interface.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerLogger(Container $container)
    {
        $container['logger'] = function () {
            return new NullLogger();
        };
    }

    /**
     * Setup the application's caching interface.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerCache(Container $container)
    {
        $container['cache'] = function () {
            return new Pool();
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerDatabase(Container $container)
    {
        $container['database'] = function () {
            $pdo = new PDO('sqlite::memory:');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerModelServiceProvider(Container $container)
    {
        static $provider = null;

        if ($provider === null) {
            $provider = new ModelServiceProvider();
        }

        $provider->register($container);
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerTranslatorServiceProvider(Container $container)
    {
        static $provider = null;

        if ($provider === null) {
            $provider = new TranslatorServiceProvider();
        }

        $provider->register($container);
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerViewServiceProvider(Container $container)
    {
        static $provider = null;

        if ($provider === null) {
            $provider = new ViewServiceProvider();
        }

        $provider->register($container);
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerAcl(Container $container)
    {
        $container['admin/acl'] = function () {
            return new Acl();
        };

        $container['authorizer/acl'] = function () {
            return $container['admin/acl'];
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerAuthenticator(Container $container)
    {
        $this->registerLogger($container);
        $this->registerModelServiceProvider($container);

        $container['admin/authenticator'] = function (Container $container) {
            return new Authenticator([
                'logger'        => $container['logger'],
                'user_type'     => AdminUser::class,
                'user_factory'  => $container['model/factory'],
                'token_type'    => AdminAuthToken::class,
                'token_factory' => $container['model/factory'],
            ]);
        };

        $container['authenticator'] = function (Container $container) {
            return $container['admin/authenticator'];
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerAuthorizer(Container $container)
    {
        $this->registerLogger($container);
        $this->registerAcl($container);

        $container['admin/authorizer'] = function (Container $container) {
            return new Authorizer([
                'logger'    => $container['logger'],
                'acl'       => $container['admin/acl'],
                'resource'  => 'admin',
            ]);
        };

        $container['authorizer'] = function (Container $container) {
            return $container['admin/authorizer'];
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerPropertyDisplayFactory(Container $container)
    {
        $this->registerDatabase($container);
        $this->registerLogger($container);

        $container['property/display/factory'] = function (Container $container) {
            return new Factory([
                'resolver_options' => [
                    'suffix' => 'Display'
                ],
                'arguments' => [[
                    'container' => $container,
                    'logger'    => $container['logger']
                ]]
            ]);
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerEmailFactory(Container $container)
    {
        $container['email/factory'] = function () {
            return new Factory([
                'map' => [
                    'email' => Email::class,
                ],
            ]);
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerActionDependencies(Container $container)
    {
        $this->registerDebug($container);
        $this->registerLogger($container);
        $this->registerDatabase($container);
        $this->registerCache($container);

        $this->registerAdminConfig($container);
        $this->registerBaseUrl($container);

        $this->registerAuthenticator($container);
        $this->registerAuthorizer($container);

        $this->registerViewServiceProvider($container);
        $this->registerModelServiceProvider($container);
        $this->registerTranslatorServiceProvider($container);
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerTemplateDependencies(Container $container)
    {
        $this->registerDebug($container);
        $this->registerLogger($container);
        $this->registerDatabase($container);
        $this->registerCache($container);

        $this->registerAdminConfig($container);
        $this->registerBaseUrl($container);

        $this->registerAuthenticator($container);
        $this->registerAuthorizer($container);

        $this->registerViewServiceProvider($container);
        $this->registerModelServiceProvider($container);
        $this->registerTranslatorServiceProvider($container);

        $container['menu/builder'] = null;
        $container['menu/item/builder'] = null;
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerWidgetDependencies(Container $container)
    {
        $this->registerDebug($container);
        $this->registerLogger($container);
        $this->registerDatabase($container);
        $this->registerCache($container);

        $this->registerAdminConfig($container);
        $this->registerBaseUrl($container);

        $this->registerAuthenticator($container);
        $this->registerAuthorizer($container);

        $this->registerViewServiceProvider($container);
        $this->registerModelServiceProvider($container);
        $this->registerTranslatorServiceProvider($container);
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerInputDependencies(Container $container)
    {
        $this->registerDebug($container);
        $this->registerLogger($container);
        $this->registerDatabase($container);
        $this->registerCache($container);

        $this->registerAdminConfig($container);
        $this->registerBaseUrl($container);

        $this->registerAuthenticator($container);
        $this->registerAuthorizer($container);

        $this->registerViewServiceProvider($container);
        $this->registerModelServiceProvider($container);
        $this->registerTranslatorServiceProvider($container);
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerScriptDependencies(Container $container)
    {
        $this->registerDebug($container);
        $this->registerLogger($container);
        $this->registerDatabase($container);
        $this->registerCache($container);

        $this->registerAdminConfig($container);
        $this->registerBaseUrl($container);

        $this->registerAuthenticator($container);
        $this->registerAuthorizer($container);

        $this->registerViewServiceProvider($container);
        $this->registerModelServiceProvider($container);
        $this->registerTranslatorServiceProvider($container);

        $this->registerClimate($container);
    }
}
