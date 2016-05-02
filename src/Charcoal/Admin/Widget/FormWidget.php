<?php

namespace Charcoal\Admin\Widget;

use InvalidArgumentException;

use \Pimple\Container;

/// From `charcoal-ui`
use \Charcoal\Ui\Form\FormInterface;
use \Charcoal\Ui\Form\FormTrait;
use \Charcoal\Ui\FormGroup\FormGroupInterface;
use \Charcoal\Ui\Layout\LayoutAwareInterface;
use \Charcoal\Ui\Layout\LayoutAwareTrait;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminWidget;

/**
 *
 */
class FormWidget extends AdminWidget implements
    FormInterface,
    LayoutAwareInterface
{
    use FormTrait;
    use LayoutAwareTrait;

    /**
     * @var array $sidebars
     */
    protected $sidebars = [];

    /**
     * @param Container $container The DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Fill FormInterface dependencies
        $this->setFormGroupBuilder($container['form/group/builder']);

        // Fill LayoutAwareInterface dependencies
        $this->setLayoutBuilder($container['layout/builder']);
    }

    /**
     * @param array $data Optional. The form property data to set.
     * @return FormPropertyWidget
     */
    public function createFormProperty(array $data = null)
    {
        $p = new FormPropertyWidget([
            'logger'=>$this->logger
        ]);
        if ($data !== null) {
            $p->setData($data);
        }
        return $p;
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
     * @param string                  $sidebarIdent The sidebar identifier.
     * @param array|FormSidebarWidget $sidebar      The sidebar data or object.
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
        if (($sidebar instanceof FormSidebarWidget)) {
            $this->sidebars[$sidebarIdent] = $sidebar;
        } elseif (is_array($sidebar)) {
            $s = new FormSidebarWidget([
                'logger'=>$this->logger
            ]);
            $s->setForm($this);
            $s->setData($sidebar);
            $this->sidebars[$sidebarIdent] = $s;
        } else {
            throw new InvalidArgumentException(
                'Sidebar must be a FormSidebarWidget object or an array'
            );
        }
        return $this;
    }

    /**
     * @return FormSidebarWidget
     */
    public function sidebars()
    {
        $sidebars = $this->sidebars;
        if (!is_array($this->sidebars)) {
            yield null;
        } else {
            uasort($sidebars, ['self', 'sortSidebarsByPriority']);
            foreach ($sidebars as $sidebar) {
                $GLOBALS['widget_template'] = 'charcoal/admin/widget/form.sidebar';
                yield $sidebar->ident() => $sidebar;
            }
        }
    }

    /**
     * To be called with uasort().
     *
     * @param FormGroupInterface $a Item "a" to compare, for sorting.
     * @param FormGroupInterface $b Item "b" to compaer, for sorting.
     * @return integer Sorting value: -1, 0, or 1
     */
    protected static function sortSidebarsByPriority(FormGroupInterface $a, FormGroupInterface $b)
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
     * @return void This method is a generator.
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
     * @return string
     */
    public function defaultGroupType()
    {
        return 'charcoal/admin/widget/form-group-widget';
    }
}
