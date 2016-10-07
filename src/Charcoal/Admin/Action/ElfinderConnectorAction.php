<?php

namespace Charcoal\Admin\Action;

use \RuntimeException;

// From PSR-7 (HTTP Messaging)
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From Pimple
use \Pimple\Container;

// From elFinder
use \elFinderConnector;
use \elFinder;

// From 'charcoal-factory'
use \Charcoal\Factory\FactoryInterface;

// From 'charcoal-property'
use \Charcoal\Property\PropertyInterface;

// From 'charcoal-app'
use \Charcoal\App\CallableResolverAwareTrait;

// Intra-module ('charcoal-admin') dependencies
use \Charcoal\Admin\AdminAction;

/**
 * elFinder Connector
 */
class ElfinderConnectorAction extends AdminAction
{
    use CallableResolverAwareTrait;

    /**
     * The base URI for the Charcoal application.
     *
     * @var string|\Psr\Http\Message\UriInterface
     */
    public $baseUrl;

    /**
     * Store the elFinder configuration.
     *
     * @var array
     */
    protected $elfinderConfig;

    /**
     * Store a reference to the admin configuration.
     *
     * @var \Charcoal\Admin\AdminConfig
     */
    private $adminConfig;

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
        $this->adminConfig = $container['admin/config'];
        $this->setPropertyFactory($container['property/factory']);
        $this->setCallableResolver($container['callableResolver']);
    }

    /**
     * Set a property factory.
     *
     * @param FactoryInterface $factory The property factory,
     *     to createable property values.
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
            throw new RuntimeException(
                sprintf('Property Factory is not defined for "%s"', get_class($this))
            );
        }

        return $this->propertyFactory;
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $this->elfinderConfig = $this->defaultConnectorOptions();

        if (isset($this->adminConfig['elfinder'])) {
            $this->elfinderConfig = array_replace_recursive(
                $this->elfinderConfig,
                $this->adminConfig['elfinder']
            );
        }

        if (isset($this->elfinderConfig['bind'])) {
            $this->elfinderConfig['bind'] = $this->resolveBoundCallbacks($this->elfinderConfig['bind']);
        }

        // Run elFinder
        $connector = new elFinderConnector(new elFinder($this->elfinderConfig));
        $connector->run();

        return $response;
    }

    /**
     * Retrieve the default elFinder Connector options.
     *
     * @link https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
     *     Documentation for connector options.
     * @example https://gist.github.com/mcaskill/5944478b1894a5bf1349bfa699387cd4
     *     The Connector can be customized by defining a "elfinder" structure in
     *     your application's admin configuration.
     *
     * @return array
     */
    public function defaultConnectorOptions()
    {
        $startPath    = 'uploads/';
        $formProperty = $this->formProperty();

        if (isset($formProperty['upload_path'])) {
            $startPath = $formProperty['upload_path'];

            if (!file_exists($startPath)) {
                mkdir($startPath, 0777, true);
            }
        }

        $baseUrl = rtrim((string)$this->baseUrl, '/');

        return [
            'debug' => false,
            'roots' => [
                'default' => [
                    // Driver for accessing file system (REQUIRED)
                    'driver'         => 'LocalFileSystem',
                    // Path to files (REQUIRED)
                    'path'           => 'uploads/',
                    'startPath'      => $startPath,
                    // Enable localized folder names
                    'i18nFolderName' => true,
                    // URL to files (REQUIRED)
                    'URL'            => $baseUrl.'/uploads',
                    'tmbURL'         => $baseUrl.'/uploads/.tmb',
                    'tmbPath'        => 'uploads/.tmb',
                    'tmbSize'        => 200,
                    'tmbBgColor'     => 'transparent',
                    // All MIME types not allowed to upload
                    'uploadDeny'     => [ 'all' ],
                    // MIME type `image` and `text/plain` allowed to upload
                    'uploadAllow'    => $this->defaultUploadAllow(),
                    // Allowed MIME type `image` and `text/plain` only
                    'uploadOrder'    => [ 'deny', 'allow' ],
                    // Disable and hide dot starting files (OPTIONAL)
                    'accessControl'  => 'access',
                    // File permission attributes
                    'attributes'     => [
                        $this->attributesForHiddenFiles()
                    ]
                ]
            ]
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
        if (isset($this->elfinderConfig['plugin']['Sanitizer'])) {
            $opts = $this->elfinderConfig['plugin']['Sanitizer'];

            if (isset($opts['enable']) && $opts['enable']) {
                $mask  = (is_array($opts['replace']) ? implode($opts['replace']) : $opts['replace']);
                $ext   = '.'.pathinfo($name, PATHINFO_EXTENSION);

                // Strip leading and trailing dashes or underscores
                $name  = trim(str_replace($ext, '', $name), $mask);

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
     * @return array
     */
    private function defaultUploadAllow()
    {
        // By default, all images, pdf and plaintext files are allowed.
        return [
            'image',
            'application/pdf',
            'text/plain'
        ];
    }

    /**
     * @return array
     */
    private function attributesForHiddenFiles()
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
