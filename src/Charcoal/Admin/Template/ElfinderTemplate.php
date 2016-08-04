<?php

namespace Charcoal\Admin\Template;

use \RuntimeException;

// Dependency from Pimple
use \Pimple\Container;

// Dependency from 'charcoal-factory'
use \Charcoal\Factory\FactoryInterface;

// Dependency from 'charcoal-property'
use Charcoal\Property\FileProperty;

// Dependency from 'charcoal-translation'
use \Charcoal\Translation\TranslationConfig;

// Local parent namespace dependencies
use \Charcoal\Admin\AdminTemplate;

/**
 *
 */
class ElfinderTemplate extends AdminTemplate
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
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

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
     * @return string
     */
    public function elfinderUrl()
    {
        return $this->baseUrl().'assets/admin/elfinder/';
    }

    /**
     * @return string
     */
    public function elfinderAssets()
    {
        $flag = filter_input(INPUT_GET, 'assets', FILTER_VALIDATE_BOOLEAN);

        if ($flag === null) {
            return true;
        }

        return $flag;
    }

    /**
     * Retrieve the current elFinder callback ID from the GET parameters.
     *
     * @return string|null
     */
    public function elfinderCallback()
    {
        return filter_input(INPUT_GET, 'callback', FILTER_SANITIZE_STRING);
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
     * Retrieve the current property's client-side settings for elFinder.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function elfinderPropertyConfig()
    {
        $property = $this->formProperty();
        $settings = [];

        if ($property) {
            $translator = TranslationConfig::instance();

            $settings['lang'] = $translator->currentLanguage();

            $mimeTypes = filter_input(INPUT_GET, 'filetype', FILTER_SANITIZE_STRING);
            if ($mimeTypes) {
                $settings['onlyMimes'] = (array)$mimeTypes;
            } elseif ($property instanceof FileProperty) {
                $settings['onlyMimes'] = $property->acceptedMimetypes();
            }

            $settings['rememberLastDir'] = !($property instanceof FileProperty);
        }

        return json_encode($settings, (JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
    }
}
