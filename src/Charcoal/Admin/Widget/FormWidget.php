<?php

namespace Charcoal\Admin\Widget;

use InvalidArgumentException;

use \Pimple\Container;

// From `charcoal-app`
use \Charcoal\App\Template\WidgetFactory;

/// From `charcoal-ui`
use \Charcoal\Ui\Form\FormInterface;
//use \Charcoal\Ui\Form\FormTrait;
use \Charcoal\Ui\Layout\LayoutAwareInterface;
use \Charcoal\Ui\Layout\LayoutAwareTrait;

use \Charcoal\Admin\AdminWidget;
//use \Charcoal\Admin\Ui\FormInterface;
use \Charcoal\Admin\Ui\FormTrait;
use \Charcoal\Admin\Ui\FormGroupInterface;
use \Charcoal\Admin\Widget\LayoutWidget;

// Local namespace dependencies
use \Charcoal\Admin\Widget\FormGroupWidget;

/**
 *
 */
class FormWidget extends AdminWidget implements
    FormInterface,
    LayoutAwareInterface
{
    use FormTrait;
    use LayoutAwareTrait;

    protected $sidebars = [];

    /**
    * @var WidgetFactory $widgetFactory
    */
    private $widgetFactory;

    public function setDependencies(Container $container)
    {
        //$this->setFormGroupBuilder($container['form/group/builder']);
        $this->setLayoutBuilder($container['layout/builder']);
    }

    /**
     * @param array|null $data
     * @return FormGroupInterface
     */
    public function createGroup(array $data = null)
    {
        $widget_type = (isset($data['widget_type']) ? $data['widget_type'] : 'charcoal/admin/widget/formGroup');
        $group = $this->widgetFactory()->create($widget_type, [
            'logger' => $this->logger
        ]);
        $group->setForm($this);
        if ($data) {
            $group->setData($data);
        }
        return $group;

    }

    /**
     * @param array $data
     * @return FormPropertyInterface
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
     *
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
     * @param array|FormSidebarWidget $sidebar
     * @throws InvalidArgumentException
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
        } else if (is_array($sidebar)) {
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
                /*if ($sidebar->widget_type() != '') {
                    $GLOBALS['widget_template'] = $sidebar->widget_type();
                } else {
                    $GLOBALS['widget_template'] = 'charcoal/admin/widget/form.sidebar';
                }*/
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

        if ($a == $b) {
            return 1;
// Should be 0?
        }

        return ($a < $b) ? (-1) : 1;
    }

    /**
     * @return WidgetFactory
     */
    private function widgetFactory()
    {
        if ($this->widgetFactory === null) {
            $this->widgetFactory = new WidgetFactory();
        }
        return $this->widgetFactory;
    }
}
