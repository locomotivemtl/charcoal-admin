<?php

namespace Charcoal\Admin\Widget;

use InvalidArgumentException;

// From `charcoal-app`
use \Charcoal\App\Template\WidgetFactory;

use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Ui\FormInterface;
use \Charcoal\Admin\Ui\FormTrait;
use \Charcoal\Admin\Ui\FormGroupInterface;
use \Charcoal\Admin\Widget\LayoutWidget;

// Local namespace dependencies
use \Charcoal\Admin\Widget\FormGroupWidget;

class FormWidget extends AdminWidget implements FormInterface
{
    use FormTrait;

    /*public $label;
    public $subtitle;
    public $description;
    public $longDescription;
    public $notes;*/

    //public $type;

    /*public $showLabel;
    public $showDescription;
    public $show_notes;
    public $show_actions;*/

    protected $layout;

    protected $sidebars = [];

    /*public $use_captcha;
    public $use_token;
    public $lang;*/

    /*public $read_only;
    public $next_actions;*/

    private $widgetFactory;

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
            $group->set_data($data);
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
            $p->set_data($data);
        }
        return $p;
    }

    /**
    * @param LayoutWidget|array
    * @throws InvalidArgumentException
    * @return FormWidget Chainable
    */
    public function setLayout($layout)
    {
        if (($layout instanceof LayoutWidget)) {
            $this->layout = $layout;
        } else if (is_array($layout)) {
            $l = new LayoutWidget([
                'logger' => $this->logger
            ]);
//            $l->set_parent($this);
            $l->set_data($layout);
            $this->layout = $l;
        } else {
            throw new InvalidArgumentException(
                'Layout must be a LayoutWidget object or an array'
            );
        }
        return $this;
    }

    /**
    * @return LayoutWidget|null
    */
    public function layout()
    {
        return $this->layout;
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
    * To be called with uasort()
    *
    * @param FormGroupInterface $a
    * @param FormGroupInterface $b
    * @return integer Sorting value: -1, 0, or 1
    */
    static protected function sortSidebarsByPriority(FormGroupInterface $a, FormGroupInterface $b)
    {
        $a = $a->priority();
        $b = $b->priority();

        if ($a == $b) {
            return 1; // Should be 0?
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
