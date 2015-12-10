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
    public $long_description;
    public $notes;*/

    //public $type;

    /*public $show_label;
    public $show_description;
    public $show_notes;
    public $show_actions;*/

    protected $layout;

    protected $sidebars = [];

    /*public $use_captcha;
    public $use_token;
    public $lang;*/

    /*public $read_only;
    public $next_actions;*/

    private $widget_factory;

    /**
    * @param array $data Optional
    */
    public function __construct(array $data = null)
    {
        //parent::__construct($data);

        if (is_array($data)) {
            $this->set_data($data);
        }
    }

    /**
    * @param array|null $data
    * @return FormGroupInterface
    */
    public function create_group(array $data = null)
    {
        $widget_type = (isset($data['widget_type']) ? $data['widget_type'] : 'charcoal/admin/widget/formGroup');
        $group = $this->widget_factory()->create($widget_type, [
            'logger' => $this->logger()
        ]);
        $group->set_form($this);
        if ($data) {
            $group->set_data($data);
        }
        return $group;

    }


    /**
    * @param array $data
    * @return FormPropertyInterface
    */
    public function create_form_property(array $data = null)
    {
        $p = new FormPropertyWidget();
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
    public function set_layout($layout)
    {
        if (($layout instanceof LayoutWidget)) {
            $this->layout = $layout;
        } else if (is_array($layout)) {
            $l = new LayoutWidget([
                'logger' => $this->logger()
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


    public function set_sidebars(array $sidebars)
    {
        $this->sidebars = [];
        foreach ($sidebars as $sidebar_ident => $sidebar) {
            $this->add_sidebar($sidebar_ident, $sidebar);
        }
        return $this;
    }
    /**
    * @param array|FormSidebarWidget $sidebar
    * @throws InvalidArgumentException
    * @return FormWidget Chainable
    */
    public function add_sidebar($sidebar_ident, $sidebar)
    {
        if (!is_string($sidebar_ident)) {
            throw new InvalidArgumentException(
                'Sidebar ident must be a string'
            );
        }
        if (($sidebar instanceof FormSidebarWidget)) {
            $this->sidebars[$sidebar_ident] = $sidebar;
        } else if (is_array($sidebar)) {
            $s = new FormSidebarWidget([
                'logger'=>$this->logger()
            ]);
            $s->set_form($this);
            $s->set_data($sidebar);
            $this->sidebars[$sidebar_ident] = $s;
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
            uasort($sidebars, ['self', 'sort_sidebars_by_priority']);
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
    static protected function sort_sidebars_by_priority(FormGroupInterface $a, FormGroupInterface $b)
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
    private function widget_factory()
    {
        if ($this->widget_factory === null) {
            $this->widget_factory = new WidgetFactory();
        }
        return $this->widget_factory;
    }
}
