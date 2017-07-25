<?php

namespace Charcoal\Admin\Action;

use RuntimeException;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

// From Pimple
use Pimple\Container;

// From elFinder
use elFinderConnector;
use elFinder;

// From 'charcoal-config'
use Charcoal\Config\GenericConfig as Config;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;

// From 'charcoal-app'
use Charcoal\App\CallableResolverAwareTrait;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;

/**
 * Action: Setup elFinder Connector
 */
class ElfinderConnectorAction extends AdminAction
{
    use CallableResolverAwareTrait;

    /**
     * The base URI for the Charcoal application.
     *
     * @var UriInterface|string
     */
    protected $baseUrl;

    /**
     * The relative path (from filesystem's root) to the storage directory.
     *
     * @var string
     */
    protected $uploadPath = 'uploads/';

    /**
     * Store the elFinder configuration from the admin configuration.
     *
     * @var \Charcoal\Config\ConfigInterface
     */
    protected $elfinderConfig;

    /**
     * The elFinder class settings.
     *
     * @var array
     * - {@link https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options}
     */
    protected $elfinderOptions;

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $propertyFactory;

    /**
     * Store the current property instance for the current class.
     *
     * @var PropertyInterface
     */
    private $formProperty;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->baseUrl = $container['base-url'];
        $this->elfinderConfig = $container['elfinder/config'];
        $this->setPropertyFactory($container['property/factory']);
        $this->setCallableResolver($container['callableResolver']);

        // From filesystem provider
        $this->filesystemConfig = $container['filesystem/config'];
        $this->filesystems = $container['filesystems'];
    }

    /**
     * Set a property factory.
     *
     * @param FactoryInterface $factory The property factory,
     *                                  to createable property values.
     * @return self
     */
    protected function setPropertyFactory(FactoryInterface $factory)
    {
        $this->propertyFactory = $factory;

        return $this;
    }

    /**
     * Retrieve the property factory.
     *
     * @throws RuntimeException If the property factory was not previously set.
     * @return FactoryInterface
     */
    public function propertyFactory()
    {
        if (!isset($this->propertyFactory)) {
            throw new RuntimeException(sprintf(
                'Property Factory is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->propertyFactory;
    }

    /**
     * @todo   Implement {@see self::$httpRequest} to replace `filter_input(INPUT_GET)`.
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $this->setupElfinder();

        return $response;
    }

    /**
     * Setup the elFinder connector.
     *
     * @param  array|null $extraOptions Additional settings to pass to elFinder.
     * @return elFinderConnector
     */
    public function setupElfinder(array $extraOptions = [])
    {
        $options = $this->connectorOptions($extraOptions);

        // Run elFinder
        $connector = new elFinderConnector(new elFinder($options));
        $connector->run();

        return $connector;
    }

    /**
     * Retrieve the default elFinder Connector options.
     *
     * @link    https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
     *     Documentation for connector options.
     * @example https://gist.github.com/mcaskill/5944478b1894a5bf1349bfa699387cd4
     *     The Connector can be customized by defining a "elfinder" structure in
     *     your application's admin configuration.
     *
     * @param  array|null $extraOptions Additional settings to pass to elFinder.
     * @return array
     */
    public function connectorOptions(array $extraOptions = [])
    {
        if ($this->elfinderOptions === null || !empty($extraOptions)) {
            $options = $this->defaultConnectorOptions();

            if (isset($this->elfinderConfig['connector'])) {
                $options = array_replace_recursive(
                    $options,
                    $this->elfinderConfig['connector'],
                    $extraOptions
                );
            } else {
                /** @todo Remove this deprecation notice for the next release */
                $keys = $this->elfinderConfig->keys();
                if (!empty($keys) && array_intersect([ 'bind', 'plugin', 'roots' ], $keys)) {
                    trigger_error(
                        'elFinder connector settings must be nested under [admin.elfinder.connector].',
                        E_USER_DEPRECATED
                    );
                }
            }

            if ($extraOptions) {
                $options = array_replace_recursive(
                    $options,
                    $extraOptions
                );
            }

            if (isset($options['bind'])) {
                $options['bind'] = $this->resolveBoundCallbacks($options['bind']);
            }

            $this->elfinderOptions = $options;
        }

        return $this->elfinderOptions;
    }

    /**
     * Retrieve the default elFinder Connector options from the configured flysystem filesystems.
     *
     * @link    https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
     *     Documentation for connector options.
     * @example https://gist.github.com/mcaskill/5944478b1894a5bf1349bfa699387cd4
     *     The Connector can be customized by defining a "elfinder" structure in
     *     your application's admin configuration.
     *
     * @return array
     */
    public function defaultConnectorOptions()
    {
        $uploadPath = $this->uploadPath();

        $defaultBaseUrl = rtrim((string)$this->baseUrl, '/');

        $filesystemConfig = $this->filesystemConfig;
        $filesystems = $this->filesystems;

        $roots = [];
        $currentFileSystem = $this->formProperty() ? $this->formProperty()->fileSystem() : false;

        if ($currentFileSystem && isset($filesystems[$currentFileSystem])) {
            $config = $filesystemConfig['connections'][$currentFileSystem];

            if (isset($config['label'])) {
                $label = $this->translator()->translation($config['label']);
            } else {
                $label = ucfirst($filesystem);
            }

            if (isset($config['base_url'])) {
                $baseUrl = $config['base_url'];
            } else {
                $baseUrl = $defaultBaseUrl;
            }

            $startPath = $this->formProperty()->uploadPath();
            if (!$startPath) {
                $startPath = isset($config['path']) ? $config['path'] : '/';
            }

            $filesystems[$currentFileSystem]->createDir($startPath);

            $roots[$currentFileSystem] = [
                'driver'         => 'Flysystem',
                'filesystem'     => $filesystems[$currentFileSystem],

                'alias'          => (string)$label,
                'cache'          => false,

                // Path to files (REQUIRED)
                'path'           => $uploadPath,
                'startPath'      => $startPath,

                // Jpg Compression quality
                'jpgQuality'     => 80,
                // Enable localized folder names
                'i18nFolderName' => true,
                // URL to files (REQUIRED)
                'URL'            => $baseUrl.'/'.$uploadPath,
                'tmbURL'         => $defaultBaseUrl.'/'.$uploadPath.'/.tmb',
                'tmbPath'        => $uploadPath.'/.tmb',
                'tmbSize'        => 200,
                'tmbBgColor'     => 'transparent',
                // All MIME types not allowed to upload
                'uploadDeny'     => [ 'all' ],
                // MIME type `image` and `text/plain` allowed to upload
                'uploadAllow'    => $this->defaultUploadAllow(),
                // Allowed MIME type `image` and `text/plain` only
                'uploadOrder'    => [ 'deny', 'allow' ],
                // Disable and hide dot starting files
                'accessControl'  => 'access',
                // File permission attributes
                'attributes'     => [
                    $this->attributesForHiddenFiles()
                ]
            ];

            return [
                'debug' => true,
                'roots' => $roots
            ];
        }

        foreach ($filesystemConfig['connections'] as $filesystem => $config) {
            if (isset($config['public']) && $config['public'] !== true) {
                continue;
            }

            if (isset($config['label'])) {
                $label = $this->translator()->translation($config['label']);
            } else {
                $label = ucfirst($filesystem);
            }

            if (isset($config['base_url'])) {
                $baseUrl = $config['base_url'];
            } else {
                $baseUrl = $defaultBaseUrl;
            }


            $roots[$filesystem] = [
                'driver'         => 'Flysystem',
                'filesystem'     => $filesystems[$filesystem],

                'alias'          => (string)$label,
                'cache'          => false,

                // Path to files (REQUIRED)
                'path'           => $uploadPath,
                'startPath'      => isset($config['path']) ? $config['path'] : '/',

                // Jpg Compression quality
                'jpgQuality'     => 80,
                // Enable localized folder names
                'i18nFolderName' => true,
                // URL to files (REQUIRED)
                'URL'            => $baseUrl.'/'.$uploadPath,
                'tmbURL'         => $defaultBaseUrl.'/'.$uploadPath.'/.tmb',
                'tmbPath'        => $uploadPath.'/.tmb',
                'tmbSize'        => 200,
                'tmbBgColor'     => 'transparent',
                // All MIME types not allowed to upload
                'uploadDeny'     => [ 'all' ],
                // MIME type `image` and `text/plain` allowed to upload
                'uploadAllow'    => $this->defaultUploadAllow(),
                // Allowed MIME type `image` and `text/plain` only
                'uploadOrder'    => [ 'deny', 'allow' ],
                // Disable and hide dot starting files
                'accessControl'  => 'access',
                // File permission attributes
                'attributes' => [
                    $this->attributesForHiddenFiles()
                ]

            ];
        }

        return [
            'debug' => true,
            'roots' => $roots
        ];
    }

    /**
     * Resolve elFinder event listeners.
     *
     * @param  array $toResolve One or many pairs of callbacks.
     * @return array Returns the parsed event listeners.
     */
    protected function resolveBoundCallbacks(array $toResolve)
    {
        $resolved = $toResolve;

        foreach ($toResolve as $actions => $callables) {
            foreach ($callables as $i => $callable) {
                if (!is_callable($callable) && is_string($callable)) {
                    if (0 === strpos($callable, 'Plugin.')) {
                        continue;
                    }

                    $resolved[$actions][$i] = $this->resolveCallable($callable);
                }
            }
        }

        return $resolved;
    }

    /**
     * Trim a file name.
     *
     * @param  string $path     The target path.
     * @param  string $name     The target name.
     * @param  string $src      The temporary file name.
     * @param  object $elfinder The elFinder instance.
     * @param  object $volume   The current volume instance.
     * @return true
     */
    public function sanitizeOnUploadPreSave(&$path, &$name, $src, $elfinder, $volume)
    {
        // To please PHPCS
        unset($path, $src, $elfinder, $volume);

        if (isset($this->elfinderOptions['plugin']['Sanitizer'])) {
            $opts = $this->elfinderOptions['plugin']['Sanitizer'];

            if (isset($opts['enable']) && $opts['enable']) {
                $mask = (is_array($opts['replace']) ? implode($opts['replace']) : $opts['replace']);
                $ext = '.'.pathinfo($name, PATHINFO_EXTENSION);

                // Strip leading and trailing dashes or underscores
                $name = trim(str_replace($ext, '', $name), $mask);

                // Squeeze multiple delimiters and whitespace with a single separator
                $name = preg_replace('!['.preg_quote($mask, '!').'\.\s]{2,}!', $opts['replace'], $name);

                // Reassemble the file name
                $name .= $ext;
            }
        }

        return true;
    }

    /**
     * Retrieve the current object type from the GET parameters.
     *
     * @return string|null
     */
    public function objType()
    {
        return filter_input(INPUT_GET, 'obj_type', FILTER_SANITIZE_STRING);
    }

    /**
     * Retrieve the current object ID from the GET parameters.
     *
     * @return string|null
     */
    public function objId()
    {
        return filter_input(INPUT_GET, 'obj_id', FILTER_SANITIZE_STRING);
    }

    /**
     * Retrieve the current object's property identifier from the GET parameters.
     *
     * @return string|null
     */
    public function propertyIdent()
    {
        return filter_input(INPUT_GET, 'property', FILTER_SANITIZE_STRING);
    }

    /**
     * Retrieve the current property.
     *
     * @return PropertyInterface
     */
    public function formProperty()
    {
        if ($this->formProperty === null) {
            $this->formProperty = false;

            if ($this->objType() && $this->propertyIdent()) {
                $propertyIdent = $this->propertyIdent();

                $model = $this->modelFactory()->create($this->objType());
                $props = $model->metadata()->properties();

                if (isset($props[$propertyIdent])) {
                    $propertyMetadata = $props[$propertyIdent];

                    $property = $this->propertyFactory()->create($propertyMetadata['type']);

                    $property->setIdent($propertyIdent);
                    $property->setData($propertyMetadata);

                    $this->formProperty = $property;
                }
            }
        }

        return $this->formProperty;
    }

    /**
     * Retrieve the default root path.
     *
     * @return string
     */
    public function uploadPath()
    {
        return trim($this->uploadPath, '/');
    }

    /**
     * Default acceptable files to upload.
     *
     * @return array
     */
    protected function defaultUploadAllow()
    {
        // By default, all images, pdf and plaintext files are allowed.
        return [
            'image',
            'application/pdf',
            'text/plain'
        ];
    }

    /**
     * Default attributes for files and directories.
     *
     * @return array
     */
    protected function attributesForHiddenFiles()
    {
        return [
            // Block access to all hidden files and directories (anything starting with ".")
            'pattern' => '!(?:^|/)\..+$!',
            'read'    => false,
            'write'   => false,
            'hidden'  => true,
            'locked'  => false
        ];
    }
}
