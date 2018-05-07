<?php

namespace Charcoal\Admin\Template;

use ArrayIterator;
use RuntimeException;
use InvalidArgumentException;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-property'
use Charcoal\Property\FileProperty;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;

/**
 *
 */
class ElfinderTemplate extends AdminTemplate
{
    // const ELFINDER_IMG_PARENT_URL;
    const ADMIN_ASSETS_REL_PATH    = 'assets/admin/';
    const ELFINDER_ASSETS_REL_PATH = 'assets/admin/elfinder/';

    /**
     * Store the elFinder configuration from the admin configuration.
     *
     * @var \Charcoal\Config\ConfigInterface
     */
    protected $elfinderConfig;

    /**
     * Store the current property instance for the current class.
     *
     * @var \Charcoal\Property\PropertyInterface
     */
    private $formProperty;

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
     * Whether to output JS/CSS assets for initializing elFinder.
     *
     * @var boolean
     */
    private $showAssets = true;

    /**
     * Custom localization messages.
     *
     * @var {\Charcoal\Translator\Translation|string|null}[]|null
     */
    private $localizations;

    /**
     * The related JS callback ID.
     *
     * @var string
     */
    private $callbackIdent = '';


    /**
     * Sets the template data from a PSR Request object.
     *
     * @param  RequestInterface $request A PSR-7 compatible Request instance.
     * @return self
     */
    protected function setDataFromRequest(RequestInterface $request)
    {
        $keys = $this->validDataFromRequest();
        $data = $request->getParams($keys);

        if (isset($data['obj_type'])) {
            $this->objType = filter_var($data['obj_type'], FILTER_SANITIZE_STRING);
        }

        if (isset($data['obj_id'])) {
            $this->objId = filter_var($data['obj_id'], FILTER_SANITIZE_STRING);
        }

        if (isset($data['property'])) {
            $this->propertyIdent = filter_var($data['property'], FILTER_SANITIZE_STRING);
        }

        if (isset($data['assets'])) {
            $this->showAssets = !!$data['assets'];
        }

        if (isset($data['callback'])) {
            $this->callbackIdent = filter_var($data['callback'], FILTER_SANITIZE_STRING);
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
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    protected function validDataFromRequest()
    {
        return array_merge([
            // Current object
            'obj_type', 'obj_id', 'property',
            // elFinder instance
            'assets', 'callback'
        ], parent::validDataFromRequest());
    }

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation
     */
    public function title()
    {
        if ($this->title === null) {
            $this->title = $this->translator()->translation('Media Manager');
        }

        return $this->title;
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
            throw new InvalidArgumentException(sprintf(
                'Translation key must be a string, received %s',
                (is_object($ident) ? get_class($ident) : gettype($ident))
            ));
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
            throw new InvalidArgumentException(sprintf(
                'Translation key must be a string, received %s',
                (is_object($ident) ? get_class($ident) : gettype($ident))
            ));
        }

        unset($this->localizations[$ident]);

        return $this;
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
     * @return {\Charcoal\Translator\Translation|string}[]|null
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
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function localization($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(sprintf(
                'Translation key must be a string, received %s',
                (is_object($ident) ? get_class($ident) : gettype($ident))
            ));
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
        return $this->baseUrl(static::ADMIN_ASSETS_REL_PATH);
    }

    /**
     * @return string
     */
    public function elfinderAssetsUrl()
    {
        return $this->baseUrl(static::ELFINDER_ASSETS_REL_PATH);
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
     * @return \Charcoal\Property\PropertyInterface
     */
    public function formProperty()
    {
        if ($this->formProperty === null) {
            $this->formProperty = false;

            if ($this->objType() && $this->propertyIdent()) {
                $propertyIdent = $this->propertyIdent();

                $model = $this->modelFactory()->create($this->objType());
                if ($model->hasProperty($propertyIdent)) {
                    $this->formProperty = $model->property($propertyIdent);
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

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->elfinderConfig = $container['elfinder/config'];
    }

    /**
     * @return boolean
     */
    public function disableTheme()
    {
        return isset($this->elfinderConfig['disable_theme']) ?
            !!$this->elfinderConfig['disable_theme'] :
            false;
    }

    /**
     * Retrieve the default custom localizations.
     *
     * @return array
     */
    protected function defaultLocalizations()
    {
        return [
            'volume_default' => $this->translator()->translation('Library')
        ];
    }



    // Templating
    // =========================================================================

    /**
     * Determine if main & secondary menu should appear as mobile in a desktop resolution.
     *
     * @return boolean
     */
    public function isFullscreenTemplate()
    {
        // return true;
        return false;
    }
}
