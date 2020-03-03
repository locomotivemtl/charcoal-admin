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
use elFinder;
use elFinderConnector;
use elFinderVolumeDriver;

// From 'charcoal-config'
use Charcoal\Config\ConfigInterface;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;

// From 'charcoal-app'
use Charcoal\App\CallableResolverAwareTrait;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;
use Charcoal\Admin\Template\ElfinderTemplate;

/**
 * Action: Setup elFinder Connector
 */
class ElfinderConnectorAction extends AdminAction
{
    use CallableResolverAwareTrait;

    /**
     * The default relative path (from filesystem's root) to the storage directory.
     *
     * @const string
     */
    const DEFAULT_STORAGE_PATH = 'uploads';

    /**
     * The base path for the Charcoal installation.
     *
     * @var string|null
     */
    protected $basePath;

    /**
     * The path to the public / web directory.
     *
     * @var string|null
     */
    protected $publicPath;

    /**
     * Store the collection of filesystem adapters.
     *
     * @var \League\Flysystem\FilesystemInterface[]
     */
    protected $filesystems;

    /**
     * Store the filesystem configset.
     *
     * @var \Charcoal\App\Config\FilesystemConfig
     */
    protected $filesystemConfig;

    /**
     * Store the elFinder configuration from the admin / app configset.
     *
     * @var ConfigInterface
     */
    protected $elfinderConfig;

    /**
     * Store the compiled elFinder configuration settings.
     *
     * @var array
     * - {@link https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options}
     */
    protected $elfinderOptions;

    /**
     * Store the elFinder connector instance.
     *
     * @var \elFinderConnector
     */
    protected $elfinderConnector;

    /**
     * Store the current property instance for the current class.
     *
     * @var PropertyInterface
     */
    protected $formProperty;

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $propertyFactory;

    /**
     * The related object type.
     *
     * @var string
     */
    private $objType;

    /**
     * The related object ID.
     *
     * @var string
     */
    private $objId;

    /**
     * The related property identifier.
     *
     * @var string
     */
    private $propertyIdent;

    /**
     * Sets the action data from a PSR Request object.
     *
     * @param  RequestInterface $request A PSR-7 compatible Request instance.
     * @return self
     */
    protected function setDataFromRequest(RequestInterface $request)
    {
        $keys = $this->validDataFromRequest();
        $data = $request->getParams($keys);

        if (isset($data['obj_type'])) {
            $this->objType = $data['obj_type'];
        }

        if (isset($data['obj_id'])) {
            $this->objId = $data['obj_id'];
        }

        if (isset($data['property'])) {
            $this->propertyIdent = $data['property'];
        }

        return $this;
    }

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    protected function validDataFromRequest()
    {
        return [
            'obj_type',
            'obj_id',
            'property',
        ];
    }

    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $this->connector = $this->setupElfinder();

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
        if (!defined('ELFINDER_IMG_PARENT_URL')) {
            // Ensure images injected by elFinder are relative to its assets directory
            define('ELFINDER_IMG_PARENT_URL', (string)$this->baseUrl(ElfinderTemplate::ELFINDER_ASSETS_REL_PATH));
        }

        $options = $this->buildConnectorOptions($extraOptions);

        // Run elFinder
        $connector = new elFinderConnector(new elFinder($options));
        $connector->run();

        return $connector;
    }

    /**
     * Retrieve the elFinder Connector options.
     *
     * @return array
     */
    public function getConnectorOptions()
    {
        if ($this->elfinderOptions === null) {
            $this->elfinderOptions = $this->buildConnectorOptions();
        }

        return $this->elfinderOptions;
    }

    /**
     * Build and retrieve the elFinder Connector options.
     *
     * @link    https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
     *     Documentation for connector options.
     * @example https://gist.github.com/mcaskill/5944478b1894a5bf1349bfa699387cd4
     *     The Connector can be customized by defining a "elfinder" structure in
     *     your application's admin configuration.
     *
     * @param  array $extraOptions Additional settings to pass to elFinder.
     * @return array
     */
    public function buildConnectorOptions(array $extraOptions = [])
    {
        $options = [
            'debug' => false,
            'roots' => $this->getCurrentRoots(),
        ];

        $adminOptions = $this->getAdminConnectorOptions();
        $adminOptions = $this->parseAdminOptionsForConnectorBuild($adminOptions);
        $extraOptions = $this->parseExtraOptionsForConnectorBuild($extraOptions);

        $options = $this->mergeConnectorOptions($options, $adminOptions);
        $options = $this->mergeConnectorOptions($options, $extraOptions);

        if (isset($options['bind'])) {
            $options['bind'] = $this->resolveCallbacksForBindOption($options['bind']);
        }

        $this->elfinderOptions = $options;

        return $options;
    }

    /**
     * Merge the elFinder Connector options.
     *
     * @param  array $options1 The settings in which data is replaced.
     * @param  array $options2 The settings from which data is extracted.
     * @return array The merged settings.
     */
    protected function mergeConnectorOptions(array $options1, array $options2)
    {
        return array_replace_recursive($options1, $options2);
    }

    /**
     * Parse the admin options for the elFinder Connector.
     *
     * @param  array $options The admin settings to parse.
     * @return array The parsed settings.
     */
    protected function parseAdminOptionsForConnectorBuild(array $options)
    {
        // Root settings are already merged when retrieving available roots.
        unset($options['roots']);

        return $options;
    }

    /**
     * Parse the extra options for the elFinder Connector.
     *
     * @param  array $options The extra settings to parse.
     * @return array The parsed settings.
     */
    protected function parseExtraOptionsForConnectorBuild(array $options)
    {
        // Resolve callbacks on extra options
        if (isset($options['roots'])) {
            $options['roots'] = $this->resolveCallbacksForRoots($options['roots']);
        }

        return $options;
    }

    /**
     * Retrieve the admin's elFinder Connector options.
     *
     * Path: `config.admin.elfinder.connector`
     *
     * @return array
     */
    public function getAdminConnectorOptions()
    {
        $config = $this->elfinderConfig('connector');
        if (!is_array($config)) {
            return [];
        }

        return $config;
    }

    /**
     * Retrieve the default elFinder Connector options.
     *
     * @return array
     */
    protected function getDefaultElfinderRootSettings()
    {
        return [
            'driver'          => 'LocalFileSystem',
            'i18nFolderName'  => true,

            'jpgQuality'      => 80,
            'tmbSize'         => 200,
            'tmbCrop'         => true,
            'tmbBgColor'      => 'transparent',

            'uploadDeny'      => $this->defaultUploadDeny(),
            'uploadAllow'     => $this->defaultUploadAllow(),
            'uploadOrder'     => [ 'deny', 'allow' ],
            'accessControl'   => [ $this, 'checkAccess' ],
            'duplicateSuffix' => '_%s_',
        ];
    }

    /**
     * Retrieve the default Flysystem / elFinder options.
     *
     * @return array
     */
    protected function getDefaultFlysystemRootSettings()
    {
        return [
            'driver'       => 'Flysystem',
            'rootCssClass' => 'elfinder-navbar-root-local',
            'filesystem'   => null,
            'cache'        => false,
            'URL'          => (string)$this->baseUrl(self::DEFAULT_STORAGE_PATH),
            'path'         => self::DEFAULT_STORAGE_PATH,
        ];
    }

    /**
     * Retrieve the default Flysystem / elFinder options.
     *
     * @param  string $ident The disk identifier.
     * @return array
     */
    protected function resolveFallbackRootSettings($ident)
    {
        $fsConfig   = $this->getFilesystemConfig($ident);
        $uploadPath = $this->defaultUploadPath();

        if (isset($fsConfig['base_url'])) {
            $baseUrl = rtrim($fsConfig['base_url'], '/').'/';
        } else {
            $baseUrl = $this->baseUrl();
        }

        return [
            'URL'     => $baseUrl.'/'.$uploadPath,
            'path'    => $uploadPath,
            'tmbURL'  => (string)$this->baseUrl($uploadPath.'/.tmb'),
            'tmbPath' => $uploadPath.'/.tmb',
        ];
    }

    /**
     * Retrieve the elFinder root options for the given file system.
     *
     * Merges `config.filesystem.connections` with
     * {@see self::getDefaultDiskSettings() default root settings}.
     *
     * @param  string $ident The disk identifier.
     * @return array|null Returns an elFinder root structure or NULL.
     */
    public function getNamedRoot($ident)
    {
        if ($this->hasFilesystem($ident) === false) {
            return null;
        }

        $filesystem = $this->getFilesystem($ident);
        $fsConfig   = $this->getFilesystemConfig($ident);
        $elfConfig  = $this->getFilesystemAdminConfig($ident);

        $immutableSettings = [
            'filesystem'  => $filesystem,
        ];

        $root = array_replace_recursive(
            $this->getDefaultElfinderRootSettings(),
            $this->getDefaultFlysystemRootSettings(),
            $elfConfig
        );

        $root = array_replace_recursive(
            $this->resolveFallbackRootSettings($ident),
            $root,
            $immutableSettings
        );

        return $this->resolveCallbacksForRoot($root);
    }

    /**
     * Retrieve only the public elFinder root volumes.
     *
     * @return array
     */
    public function getPublicRoots()
    {
        $roots = [];
        foreach ($this->filesystems->keys() as $ident) {
            if ($this->isFilesystemPublic($ident)) {
                $disk = $this->getNamedRoot($ident);
                if ($disk !== null) {
                    $roots[$ident] = $disk;
                }
            }
        }

        return $roots;
    }

    /**
     * Retrieve all elFinder root volumes.
     *
     * @return array
     */
    public function getAllRoots()
    {
        $roots = [];
        foreach ($this->filesystems->keys() as $ident) {
            $disk = $this->getNamedRoot($ident);
            if ($disk !== null) {
                $roots[$ident] = $disk;
            }
        }

        return $roots;
    }

    /**
     * Retrieve only the current context's elFinder root volumes.
     *
     * @return array Returns all public root volumes
     *     or a subset if the context has a related form property.
     */
    public function getCurrentRoots()
    {
        $formProperty     = $this->formProperty();
        $targetFilesystem = $formProperty ? $formProperty['filesystem'] : null;

        if ($this->hasFilesystem($targetFilesystem)) {
            $disk = $this->getNamedRoot($targetFilesystem);

            $startPath = $formProperty['uploadPath'];
            $isPublic  = $formProperty['publicAccess'];
            $acceptedMimetypes = $formProperty['acceptedMimetypes'];
            $basePath  = $isPublic ? $this->publicPath : $this->basePath;

            if (!file_exists($basePath.$startPath)) {
                mkdir($basePath.$startPath, 0755, true);
            }

            if ($startPath) {
                $disk['startPath'] = $startPath;
            }

            if ($acceptedMimetypes) {
                $disk['uploadAllow'] = array_merge(
                    isset($disk['uploadAllow'])
                        ? $disk['uploadAllow']
                        : [],
                    $acceptedMimetypes
                );
            }
            return [ $disk ];
        }

        return $this->getPublicRoots();
    }

    /**
     * Resolve callables in a collection of elFinder's root volumes.
     *
     * @param  array $roots One or many roots with possible unresolved callables.
     * @return array Returns the root(s) with resolved callables.
     */
    protected function resolveCallbacksForRoots(array $roots)
    {
        foreach ($roots as $i => $root) {
            $roots[$i] = $this->resolveCallbacksForRoot($root);
        }

        return $roots;
    }

    /**
     * Resolve callables in one elFinder root volume.
     *
     * @param  array $root A root structure with possible unresolved callables.
     * @return array Returns the root with resolved callables.
     */
    protected function resolveCallbacksForRoot(array $root)
    {
        if (isset($root['accessControl'])) {
            $callable = $root['accessControl'];
            if (!is_callable($callable) && is_string($callable)) {
                $root['accessControl'] = $this->resolveCallable($callable);
            }
        }

        return $root;
    }

    /**
     * Resolve callables in elFinder's "bind" option.
     *
     * @param  array $toResolve One or many pairs of callbacks.
     * @return array Returns the parsed event listeners.
     */
    protected function resolveCallbacksForBindOption(array $toResolve)
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
     * Control file access.
     *
     * This method will disable accessing files/folders starting from '.' (dot)
     *
     * @param  string                $attr    Attribute name ("read", "write", "locked", "hidden").
     * @param  string                $path    Absolute file path.
     * @param  string                $data    Value of volume option `accessControlData`.
     * @param  \elFinderVolumeDriver $volume  ElFinder volume driver object.
     * @param  boolean|null          $isDir   Whether the path is a directory
     *     (TRUE: directory, FALSE: file, NULL: unknown).
     * @param  string                $relPath File path relative to the volume root directory
     *     started with directory separator.
     * @return boolean|null TRUE to allow, FALSE to deny, NULL to let elFinder decide.
     **/
    public function checkAccess($attr, $path, $data, elFinderVolumeDriver $volume, $isDir, $relPath)
    {
        unset($data, $volume, $isDir);

        $basename = basename($path);
        /**
         * If file/folder begins with '.' (dot) but without volume root,
         * set to FALSE if attributes are "read" or "write",
         * set to TRUE if attributes are other ("locked" + "hidden"),
         * set to NULL to let elFinder decide itself.
         */
        return ($basename[0] === '.' && strlen($relPath) !== 1)
                ? !($attr === 'read' || $attr === 'write')
                :  null;
    }

    /**
     * Retrieve the current object type from the GET parameters.
     *
     * @return string|null
     */
    public function objType()
    {
        return $this->objType;
    }

    /**
     * Retrieve the current object ID from the GET parameters.
     *
     * @return string|null
     */
    public function objId()
    {
        return $this->objId;
    }

    /**
     * Retrieve the current object's property identifier from the GET parameters.
     *
     * @return string|null
     */
    public function propertyIdent()
    {
        return $this->propertyIdent;
    }

    /**
     * Retrieve the current property.
     *
     * @return PropertyInterface|boolean A Form Property instance
     *     or FALSE if a property can not be resolved.
     */
    public function formProperty()
    {
        if ($this->formProperty === null) {
            $this->formProperty = false;

            $objType       = $this->objType();
            $propertyIdent = $this->propertyIdent();

            if ($objType && $propertyIdent) {
                $model = $this->modelFactory()->get($objType);

                if ($model->hasProperty($propertyIdent)) {
                    $this->formProperty = $model->property($propertyIdent);
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
    public function defaultUploadPath()
    {
        return self::DEFAULT_STORAGE_PATH;
    }

    /**
     * Allow upload for a subset MIME types.
     *
     * @return array
     */
    protected function defaultUploadAllow()
    {
        // By default, all images, PDF, and plain-text files are allowed.
        return [
            'image',
            'application/pdf',
            'text/plain',
        ];
    }

    /**
     * Deny upload for all MIME types.
     *
     * @return array
     */
    protected function defaultUploadDeny()
    {
        // By default, all files are rejected.
        return [
            'all',
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
            'locked'  => false,
        ];
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->basePath = $container['config']['base_path'];
        $this->publicPath = $container['config']['public_path'];

        $this->setElfinderConfig($container['elfinder/config']);
        $this->setPropertyFactory($container['property/factory']);
        $this->setCallableResolver($container['callableResolver']);

        /** @see \Charcoal\App\ServiceProvide\FilesystemServiceProvider */
        $this->filesystemConfig = $container['filesystem/config'];
        $this->filesystems = $container['filesystems'];
    }

    /**
     * Get the named filesystem object.
     *
     * @param  string $ident The filesystem identifier.
     * @return \League\Flysystem\FilesystemInterface|null Returns the filesystem instance
     *     or NULL if not found.
     */
    protected function getFilesystem($ident)
    {
        if (isset($this->filesystems[$ident])) {
            return $this->filesystems[$ident];
        }

        return null;
    }

    /**
     * Determine if the named filesystem object exists.
     *
     * @param  string $ident The filesystem identifier.
     * @return boolean TRUE if the filesystem instance exists, otherwise FALSE.
     */
    protected function hasFilesystem($ident)
    {
        return ($this->getFilesystem($ident) !== null);
    }

    /**
     * Get the given filesystem's storage configset.
     *
     * @param  string $ident The filesystem identifier.
     * @return array|null Returns the filesystem configset
     *     or NULL if the filesystem is not found.
     */
    protected function getFilesystemConfig($ident)
    {
        if ($this->hasFilesystem($ident) === false) {
            return null;
        }

        if (isset($this->filesystemConfig['connections'][$ident])) {
            return $this->filesystemConfig['connections'][$ident];
        }

        return [];
    }

    /**
     * Determine if the named filesystem is public (from its configset).
     *
     * @param  string $ident The filesystem identifier.
     * @return boolean TRUE if the filesystem is public, otherwise FALSE.
     */
    protected function isFilesystemPublic($ident)
    {
        if ($this->hasFilesystem($ident) === false) {
            return false;
        }

        $config = $this->getFilesystemConfig($ident);
        if (isset($config['public']) && $config['public'] === false) {
            return false;
        }

        return true;
    }

    /**
     * Get the given filesystem's admin configset.
     *
     * @param  string $ident The filesystem identifier.
     * @return array|null Returns the filesystem configset
     *     or NULL if the filesystem is not found.
     */
    protected function getFilesystemAdminConfig($ident)
    {
        if ($this->hasFilesystem($ident) === false) {
            return null;
        }

        $elfConfig = $this->getAdminConnectorOptions();
        if (isset($elfConfig['roots'][$ident])) {
            return $elfConfig['roots'][$ident];
        }

        return [];
    }

    /**
     * Set the elFinder's configset.
     *
     * @param  ConfigInterface $config A configset.
     * @return void
     */
    protected function setElfinderConfig(ConfigInterface $config)
    {
        $this->elfinderConfig = $config;
    }

    /**
     * Retrieve the elFinder's configset.
     *
     * @param  string|null $key     Optional data key to retrieve from the configset.
     * @param  mixed|null  $default The default value to return if data key does not exist.
     * @return mixed|AdminConfig
     */
    protected function elfinderConfig($key = null, $default = null)
    {
        if ($key) {
            if (isset($this->elfinderConfig[$key])) {
                return $this->elfinderConfig[$key];
            } else {
                if (!is_string($default) && is_callable($default)) {
                    return $default();
                } else {
                    return $default;
                }
            }
        }

        return $this->elfinderConfig;
    }

    /**
     * Set a property factory.
     *
     * @param FactoryInterface $factory The property factory,
     *                                  to createable property values.
     * @return void
     */
    protected function setPropertyFactory(FactoryInterface $factory)
    {
        $this->propertyFactory = $factory;
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
}
