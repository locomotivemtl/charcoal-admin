<?php

namespace Charcoal\Admin\Template;

use RuntimeException;
use InvalidArgumentException;
use ArrayIterator;

use Psr\Http\Message\RequestInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-property'
use Charcoal\Property\FileProperty;

// Local module (charcoal-admin) dependency
use Charcoal\Admin\AdminTemplate;

/**
 *
 */
class ElfinderTemplate extends AdminTemplate
{
    /**
     * Store the elFinder configuration from the admin configuration.
     *
     * @var \Charcoal\Config\ConfigInterface
     */
    protected $elfinderConfig;

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface $propertyFactory
     */
    private $propertyFactory;

    /**
     * Store the current property instance for the current class.
     *
     * @var PropertyInterface $formProperty
     */
    private $formProperty;

    /**
     * @var string $objType
     */
    private $objType;

    /**
     * @var string $objId
     */
    private $objId;

    /**
     * @var string $propertyIdent
     */
    private $propertyIdent;

    /**
     * @var boolean $showAssets
     */
    private $showAssets = true;

    /**
     * Custom localization messages.
     *
     * @var Translation|Traversable|array
     */
    private $localizations;

    /**
     * @var string $callbackIdent
     */
    private $callbackIdent = '';


    /**
     * Retrieve options from Request's parameters (GET).
     *
     * @param RequestInterface $request The PSR7 request.
     * @return boolean
     */
    public function init(RequestInterface $request)
    {
        $params = $request->getParams();

        if (isset($params['obj_type'])) {
            $this->objType = filter_var($params['obj_type'], FILTER_SANITIZE_STRING);
        }

        if (isset($params['obj_id'])) {
            $this->objId = filter_var($params['obj_id'], FILTER_SANITIZE_STRING);
        }

        if (isset($params['property'])) {
            $this->propertyIdent = filter_var($params['property'], FILTER_SANITIZE_STRING);
        }

        if (isset($params['assets'])) {
            $this->showAssets = !!$params['assets'];
        }

        if (isset($params['callback'])) {
            $this->callbackIdent = filter_var($params['callback'], FILTER_SANITIZE_STRING);
        }

        if (isset($this->elfinderConfig['translations'])) {
            $this->setLocalizations(array_replace_recursive(
                $this->defaultLocalizations(),
                $this->elfinderConfig['translations']
            ));
        }

        return true;
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

        $this->elfinderConfig = $container['elfinder/config'];
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
     * Set the custom localization messages.
     *
     * @param  array $localizations An associative array of localizations.
     * @return self
     */
    public function setLocalizations(array $localizations)
    {
        $this->localizations = new ArrayIterator();

        foreach ($localizations as $ident => $translations) {
            $this->addLocalization($ident, $translations);
        }

        return $this;
    }

    /**
     * Add a custom localization message.
     *
     * @param  string $ident        The message ID.
     * @param  mixed  $translations The message translations.
     * @throws InvalidArgumentException If the message ID is not a string or the translations are invalid.
     * @return self
     */
    public function addLocalization($ident, $translations)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Translation key must be a string, received %s',
                    (is_object($ident) ? get_class($ident) : gettype($ident))
                )
            );
        }

        $this->localizations[$ident] = $this->translator()->translation($translations);

        return $this;
    }

    /**
     * Remove the translations for the given message ID.
     *
     * @param  string $ident The message ID to remove.
     * @throws InvalidArgumentException If the message ID is not a string.
     * @return self
     */
    public function removeLocalization($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Translation key must be a string, received %s',
                    (is_object($ident) ? get_class($ident) : gettype($ident))
                )
            );
        }

        unset($this->localizations[$ident]);

        return $this;
    }

    /**
     * Retrieve the default custom localizations.
     *
     * @return array
     */
    protected function defaultLocalizations()
    {
        return [
            'volume_default' => [
                'en' => 'Library',
                'fr' => 'Bibliothèque'
            ]
        ];
    }

    /**
     * Count the number of localizations.
     *
     * @return integer
     */
    public function numLocalizations()
    {
        return count($this->localizations());
    }

    /**
     * Determine if there are any localizations.
     *
     * @return boolean
     */
    public function hasLocalizations()
    {
        return !!$this->numLocalizations();
    }

    /**
     * Retrieve the localizations.
     *
     * @return Translation[]|Traversable|array|null
     */
    public function localizations()
    {
        if ($this->localizations === null) {
            $this->setLocalizations($this->defaultLocalizations());
        }

        return $this->localizations;
    }

    /**
     * Retrieve the translations for the given message ID.
     *
     * @param  string $ident The message ID to lookup.
     * @throws InvalidArgumentException If the message ID is not a string.
     * @return Translation[]|string
     */
    public function localization($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Translation key must be a string, received %s',
                    (is_object($ident) ? get_class($ident) : gettype($ident))
                )
            );
        }

        if (isset($this->localizations[$ident])) {
            return $this->localizations[$ident];
        }

        return $ident;
    }

    /**
     * Retrieve the custom localizations for elFinder.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function elfinderLocalizationsAsJson()
    {
        $i18n = [];

        foreach ($this->localizations() as $id => $translations) {
            foreach ($translations->data() as $language => $message) {
                $i18n[$language][$id] = $message;
            }
        }

        return json_encode($i18n, (JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
    }

    /**
     * @return string
     */
    public function adminAssetsUrl()
    {
        return $this->baseUrl().'assets/admin/';
    }

    /**
     * @return string
     */
    public function elfinderAssetsUrl()
    {
        return $this->baseUrl().'assets/admin/elfinder/';
    }

    /**
     * @return string
     */
    public function elfinderAssets()
    {
        return $this->showAssets;
    }

    /**
     * Retrieve the current elFinder callback ID from the GET parameters.
     *
     * @return string|null
     */
    public function elfinderCallback()
    {
        return $this->callbackIdent;
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
    public function elfinderConfigAsJson()
    {
        $property = $this->formProperty();
        $settings = [];

        if ($this->elfinderConfig['client']) {
            $settings = $this->elfinderConfig['client'];
        }

        $settings['lang'] = $this->translator()->getLocale();

        if ($property) {
            $mimeTypes = filter_input(INPUT_GET, 'filetype', FILTER_SANITIZE_STRING);

            if ($mimeTypes) {
                if ($mimeTypes === 'file') {
                    $mimeTypes = [];
                }

                $settings['onlyMimes'] = (array)$mimeTypes;
            } elseif ($property instanceof FileProperty) {
                $settings['onlyMimes'] = $property->acceptedMimetypes();
            }

            $settings['rememberLastDir'] = !($property instanceof FileProperty);
        }

        return json_encode($settings, (JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
    }
}
