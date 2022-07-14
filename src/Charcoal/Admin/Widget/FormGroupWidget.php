<?php

namespace Charcoal\Admin\Widget;

use InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From 'charcoal-ui'
use Charcoal\Ui\AbstractUiItem;
use Charcoal\Ui\FormGroup\FormGroupTrait;
use Charcoal\Ui\FormInput\FormInputInterface;
use Charcoal\Ui\Layout\LayoutAwareInterface;
use Charcoal\Ui\Layout\LayoutAwareTrait;

// From 'charcoal-admin'
use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Ui\FormGroupInterface;

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
     * Whether notes shoudl be display before or after the form fields.
     *
     * @var boolean
     */
    private $showNotesAbove = false;

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
    }

    /**
     * @param  array $data Widget data.
     * @return FormGroupWidget Chainable
     */
    public function setData(array $data)
    {
        if (!empty($data['properties'])) {
            $this->setGroupProperties($data['properties']);
            unset($data['properties']);
        }

        if (!empty($data['hidden_properties'])) {
            $this->form()->addHiddenProperties($data['hidden_properties']);
            unset($data['hidden_properties']);
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

        $groupProperties = array_map([ $this, 'camelize' ], $this->groupProperties());
        $formProperties  = $this->parsedFormProperties();
        $propOptions     = $this->propertiesOptions();

        foreach ($formProperties as $propertyIdent => $formProperty) {
            $propertyIdent = $this->camelize($propertyIdent);
            if (in_array($propertyIdent, $groupProperties)) {
                if (!empty($propOptions[$propertyIdent])) {
                    $propertyOptions = $propOptions[$propertyIdent];

                    if (is_array($propertyOptions)) {
                        $formProperty->merge($propertyOptions);
                    }
                }

                if ($obj) {
                    $val = $obj[$propertyIdent];
                    $formProperty->setPropertyVal($val);
                }

                if (!$formProperty->l10nMode()) {
                    $formProperty->setL10nMode($this->l10nMode());
                }

                if ($formProperty instanceof FormInputInterface) {
                    $formProperty->setFormGroup($this);
                }

                yield $propertyIdent => $formProperty;

                if ($formProperty instanceof FormInputInterface) {
                    $formProperty->clearFormGroup();
                }
            }
        }
    }

    /**
     * Retrieve the available languages, formatted for the sidebar language-switcher.
     *
     * @see    FormSidebarWidget::languages()
     * @return array
     */
    public function languages()
    {
        $currentLocale = $this->translator()->getLocale();
        $languages = [];
        foreach ($this->translator()->locales() as $locale => $localeStruct) {
            /**
             * @see \Charcoal\Admin\Widget\FormSidebarWidget::languages()
             * @see \Charcoal\Property\LangProperty::localeChoices()
             */
            if (isset($localeStruct['name'])) {
                $label = $this->translator()->translation($localeStruct['name']);
            } else {
                $trans = 'locale.'.$locale;
                if ($trans === $this->translator()->translate($trans)) {
                    $label = strtoupper($locale);
                } else {
                    $label = $this->translator()->translation($trans);
                }
            }

            $languages[] = [
                'ident'   => $locale,
                'name'    => $this->translator()->translation($label),
                'current' => ($locale === $currentLocale)
            ];
        }

        return $languages;
    }

    /**
     * Retrieve the current language.
     *
     * @return string
     */
    public function lang()
    {
        return $this->translator()->getLocale();
    }

    /**
     * Retrieve the current language.
     *
     * @return string
     */
    public function locale()
    {
        $lang    = $this->lang();
        $locales = $this->translator()->locales();

        if (isset($locales[$lang]['locale'])) {
            $locale = $locales[$lang]['locale'];
            if (is_array($locale)) {
                $locale = implode(' ', $locale);
            }
        } else {
            $locale = 'en-US';
        }

        return $locale;
    }

    /**
     * @return Translation|string|null
     */
    public function description()
    {
        return $this->renderTemplate((string)parent::description());
    }

    /**
     * @return Translation|string|null
     */
    public function notes()
    {
        return $this->renderTemplate((string)parent::notes());
    }

    /**
     * Show/hide the widget's notes.
     *
     * @param  boolean|string $show Whether to show or hide notes.
     * @return FormGroupWidget Chainable
     */
    public function setShowNotes($show)
    {
        $this->showNotesAbove = ($show === 'above');

        return parent::setShowNotes($show);
    }

    /**
     * @return boolean
     */
    public function showNotesAbove()
    {
        return $this->showNotesAbove && $this->showNotes();
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setFormInputBuilder($container['form/input/builder']);

        // Satisfies ViewableInterface dependencies
        $this->setView($container['view']);

        // Satisfies LayoutAwareInterface dependencies
        $this->setLayoutBuilder($container['layout/builder']);
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
}
