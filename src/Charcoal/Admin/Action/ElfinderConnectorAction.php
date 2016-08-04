<?php

namespace Charcoal\Admin\Action;

use Charcoal\Factory\FactoryInterface;
use Charcoal\Property\PropertyInterface;
use \InvalidArgumentException;

// Dependencies from PSR-7 (HTTP Messaging)
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Dependency from Pimple
use \Pimple\Container;

// Dependencies from elFinder
use \elFinderConnector;
use \elFinder;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminAction;
use SebastianBergmann\PHPLOC\RuntimeException;

/**
 * Elfinder connector
 */
class ElfinderConnectorAction extends AdminAction
{

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
     * @param Container $container Pimple DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->appConfig = $container['config'];
        $this->setPropertyFactory($container['property/factory']);
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
        $startPath = 'uploads/';

        $formData = $request->getParams();

        $formProperty = $this->formProperty($formData);

        error_log(var_export($formProperty['uploadPath'], true));

        if(isset($formProperty['uploadPath'])) {
            $startPath = $formProperty['uploadPath'];

            if (!file_exists($startPath)) {
                mkdir($startPath, 0777, true);
            }
        }

        error_log(var_export($startPath, true));

            // Documentation for connector options:
            // https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
        $opts = [
            'debug' => false,
            'roots' => [
                [
                    // Driver for accessing file system (REQUIRED)
                    'driver'        => 'LocalFileSystem',
                    // Path to files (REQUIRED)
                    'path'          => 'uploads/',

                    'startPath'     => $startPath,

                    // URL to files (REQUIRED)
                    'URL'           => $this->appConfig['URL'].'uploads',
                    // All MIME types not allowed to upload
                    'uploadDeny'    => [ 'all' ],
                    // MIME type `image` and `text/plain` allowed to upload
                    'uploadAllow'   => [ 'image', 'application/pdf', 'text/plain' ],
                    // Allowed MIME type `image` and `text/plain` only
                    'uploadOrder'   => [ 'deny', 'allow' ],
                    // Disable and hide dot starting files (OPTIONAL)
                    'accessControl' => 'access',
                    // File permission attributes
                    'attributes'    => [
                        [
                            // Block access to all hidden files and directories (anything starting with ".")
                            'pattern' => '!(?:^|/)\..+$!',
                            'read'    => false,
                            'write'   => false,
                            'hidden'  => true,
                            'locked'  => false
                        ]
                    ]
                ]
            ]
        ];

        // run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();

        return $response;
    }

    /**
     * Retrieve the current object type from the GET parameters.
     *
     * @param $formData
     * @return string
     */
    public function objType($formData)
    {
        return (isset($formData['obj_type']) ? $formData['obj_type'] : null);
    }

    /**
     * Retrieve the current object ID from the GET parameters.
     *
     * @param $formData
     * @return string
     */
    public function objId($formData)
    {
        return (isset($formData['obj_id']) ? $formData['obj_id'] : null);
    }

    /**
     * Retrieve the current object's property identifier from the GET parameters.
     *
     * @param $formData
     * @return string
     */
    public function propertyIdent($formData)
    {
        return (isset($formData['property']) ? $formData['property'] : null);
    }

    /**
     * Retrieve the current property.
     *
     * @param $formData
     * @return PropertyInterface
     */
    public function formProperty($formData)
    {
        if ($this->formProperty === null) {
            $this->formProperty = false;

            if ($this->objType($formData) && $this->propertyIdent($formData)) {
                $propertyIdent = $this->propertyIdent($formData);

                $model = $this->modelFactory()->create($this->objType($formData));
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
}
