<?php

namespace Charcoal\Admin\Widget;

use \Exception;
use \InvalidArgumentException;
use \RuntimeException;

// From Pimple
use \Pimple\Container;

// From PSR-7 (HTTP Messaging)
use \Psr\Http\Message\RequestInterface;

// From 'charcoal-translation'
use \Charcoal\Translation\TranslationString;

// From `charcoal-app`
use \Charcoal\Factory\FactoryInterface;

/// From `charcoal-ui`
use \Charcoal\Ui\Form\FormInterface;
use \Charcoal\Ui\Form\FormTrait;
use \Charcoal\Ui\FormGroup\FormGroupInterface;
use \Charcoal\Ui\Layout\LayoutAwareInterface;
use \Charcoal\Ui\Layout\LayoutAwareTrait;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Ui\FormSidebarInterface;
use \Charcoal\Admin\User\AuthAwareInterface;
use \Charcoal\Admin\User\AuthAwareTrait;

/**
 * A Basic Admin Form
 *
 * For submitting information to a web server.
 *
 * The widget is a variant of {@see \Charcoal\Ui\Form\AbstractForm}.
 */
class FormWidget extends AdminWidget implements
    FormInterface,
    AuthAwareInterface,
    LayoutAwareInterface
{
    use FormTrait;
    use AuthAwareTrait;
    use LayoutAwareTrait;

    /**
     * The form's sidebars.
     *
     * @var array
     */
    protected $sidebars = [];

    /**
     * Label for the form submission button.
     *
     * @var TranslationString|string
     */
    protected $submitLabel;

    /**
     * Store the HTTP request object.
     *
     * @var RequestInterface
     */
    private $httpRequest;

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $widgetFactory;

    /**
     * @param  Container $container The DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setHttpRequest($container['request']);
        $this->setWidgetFactory($container['widget/factory']);

        // Satisfies FormInterface
        $this->setFormGroupFactory($container['form/group/factory']);

        // Satisfies LayoutAwareInterface
        $this->setLayoutBuilder($container['layout/builder']);

        // Satisfies AuthAwareInterface
        $this->setAuthDependencies($container);
    }

    /**
     * Set an HTTP request object.
     *
     * @param RequestInterface $request A PSR-7 compatible Request instance.
     * @return self
     */
    protected function setHttpRequest(RequestInterface $request)
    {
        $this->httpRequest = $request;

        return $this;
    }

    /**
     * Retrieve the HTTP request object.
     *
     * @throws RuntimeException If an HTTP request was not previously set.
     * @return RequestInterface
     */
    public function httpRequest()
    {
        if (!isset($this->httpRequest)) {
            throw new RuntimeException(
                sprintf('A PSR-7 Request instance is not defined for "%s"', get_class($this))
            );
        }

        return $this->httpRequest;
    }

    /**
     * Set an widget factory.
     *
     * @param FactoryInterface $factory The factory to create widgets.
     * @return self
     */
    protected function setWidgetFactory(FactoryInterface $factory)
    {
        $this->widgetFactory = $factory;

        return $this;
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
            throw new RuntimeException(
                sprintf('Widget Factory is not defined for "%s"', get_class($this))
            );
        }

        return $this->widgetFactory;
    }

    /**
     * @param array $data Optional. The form property data to set.
     * @return FormPropertyWidget
     */
    public function createFormProperty(array $data = null)
    {
        $p = $this->widgetFactory()->create('charcoal/admin/widget/form-property');
        if ($data !== null) {
            $p->setData($data);
        }

        return $p;
    }

    /**
     * Fetch metadata from the current request.
     *
     *
     * @return array
     */
    protected function dataFromRequest()
    {
        $request = $this->httpRequest();

        return array_intersect_key($request->getParams(), array_flip($this->acceptedRequestData()));
    }

    /**
     * Retrieve the accepted metadata from the current request.
     *
     * @return array
     */
    protected function acceptedRequestData()
    {
        return [ 'next_url', 'form_ident', 'form_data', 'l10n_mode', 'group_display_mode' ];
    }

    /**
     * @param array $sidebars The form sidebars.
     * @return FormWidget Chainable
     */
    public function setSidebars(array $sidebars)
    {
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
     * @return FormWidget Chainable
     */
    public function addSidebar($sidebarIdent, $sidebar)
    {
        if (!is_string($sidebarIdent)) {
            throw new InvalidArgumentException(
                'Sidebar ident must be a string'
            );
        }
        if (($sidebar instanceof FormSidebarInterface)) {
            $this->sidebars[$sidebarIdent] = $sidebar;
        } elseif (is_array($sidebar)) {
            if (isset($sidebar['widget_type'])) {
                $s = $this->widgetFactory()->create($sidebar['widget_type']);
                $s->setTemplate($sidebar['widget_type']);
            }

            if (!isset($s) || !($s instanceof FormSidebarInterface)) {
                $s = $this->widgetFactory()->create('charcoal/admin/widget/form-sidebar');
            }

            $s->setForm($this);
            $s->setData($sidebar);

            // Test sidebar vs. ACL roles
            $authUser = $this->authenticator()->authenticate();
            if (!$this->authorizer()->userAllowed($authUser, $s->requiredGlobalAclPermissions())) {
                header('HTTP/1.0 403 Forbidden');
                header('Location: '.$this->adminUrl().'login');

                return $this;
            }

            $this->sidebars[$sidebarIdent] = $s;
        } else {
            throw new InvalidArgumentException(
                'Sidebar must be a FormSidebarWidget object or an array'
            );
        }

        return $this;
    }

    /**
     * @return FormSidebarInterface[]|Generator
     */
    public function sidebars()
    {
        $sidebars = $this->sidebars;
        uasort($sidebars, [ $this, 'sortSidebarsByPriority' ]);
        foreach ($sidebars as $sidebarIdent => $sidebar) {
            if ($sidebar->template()) {
                $template = $sidebar->template();
            } else {
                $template = 'charcoal/admin/widget/form.sidebar';
            }
            $GLOBALS['widget_template'] = $template;
            yield $sidebarIdent => $sidebar;
        }
    }

    /**
     * To be called with uasort().
     *
     * @param FormSidebarInterface $a Item "a" to compare, for sorting.
     * @param FormSidebarInterface $b Item "b" to compaer, for sorting.
     * @return integer Sorting value: -1, 0, or 1
     */
    protected static function sortSidebarsByPriority(FormSidebarInterface $a, FormSidebarInterface $b)
    {
        $a = $a->priority();
        $b = $b->priority();

        return ($a < $b) ? (-1) : 1;
    }

    /**
     * @param array $properties The form properties.
     * @return FormInterface Chainable
     */
    public function setFormProperties(array $properties)
    {
        $this->formProperties = [];
        foreach ($properties as $propertyIdent => $property) {
            $this->addFormProperty($propertyIdent, $property);
        }

        return $this;
    }

    /**
     * @param string                      $propertyIdent The property identifier.
     * @param array|FormPropertyInterface $property      The property object or structure.
     * @throws InvalidArgumentException If the ident is not a string or the property not a valid object or structure.
     * @return FormInterface Chainable
     */
    public function addFormProperty($propertyIdent, $property)
    {
        if (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property ident must be a string'
            );
        }

        if (($property instanceof FormPropertyWidget)) {
            $this->formProperties[$propertyIdent] = $property;
        } elseif (is_array($property)) {
            $p = $this->createFormProperty($property);
            $p->setPropertyIdent($propertyIdent);
            $this->formProperties[$propertyIdent] = $p;
        } else {
            throw new InvalidArgumentException(
                'Property must be a FormProperty object or an array'
            );
        }

        return $this;
    }

    /**
     * Properties generator
     *
     * @return FormPropertyWidget[] This method is a generator.
     */
    public function formProperties()
    {
        $sidebars = $this->sidebars;
        if (!is_array($this->sidebars)) {
            yield null;
        } else {
            foreach ($this->formProperties as $prop) {
                if ($prop->active() === false) {
                    continue;
                }
                $GLOBALS['widget_template'] = $prop->inputType();
                yield $prop->propertyIdent() => $prop;
            }
        }
    }

    /**
     * Retrieve the label for the form submission button.
     *
     * @return TranslationString|string|null
     */
    public function submitLabel()
    {
        if ($this->submitLabel === null) {
            $this->submitLabel = new TranslationString([
                'en' => 'Save',
                'fr' => 'Sauvegarder'
            ]);
        }

        return $this->submitLabel;
    }

    /**
     * @return string
     */
    public function defaultGroupType()
    {
        return 'charcoal/admin/widget/form-group/generic';
    }
}
