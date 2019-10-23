<?php

namespace Charcoal\Admin\ServiceProvider;

// From Pimple
use Charcoal\Admin\AssetsConfig;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Assetic\Asset\AssetReference;
use Charcoal\Attachment\Object\File;
use Charcoal\Factory\GenericResolver;

// from 'kriswallsmith/assetic'
use Assetic\AssetManager;

// From PSR-7
use Psr\Http\Message\UriInterface;

// From Slim
use Slim\Http\Uri;

// From Mustache
use Mustache_LambdaHelper as LambdaHelper;

// From 'charcoal-config'
use Charcoal\Config\ConfigInterface;
use Charcoal\Config\GenericConfig as Config;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-core'
use Charcoal\Model\Service\MetadataConfig;

// From 'charcoal-ui'
use Charcoal\Ui\ServiceProvider\UiServiceProvider;

// From 'charcoal-email'
use Charcoal\Email\ServiceProvider\EmailServiceProvider;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-user'
use Charcoal\User\Authenticator;
use Charcoal\User\Authorizer;

// From 'charcoal-admin'
use Charcoal\Admin\Config as AdminConfig;
use Charcoal\Admin\Property\PropertyInputInterface;
use Charcoal\Admin\Property\PropertyDisplayInterface;
use Charcoal\Admin\Service\SelectizeRenderer;
use Charcoal\Admin\Ui\SecondaryMenu\GenericSecondaryMenuGroup;
use Charcoal\Admin\Ui\SecondaryMenu\SecondaryMenuGroupInterface;
use Charcoal\Admin\User;
use Charcoal\Admin\User\AuthToken;

/**
 * Charcoal Administration Service Provider
 *
 * ## Services
 *
 * - Module
 * - Config
 * - Widget Factory
 */
class AdminServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param  Container $container The Pimple DI container.
     * @return void
     */
    public function register(Container $container)
    {
        // Ensure dependencies are set
        $container->register(new EmailServiceProvider());
        $container->register(new UiServiceProvider());
        $container->register(new AssetsManagerServiceProvider());

        $this->registerAdminServices($container);
        $this->registerFactoryServices($container);
        $this->registerElfinderServices($container);
        $this->registerSelectizeServices($container);
        $this->registerMetadataExtensions($container);
        $this->registerAuthExtensions($container);
        $this->registerViewExtensions($container);
        $this->registerAssetsManager($container);

        // Register Access-Control-List (acl)
        $container->register(new AclServiceProvider());
    }

    /**
     * Registers admin services.
     *
     * @param  Container $container The Pimple DI container.
     * @return void
     */
    protected function registerAdminServices(Container $container)
    {
        /**
         * The admin configset.
         *
         * @param  Container $container The Pimple DI Container.
         * @return AdminConfig
         */
        $container['admin/config'] = function (Container $container) {
            $appConfig = $container['config'];

            $extraConfigs = [];

            foreach ($container['module/classes'] as $module) {
                if (defined(sprintf('%s::ADMIN_CONFIG', $module))) {
                    $moduleAdminConfigs = (array)$module::ADMIN_CONFIG;
                    array_push($extraConfigs, ...$moduleAdminConfigs);
                };
            }

            // The `admin.json` file is not part of regular config
            if (!empty($appConfig['admin_config'])) {
                $appAdminConfigs = (array)$appConfig['admin_config'];
                array_push($extraConfigs, ...$appAdminConfigs);
            }

            if (!empty($extraConfigs)) {
                $basePath = $appConfig['base_path'];
                foreach ($extraConfigs as $path) {
                    $appConfig->addFile($basePath.$path);
                }
            }

            $adminConfig = $appConfig['admin'];
            if (!($adminConfig instanceof AdminConfig)) {
                $adminConfig = new AdminConfig($appConfig['admin']);
            }

            return $adminConfig;
        };

        if (!isset($container['admin/base-url'])) {
            /**
             * Base Admin URL as a PSR-7 UriInterface object for the current request
             * or the Charcoal application.
             *
             * @param  Container $container The Pimple DI Container.
             * @return \Psr\Http\Message\UriInterface
             */
            $container['admin/base-url'] = function (Container $container) {
                $adminConfig = $container['admin/config'];

                if (isset($adminConfig['base_url'])) {
                    $adminUrl = $adminConfig['base_url'];
                } else {
                    $adminUrl = clone $container['base-url'];
                    if ($adminConfig['base_path']) {
                        $basePath  = rtrim($adminUrl->getBasePath(), '/');
                        $adminPath = ltrim($adminConfig['base_path'], '/');
                        $adminUrl  = $adminUrl->withBasePath($basePath.'/'.$adminPath);
                    }
                }

                $adminUrl = Uri::createFromString($adminUrl)->withUserInfo('');

                /** Fix the base path */
                $path = $adminUrl->getPath();
                if ($path) {
                    $adminUrl = $adminUrl->withBasePath($path)->withPath('');
                }

                return $adminUrl;
            };
        }
    }

    /**
     * Registers metadata extensions.
     *
     * @see    \Charcoal\Model\ServiceProvider\ModelServiceProvider
     * @param  Container $container The Pimple DI container.
     * @return void
     */
    protected function registerMetadataExtensions(Container $container)
    {
        if (!isset($container['metadata/config'])) {
            /**
             * @return MetadataConfig
             */
            $container['metadata/config'] = function () {
                $settings   = $container['admin/config']['metadata'];
                $metaConfig = new MetadataConfig($settings);

                return $metaConfig;
            };
        } else {
            /**
             * Alters the application's metadata configset.
             *
             * This extension will merge any Admin-only metadata settings.
             *
             * @param  MetadataConfig $metaConfig The metadata configset.
             * @param  Container      $container  The Pimple DI container.
             * @return MetadataConfig
             */
            $container->extend('metadata/config', function (MetadataConfig $metaConfig, Container $container) {
                $settings = $container['admin/config']['metadata'];
                if (is_array($settings) && !empty($settings)) {
                    $metaConfig->merge($settings);
                }

                return $metaConfig;
            });
        }

        /**
         * Alters the application's metadata configset.
         *
         * This extension will duplicate each previously defined
         * metadata include path to introduce an "admin" subdirectory
         * which adds support for Admin-only metadata settings.
         *
         * For example, if a developer defines the following paths:
         *
         * ```json
         * "paths": [
         *     "my-app/metadata/",
         *     "vendor/locomotivemtl/charcoal-cms/metadata/"
         * ]
         * ```
         *
         * The Admin's service provider will duplicate like so:
         *
         * ```json
         * "paths": [
         *     "my-app/metadata/admin/",
         *     "my-app/metadata/",
         *     "vendor/locomotivemtl/charcoal-cms/metadata/admin/"
         *     "vendor/locomotivemtl/charcoal-cms/metadata/"
         * ]
         * ```
         *
         * Any data included from the "admin" subdirectory will override
         * any "base" data that's been imported.
         *
         * @param  MetadataConfig $metaConfig The metadata configset.
         * @param  Container      $container  The Pimple DI container.
         * @return MetadataConfig
         */
        $container->extend('metadata/config', function (MetadataConfig $metaConfig, Container $container) {
            $adminConfig = $container['admin/config'];
            $adminDir    = '/'.trim($adminConfig['base_path'], '/');

            $metaPaths   = $metaConfig->paths();
            $parsedPaths = [];
            foreach ($metaPaths as $basePath) {
                $adminPath = rtrim($basePath, '/').$adminDir;

                array_push($parsedPaths, $adminPath, $basePath);
            }

            $metaConfig->setPaths($parsedPaths);

            return $metaConfig;
        });
    }

    /**
     * Registers user-authentication extensions.
     *
     * @param  Container $container The Pimple DI container.
     * @return void
     */
    protected function registerAuthExtensions(Container $container)
    {
        /**
         * @param  Container $container The Pimple DI Container.
         * @return Authenticator
         */
        $container['admin/authenticator'] = function (Container $container) {
            return new Authenticator([
                'logger'        => $container['logger'],
                'user_type'     => User::class,
                'user_factory'  => $container['model/factory'],
                'token_type'    => AuthToken::class,
                'token_factory' => $container['model/factory']
            ]);
        };

        /**
         * Replace default Authenticator ('charcoal-ui') with the Admin Authenticator.
         *
         * @todo   Do this right!
         * @param  Container $container The Pimple DI Container.
         * @return Authenticator
         */
        $container['authenticator'] = function (Container $container) {
            return $container['admin/authenticator'];
        };

        /**
         * @param  Container $container The Pimple DI container.
         * @return Authorizer
         */
        $container['admin/authorizer'] = function (Container $container) {
            return new Authorizer([
                'logger'   => $container['logger'],
                'acl'      => $container['admin/acl'],
                'resource' => 'admin'
            ]);
        };

        /**
         * Replace default Authorizer ('charcoal-ui') with the Admin Authorizer.
         *
         * @todo   Do this right!
         * @param  Container $container The Pimple DI Container.
         * @return Authorizer
         */
        $container['authorizer'] = function (Container $container) {
            return $container['admin/authorizer'];
        };
    }

    /**
     * Registers view extensions.
     *
     * @param  Container $container The Pimple DI container.
     * @return void
     */
    protected function registerViewExtensions(Container $container)
    {
        if (!isset($container['view/mustache/helpers'])) {
            $container['view/mustache/helpers'] = function () {
                return [];
            };
        }

        /**
         * Extend helpers for the Mustache Engine
         *
         * @return array
         */
        $container->extend('view/mustache/helpers', function (array $helpers, Container $container) {
            $adminUrl = $container['admin/base-url'];

            $urls = [
                /**
                 * Alias of "siteUrl"
                 *
                 * @return UriInterface|null
                 */
                'adminUrl'     => $adminUrl,
                /**
                 * Prepend the administration-area URI to the given path.
                 *
                 * @see    \Charcoal\App\ServiceProvider\AppServiceProvider::registerViewServices()
                 * @param  string $uri A URI path to wrap.
                 * @return UriInterface|null
                 */
                'withAdminUrl' => function ($uri, LambdaHelper $helper = null) use ($adminUrl) {
                    if ($helper) {
                        $uri = $helper->render($uri);
                    }

                    $uri = strval($uri);
                    if ($uri === '') {
                        $uri = $adminUrl->withPath('');
                    } else {
                        $parts = parse_url($uri);
                        if (!isset($parts['scheme'])) {
                            if (!in_array($uri[0], ['/', '#', '?'])) {
                                $path  = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
                                $query = isset($parts['query']) ? $parts['query'] : '';
                                $hash  = isset($parts['fragment']) ? $parts['fragment'] : '';

                                return $adminUrl->withPath($path)
                                                ->withQuery($query)
                                                ->withFragment($hash);
                            }
                        }
                    }

                    return $uri;
                }
            ];

            return array_merge($helpers, $urls);
        });
    }

    /**
     * Registers services for {@link https://studio-42.github.io/elFinder/ elFinder}.
     *
     * @param  Container $container The Pimple DI Container.
     * @return void
     */
    protected function registerElfinderServices(Container $container)
    {
        /**
         * Configure the "config.admin.elfinder" dataset.
         *
         * @param  AdminConfig $adminConfig The admin configset.
         * @return AdminConfig
         */
        $container->extend('admin/config', function (AdminConfig $adminConfig) {
            $adminConfig['elfinder'] = new Config($adminConfig['elfinder']);

            return $adminConfig;
        });

        /**
         * The elFinder configset.
         *
         * @param  Container $container The Pimple DI Container.
         * @return ConfigInterface
         */
        $container['elfinder/config'] = function (Container $container) {
            return $container['admin/config']['elfinder'];
        };
    }

    /**
     * Registers services for {@link https://selectize.github.io/selectize.js/ Selectize}.
     *
     * @param  Container $container The Pimple DI Container.
     * @return void
     */
    protected function registerSelectizeServices(Container $container)
    {
        /**
         * The Selectize Renderer.
         *
         * @param  Container $container The Pimple DI container.
         * @return SelectizeRenderer
         */
        $container['selectize/renderer'] = function (Container $container) {
            return new SelectizeRenderer([
                'logger'           => $container['logger'],
                'translator'       => $container['translator'],
                'template_factory' => $container['template/factory'],
                'view'             => $container['view']
            ]);
        };
    }

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    protected function registerAssetsManager(Container $container)
    {
        $container['assets/config'] = function (Container $container) {
            $config = $container['admin/config']->get('assets');

            return new AssetsConfig($config);
        };
    }

    /**
     * Registers the admin factories.
     *
     * @param  Container $container The Pimple DI container.
     * @return void
     */
    protected function registerFactoryServices(Container $container)
    {
        /**
         * @param  Container $container The Pimple DI container.
         * @return FactoryInterface
         */
        $container['property/input/factory'] = function (Container $container) {
            return new Factory([
                'base_class'       => PropertyInputInterface::class,
                'arguments'        => [[
                    'container' => $container,
                    'logger'    => $container['logger']
                ]],
                'resolver_options' => [
                    'suffix' => 'Input'
                ]
            ]);
        };

        /**
         * @param  Container $container The Pimple DI container.
         * @return FactoryInterface
         */
        $container['property/display/factory'] = function (Container $container) {
            return new Factory([
                'base_class'       => PropertyDisplayInterface::class,
                'arguments'        => [[
                    'container' => $container,
                    'logger'    => $container['logger']
                ]],
                'resolver_options' => [
                    'suffix' => 'Display'
                ]
            ]);
        };

        /**
         * @param  Container $container A Pimple DI container.
         * @return FactoryInterface
         */
        $container['secondary-menu/group/factory'] = function (Container $container) {
            return new Factory([
                'base_class'       => SecondaryMenuGroupInterface::class,
                'default_class'    => GenericSecondaryMenuGroup::class,
                'arguments'        => [[
                    'container'      => $container,
                    'logger'         => $container['logger'],
                    'view'           => $container['view'],
                    'layout_builder' => $container['layout/builder']
                ]],
                'resolver_options' => [
                    'suffix' => 'SecondaryMenuGroup'
                ]
            ]);
        };
    }
}
