<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException;

// From Pimple
use \Pimple\Container;

// From 'charcoal-translation'
use \Charcoal\Translation\TranslationConfig;

// From 'charcoal-ui'
use \Charcoal\Ui\AbstractUiItem;
use \Charcoal\Ui\FormGroup\FormGroupInterface;
use \Charcoal\Ui\FormGroup\FormGroupTrait;
use \Charcoal\Ui\Layout\LayoutAwareInterface;
use \Charcoal\Ui\Layout\LayoutAwareTrait;

// From 'charcoal-admin'
use \Charcoal\Admin\Ui\ObjectContainerInterface;

/**
 * Form Group Widget Controller
 */
class FormGroupWidget extends AbstractUiItem implements
    FormGroupInterface,
    LayoutAwareInterface
{
    use FormGroupTrait;
    use LayoutAwareTrait;

    /**
     * The widget identifier.
     *
     * @var string
     */
    private $widgetId;

    /**
     * @var array|null $parsedFormProperties
     */
    protected $parsedFormProperties;

    /**
     * @var array $groupProperties
     */
    private $groupProperties = [];

    /**
     * @var array $propertiesOptions
     */
    private $propertiesOptions = [];

    /**
     * @param array|\ArrayAccess $data Dependencies.
     */
    public function __construct($data)
    {
        parent::__construct($data);

        if (isset($data['form'])) {
            $this->setForm($data['form']);
        }

        $this->setFormInputBuilder($data['form_input_builder']);

        // Set up layout builder (to fulfill LayoutAware Interface)
        $this->setLayoutBuilder($data['layout_builder']);
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Fill LayoutAwareInterface dependencies
        $this->setLayoutBuilder($container['layout/builder']);
    }

    /**
     * @param  array|\ArrayAccess $data Widget data.
     * @return FormGroupWidget Chainable
     */
    public function setData($data)
    {
        if (!empty($data['properties'])) {
            $this->setGroupProperties($data['properties']);
            unset($data['properties']);
        }

        if (isset($data['permissions'])) {
            $this->setRequiredAclPermissions($data['permissions']);
            unset($data['permissions']);
        }

        parent::setData($data);

        return $this;
    }

    /**
     * @return string
     */
    public function type()
    {
        return 'charcoal/admin/widget/form-group-widget';
    }

    /**
     * @param string $widgetId The widget identifier.
     * @return AdminWidget Chainable
     */
    public function setWidgetId($widgetId)
    {
        $this->widgetId = $widgetId;

        return $this;
    }

    /**
     * @return string
     */
    public function widgetId()
    {
        if (!$this->widgetId) {
            $this->widgetId = 'widget_'.uniqid();
        }

        return $this->widgetId;
    }

    /**
     * @param array $properties The group properties.
     * @return FormGroupWidget Chainable
     */
    public function setGroupProperties(array $properties)
    {
        $this->groupProperties      = $properties;
        $this->parsedFormProperties = null;

        return $this;
    }

    /**
     * @return array
     */
    public function groupProperties()
    {
        return $this->groupProperties;
    }

    /**
     * @param array $properties The options to customize the group properties.
     * @return FormGroupWidget Chainable
     */
    public function setPropertiesOptions(array $properties)
    {
        $this->propertiesOptions = $properties;

        return $this;
    }

    /**
     * @return array
     */
    public function propertiesOptions()
    {
        return $this->propertiesOptions;
    }

    /**
     * Parse the form group and model properties.
     *
     * @return array
     */
    protected function parsedFormProperties()
    {
        if ($this->parsedFormProperties === null) {
            $groupProperties = $this->groupProperties();
            $formProperties  = $this->form()->formProperties($groupProperties);

            $this->parsedFormProperties = $formProperties;
        }

        return $this->parsedFormProperties;
    }

    /**
     * Determine if the form group has properties.
     *
     * @return boolean
     */
    public function hasFormProperties()
    {
        return !!count($this->parsedFormProperties());
    }

    /**
     * Retrieve the object's properties from the form.
     *
     * @return mixed|Generator
     */
    public function formProperties()
    {
        $form = $this->form();
        $obj  = ($form instanceof ObjectContainerInterface) ? $form->obj() : null;

        $groupProperties = $this->groupProperties();
        $formProperties  = $this->parsedFormProperties();
        $propOptions     = $this->propertiesOptions();

        $ret = [];
        foreach ($formProperties as $propertyIdent => $property) {
            if (in_array($propertyIdent, $groupProperties)) {
                if (!empty($propOptions[$propertyIdent])) {
                    $propertyOptions = $propOptions[$propertyIdent];

                    if (is_array($propertyOptions)) {
                        $property->setData($propertyOptions);
                    }
                }

                if ($obj) {
                    $val = $obj[$propertyIdent];
                    $property->setPropertyVal($val);
                }

                if (!$property->l10nMode()) {
                    $property->setL10nMode($this->l10nMode());
                }

                yield $propertyIdent => $property;
            }
        }
    }

    /**
     * Retrieve the available languages, formatted for the sidebar language-switcher.
     *
     * @return array|Generator
     */
    public function languages()
    {
        $trans   = TranslationConfig::instance();
        $curLang = $trans->currentLanguage();
        $langs   = $trans->languages();

        foreach ($langs as $lang) {
            yield [
                'ident'   => $lang->ident(),
                'name'    => $lang->name(),
                'current' => ($lang->ident() === $curLang)
            ];
        }
    }
}
