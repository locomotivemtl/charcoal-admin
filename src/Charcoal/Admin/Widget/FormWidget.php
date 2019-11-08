<?php

namespace Charcoal\Admin\Widget;

use Exception;
use InvalidArgumentException;
use RuntimeException;

// From Pimple
use Pimple\Container;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

/// From 'charcoal-ui'
use Charcoal\Ui\Form\FormInterface;
use Charcoal\Ui\Form\FormTrait;
use Charcoal\Ui\Layout\LayoutAwareInterface;
use Charcoal\Ui\Layout\LayoutAwareTrait;
use Charcoal\Ui\PrioritizableInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Charcoal\Admin\Property\HierarchicalObjectProperty;
use Charcoal\Admin\Support\HttpAwareTrait;
use Charcoal\Admin\Ui\FormSidebarInterface;
use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Widget\FormPropertyWidget;
use Charcoal\Admin\Widget\FormSidebarWidget;

/**
 * A Basic Admin Form
 *
 * For submitting information to a web server.
 *
 * The widget is a variant of {@see \Charcoal\Ui\Form\AbstractForm}.
 */
class FormWidget extends AdminWidget implements
    FormInterface,
    LayoutAwareInterface
{
    use FormTrait;
    use HttpAwareTrait;
    use LayoutAwareTrait;

    /**
     * The form's sidebars.
     *
     * @var array
     */
    protected $sidebars = [];

    /**
     * Keep track if sidebars are sorted.
     *
     * @var boolean
     */
    protected $sortedSidebars = false;

    /**
     * The form's controls.
     *
     * @var array
     */
    protected $formProperties = [];

    /**
     * The form's hidden controls.
     *
     * @var array
     */
    protected $hiddenProperties = [];

    /**
     * Label for the form submission button.
     *
     * @var \Charcoal\Translator\Translation|string
     */
    protected $submitLabel;

    /**
     * The class name of the form property widget.
     *
     * Must be a fully-qualified PHP namespace and an implementation of
     * {@see \Charcoal\Admin\Widget\FormPropertyWidget}.
     * Used by the widget factory.
     *
     * @var string
     */
    protected $formPropertyClass = FormPropertyWidget::class;

    /**
     * The class name of the form sidebar widget.
     *
     * Must be a fully-qualified PHP namespace and an implementation of
     * {@see \Charcoal\Admin\Ui\FormSidebarInterface}.
     * Used by the widget factory.
     *
     * @var string
     */
    protected $sidebarClass = FormSidebarWidget::class;

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $widgetFactory;

    /**
     * @param array $data Optional. The form property data to set.
     * @return FormPropertyWidget
     */
    public function createFormProperty(array $data = null)
    {
        $p = $this->widgetFactory()->create($this->formPropertyClass());
        if ($data !== null) {
            $p->setData($data);
        }

        return $p;
    }

    /**
     * Set the class name of the form property widget.
     *
     * @param  string $className The class name of the form property widget.
     * @throws InvalidArgumentException If the class name is not a string.
     * @return FormWidget Chainable
     */
    protected function setFormPropertyClass($className)
    {
        if (!is_string($className)) {
            throw new InvalidArgumentException(
                'Form property class name must be a string.'
            );
        }

        $this->formPropertyClass = $className;
        return $this;
    }

    /**
     * Retrieve the class name of the form property widget.
     *
     * @return string
     */
    public function formPropertyClass()
    {
        return $this->formPropertyClass;
    }

    /**
     * @param array $data Optional. The form sidebar data to set.
     * @return FormSidebarInterface
     */
    public function createSidebar(array $data = null)
    {
        $sidebar = $this->widgetFactory()->create($this->sidebarClass());
        if ($data !== null) {
            $sidebar->setData($data);
        }

        return $sidebar;
    }

    /**
     * Set the class name of the form sidebar widget.
     *
     * @param  string $className The class name of the form sidebar widget.
     * @throws InvalidArgumentException If the class name is not a string.
     * @return FormWidget Chainable
     */
    protected function setSidebarClass($className)
    {
        if (!is_string($className)) {
            throw new InvalidArgumentException(
                'Form sidebar class name must be a string.'
            );
        }

        $this->sidebarClass = $className;
        return $this;
    }

    /**
     * Retrieve the class name of the form sidebar widget.
     *
     * @return string
     */
    public function sidebarClass()
    {
        return $this->sidebarClass;
    }

    /**
     * @param  string $ident Property ident.
     * @param  array  $data  Property metadata.
     * @throws InvalidArgumentException If the property is already registered.
     * @return \Charcoal\Admin\Widget\FormPropertyWidget|mixed
     */
    public function getOrCreateFormProperty($ident, array $data = null)
    {
        if ($this->updateFormProperty($ident, $data)) {
            return $this->formProperties[$ident];
        }

        $formProperty = $this->buildFormProperty($ident, $data);

        if ($this->hasHiddenProperty($ident) && $formProperty->editable()) {
            throw new InvalidArgumentException(sprintf(
                'Property "%s" is already registered as hidden input',
                $ident
            ));
        }

        if ($formProperty->hidden()) {
            if ($formProperty->prop()->val()) {
                $formProperty->setPropertyVal($formProperty->prop()->val());
            }

            $this->hiddenProperties[$ident] = $formProperty;

            return $this->hiddenProperties[$ident];
        }

        $this->formProperties[$ident] = $formProperty;

        return $this->formProperties[$ident];
    }

    /**
     * @param  string $ident Property ident.
     * @param  array  $data  Property metadata.
     * @throws InvalidArgumentException If the property is already registered.
     * @return \Charcoal\Admin\Widget\FormPropertyWidget|mixed
     */
    public function getOrCreateHiddenProperty($ident, array $data = null)
    {
        if ($this->updateHiddenProperty($ident, $data)) {
            return $this->hiddenProperties[$ident];
        }

        $formProperty = $this->buildFormProperty($ident, $data);
        $formProperty->setInputType(FormPropertyWidget::HIDDEN_FORM_CONTROL);

        if ($this->hasFormProperty($ident) && $this->formProperties[$ident]->editable()) {
            throw new InvalidArgumentException(sprintf(
                'Property "%s" is already registered as user input',
                $ident
            ));
        }

        if ($formProperty->prop()->val()) {
            $formProperty->setPropertyVal($formProperty->prop()->val());
        }

        $this->hiddenProperties[$ident] = $formProperty;

        return $this->hiddenProperties[$ident];
    }

    /**
     * @param string $ident Property ident.
     * @param array  $data  Property metadata.
     * @return \Charcoal\Admin\Widget\FormPropertyWidget|mixed
     */
    protected function buildFormProperty($ident, array $data = null)
    {
        $formProperty = $this->createFormProperty();
        $formProperty->setPropertyIdent($ident);

        if ($this instanceof ObjectContainerInterface) {
            $obj = $this->obj();

            if ($obj->hasProperty($ident)) {
                $propertyMetadata = $obj->metadata()->property($ident);
                $formProperty->setData($propertyMetadata);
            }

            $formProperty->setPropertyVal($obj[$ident]);

            if ($formProperty->propertyType() === HierarchicalObjectProperty::class) {
                $formProperty->merge([ 'obj_id' => $obj->id() ]);
            }
        }

        $formProperty->setData($data);
        $formProperty->setViewController($this->viewController());

        return $formProperty;
    }

    /**
     * @param string $ident Property ident.
     * @param array  $data  Property metadata.
     * @return \Charcoal\Admin\Widget\FormPropertyWidget|null
     */
    protected function updateFormProperty($ident, array $data = null)
    {
        if ($ident && isset($this->formProperties[$ident])) {
            $formProperty = $this->formProperties[$ident];

            if ($data !== null) {
                $formProperty->setData($data);
            }

            $this->formProperties[$ident] = $formProperty;

            return $this->formProperties[$ident];
        }

        return null;
    }

    /**
     * @param  string $ident Property ident.
     * @param  array  $data  Property metadata.
     * @return \Charcoal\Admin\Widget\FormPropertyWidget|null
     */
    protected function updateHiddenProperty($ident, array $data = null)
    {
        if ($ident && isset($this->hiddenProperties[$ident])) {
            $formProperty = $this->hiddenProperties[$ident];

            if ($data !== null) {
                unset($data['inputType'], $data['input_type']);
                $formProperty->setData($data);
            }

            $this->hiddenProperties[$ident] = $formProperty;

            return $this->hiddenProperties[$ident];
        }

        return null;
    }

    /**
     * @param  string $ident Property ident.
     * @return boolean
     */
    protected function hasFormProperty($ident)
    {
        return ($ident && isset($this->formProperties[$ident]));
    }

    /**
     * @param  string $ident Property ident.
     * @return boolean
     */
    protected function hasHiddenProperty($ident)
    {
        return ($ident && isset($this->hiddenProperties[$ident]));
    }

    /**
     * @param  string $ident Sidebar ident.
     * @param  array  $data  Sidebar metadata.
     * @throws InvalidArgumentException If the property is already registered.
     * @return FormSidebarInterface|null
     */
    public function getOrCreateSidebar($ident, array $data = null)
    {
        if ($this->updateSidebar($ident, $data)) {
            return $this->sidebars[$ident];
        }

        $sidebar = $this->buildSidebar($ident, $data);

        if ($sidebar !== null) {
            $authUser = $this->authenticator()->user();
            if (!$authUser || !$this->authorizer()->userAllowed($authUser, $sidebar->requiredGlobalAclPermissions())) {
                return null;
            }

            $this->sortedSidebars = false;
            $this->sidebars[$ident] = $sidebar;
            return $this->sidebars[$ident];
        }

        return null;
    }

    /**
     * @param  string $ident Sidebar ident.
     * @param  array  $data  Sidebar metadata.
     * @throws InvalidArgumentException If the sidebar widget type is invalid.
     * @return FormSidebarInterface|null
     */
    protected function buildSidebar($ident, array $data = null)
    {
        if (isset($data['widget_type'])) {
            $sidebar = $this->widgetFactory()->create($data['widget_type']);
            if (!($sidebar instanceof FormSidebarInterface)) {
                throw new InvalidArgumentException(sprintf(
                    'Sidebar must be an instance of FormSidebarInterface, received %s',
                    get_class($sidebar)
                ));
            }
            $sidebar->setTemplate($data['widget_type']);
        } else {
            $sidebar = $this->createSidebar();
        }

        $sidebar->setIdent($ident);
        $sidebar->setForm($this->formWidget());
        $sidebar->setData($data);

        return $sidebar;
    }

    /**
     * @param  string $ident Sidebar ident.
     * @param  array  $data  Sidebar metadata.
     * @return FormSidebarInterface|null
     */
    protected function updateSidebar($ident, array $data = null)
    {
        if ($ident && isset($this->sidebars[$ident])) {
            $sidebar = $this->sidebars[$ident];

            if ($data !== null) {
                $sidebar->setData($data);
            }

            return $this->sidebars[$ident];
        }

        return null;
    }

    /**
     * @param  string $ident Sidebar ident.
     * @return boolean
     */
    protected function hasSidebar($ident)
    {
        return ($ident && isset($this->sidebars[$ident]));
    }

    /**
     * @param array $sidebars The form sidebars.
     * @return self
     */
    public function setSidebars(array $sidebars)
    {
        $this->sortedSidebars = false;
        $this->sidebars = [];
        foreach ($sidebars as $sidebarIdent => $sidebar) {
            $this->addSidebar($sidebarIdent, $sidebar);
        }

        return $this;
    }

    /**
     * @param string                     $sidebarIdent The sidebar identifier.
     * @param array|FormSidebarInterface $sidebar      The sidebar data or object.
     * @throws InvalidArgumentException If the ident is not a string or the sidebar is not valid.
     * @return self
     */
    public function addSidebar($sidebarIdent, $sidebar)
    {
        if (!is_string($sidebarIdent)) {
            throw new InvalidArgumentException(
                'Form sidebar ident must be a string'
            );
        }

        if ($sidebar instanceof FormSidebarInterface) {
            $this->sortedSidebars = false;
            $this->sidebars[$sidebarIdent] = $sidebar;
            return $this;
        }

        if (is_array($sidebar)) {
            $this->getOrCreateSidebar($sidebarIdent, $sidebar);
            return $this;
        }

        throw new InvalidArgumentException(sprintf(
            'Form sidebar "%s" must be an array or an instance of FormSidebarInterface, received %s',
            $sidebarIdent,
            is_object($sidebar) ? get_class($sidebar) : gettype($sidebar)
        ));
    }

    /**
     * Yield the form sidebar(s).
     *
     * @return FormSidebarInterface[]|\Generator
     */
    public function sidebars()
    {
        if (!$this->sortedSidebars) {
            $this->sortedSidebars = true;
            uasort($this->sidebars, [ $this, 'sortSidebarsByPriority' ]);
        }

        $sidebars = $this->sidebars;
        foreach ($sidebars as $sidebarIdent => $sidebar) {
            if (!$sidebar->active()) {
                continue;
            }

            $this->setDynamicTemplate('sidebar_widget', $sidebar->template());
            yield $sidebarIdent => $sidebar;
        }
    }

    /**
     * Retrieve the form sidebars.
     *
     * @return FormSidebarInterface[]
     */
    public function getSidebars()
    {
        if (!$this->sortedSidebars) {
            $this->sortedSidebars = true;
            uasort($this->sidebars, [ $this, 'sortSidebarsByPriority' ]);
        }

        $sidebars = [];
        foreach ($this->sidebars as $sidebar) {
            if (!$sidebar->active()) {
                continue;
            }

            $sidebars[$sidebar->ident()] = $sidebar;
        }

        return $this->sidebars;
    }

    /**
     * Replace property controls to the form.
     *
     * @param  array $properties The form properties.
     * @return self
     */
    public function setFormProperties(array $properties)
    {
        $this->formProperties = [];

        $this->addFormProperties($properties);

        return $this;
    }

    /**
     * Add property controls to the form.
     *
     * @param  array $properties The form properties.
     * @return self
     */
    public function addFormProperties(array $properties)
    {
        foreach ($properties as $propertyIdent => $property) {
            $this->addFormProperty($propertyIdent, $property);
        }

        return $this;
    }

    /**
     * Add a property control to the form.
     *
     * If a given property uses a hidden form control, the form property will be
     * added to {@see FormWidget::$hiddenProperties}.
     *
     * @param  string                   $propertyIdent The property identifier.
     * @param  array|FormPropertyWidget $formProperty  The property object or structure.
     * @throws InvalidArgumentException If the identifier or the property is invalid.
     * @return FormInterface Chainable
     */
    public function addFormProperty($propertyIdent, $formProperty)
    {
        if (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property ident must be a string'
            );
        }

        if ($formProperty instanceof FormPropertyWidget) {
            if ($formProperty->hidden()) {
                $this->hiddenProperties[$propertyIdent] = $formProperty;
            } else {
                $this->formProperties[$propertyIdent] = $formProperty;
            }

            return $this;
        }

        if (is_array($formProperty)) {
            $this->getOrCreateFormProperty($propertyIdent, $formProperty);
            return $this;
        }

        throw new InvalidArgumentException(sprintf(
            'Property must be an array or an instance of FormPropertyWidget, received %s',
            is_object($formProperty) ? get_class($formProperty) : gettype($formProperty)
        ));
    }

    /**
     * Yield the form's property controls.
     *
     * @return \Generator
     */
    public function formProperties()
    {
        $sidebars = $this->sidebars;
        if (!is_array($sidebars)) {
            yield null;
        } else {
            foreach ($this->formProperties as $formProperty) {
                if ($formProperty->active() === false) {
                    continue;
                }
                $this->setDynamicTemplate('widget_template', $formProperty->inputType());
                yield $formProperty->propertyIdent() => $formProperty;
            }
        }
    }

    /**
     * Retrieve the form's property controls.
     *
     * @return FormPropertyWidget[]
     */
    public function getFormProperties()
    {
        $formProperties = [];
        foreach ($this->formProperties as $formProperty) {
            if ($formProperty->active() === false) {
                continue;
            }

            $formProperties[$formProperty->propertyIdent()] = $formProperty;
        }

        return $formProperties;
    }

    /**
     * Replace hidden property controls to the form.
     *
     * @param  array $properties The hidden form properties.
     * @return FormInterface Chainable
     */
    public function setHiddenProperties(array $properties)
    {
        $this->hiddenProperties = [];

        $this->addHiddenProperties($properties);

        return $this;
    }

    /**
     * Add hidden property controls to the form.
     *
     * @param  array $properties The hidden form properties.
     * @return FormInterface Chainable
     */
    public function addHiddenProperties(array $properties)
    {
        foreach ($properties as $propertyIdent => $property) {
            $this->addHiddenProperty($propertyIdent, $property);
        }

        return $this;
    }

    /**
     * Add a hidden property control to the form.
     *
     * @param  string                      $propertyIdent The property identifier.
     * @param  array|FormPropertyInterface $formProperty  The property object or structure.
     * @throws InvalidArgumentException If the identifier or the property is invalid.
     * @return FormInterface Chainable
     */
    public function addHiddenProperty($propertyIdent, $formProperty)
    {
        if (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property ident must be a string'
            );
        }

        if ($formProperty instanceof FormPropertyWidget) {
            if ($formProperty->hidden()) {
                $this->hiddenProperties[$propertyIdent] = $formProperty;
                return $this;
            }

            throw new InvalidArgumentException(
                'Form property must be a hidden'
            );
        }

        if (is_array($formProperty)) {
            $this->getOrCreateHiddenProperty($propertyIdent, $formProperty);
            return $this;
        }

        throw new InvalidArgumentException(sprintf(
            'Form property must be an array or an instance of FormPropertyWidget, received %s',
            is_object($formProperty) ? get_class($formProperty) : gettype($formProperty)
        ));
    }

    /**
     * Yield the form's hidden property controls.
     *
     * @return \Generator
     */
    public function hiddenProperties()
    {
        foreach ($this->hiddenProperties as $formProperty) {
            if ($formProperty->active() === false) {
                continue;
            }

            yield $formProperty->propertyIdent() => $formProperty;
        }
    }

    /**
     * Retrieve the label for the form submission button.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function submitLabel()
    {
        if ($this->submitLabel === null) {
            $this->submitLabel = $this->defaultSubmitLabel();
        }

        return $this->submitLabel;
    }

    /**
     * Retrieve the default label for the form submission button.
     *
     * @return \Charcoal\Translator\Translation|null
     */
    public function defaultSubmitLabel()
    {
        return $this->translator()->translation('Save');
    }

    /**
     * @return string
     */
    public function defaultGroupType()
    {
        return 'charcoal/admin/widget/form-group/generic';
    }

    /**
     * @param  Container $container The DI container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Satisfies HttpAwareTrait dependencies
        $this->setHttpRequest($container['request']);

        $this->setWidgetFactory($container['widget/factory']);

        // Satisfies FormInterface
        $this->setFormGroupFactory($container['form/group/factory']);

        // Satisfies LayoutAwareInterface
        $this->setLayoutBuilder($container['layout/builder']);
    }

    /**
     * Retrieve the widget factory.
     *
     * @throws RuntimeException If the widget factory was not previously set.
     * @return FactoryInterface
     */
    protected function widgetFactory()
    {
        if ($this->widgetFactory === null) {
            throw new RuntimeException(sprintf(
                'Widget Factory is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->widgetFactory;
    }

    /**
     * Set an widget factory.
     *
     * @param  FactoryInterface $factory The factory to create widgets.
     * @return void
     */
    protected function setWidgetFactory(FactoryInterface $factory)
    {
        $this->widgetFactory = $factory;
    }

    /**
     * Fetch metadata from the current request.
     *
     * @return array
     */
    protected function dataFromRequest()
    {
        return $this->httpRequest()->getParams($this->acceptedRequestData());
    }

    /**
     * Retrieve the accepted metadata from the current request.
     *
     * @return array
     */
    protected function acceptedRequestData()
    {
        return [
            'form_ident',
            'form_data',
            'l10n_mode',
            'group_display_mode',
            'next_url',
            'tab_ident',
        ];
    }

    /**
     * Comparison function used by {@see uasort()}.
     *
     * @param  PrioritizableInterface $a Sortable entity A.
     * @param  PrioritizableInterface $b Sortable entity B.
     * @return integer Sorting value: -1 or 1.
     */
    protected function sortItemsByPriority(
        PrioritizableInterface $a,
        PrioritizableInterface $b
    ) {
        $priorityA = $a->priority();
        $priorityB = $b->priority();

        if ($priorityA === $priorityB) {
            return 0;
        }

        return ($priorityA < $priorityB) ? (-1) : 1;
    }

    /**
     * To be called with {@see uasort()}.
     *
     * @param  FormSidebarInterface $a Sortable entity A.
     * @param  FormSidebarInterface $b Sortable entity B.
     * @return integer Sorting value: -1, 0, or 1
     */
    protected function sortSidebarsByPriority(
        FormSidebarInterface $a,
        FormSidebarInterface $b
    ) {
        $a = $a->priority();
        $b = $b->priority();

        if ($a === $b) {
            return 0;
        }

        return ($a < $b) ? (-1) : 1;
    }
}
