<?php

namespace Charcoal\Admin\Widget;

use InvalidArgumentException;

use \Charcoal\Widget\WidgetFactory;
use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Ui\FormGroupInterface;
use \Charcoal\Admin\Widget\LayoutWidget;

// Local namespace dependencies
use \Charcoal\Admin\Widget\FormGroupWidget;

class FormWidget extends AdminWidget
{

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
    protected $groups = [];
    protected $next_url;
    protected $sidebars = [];

    private $action = '';
    private $method = 'post';

    private $form_data = [];
    private $form_properties = [];

    /*public $use_captcha;
    public $use_token;
    public $lang;*/

    /*public $read_only;
    public $next_actions;*/

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

    public function set_groups(array $groups)
    {
        $this->groups = [];
        foreach ($groups as $group_ident => $group) {
            $this->add_group($group_ident, $group);
        }
        return $this;
    }

    public function add_group($group_ident, $group)
    {
        if (!is_string($group_ident)) {
            throw new InvalidArgumentException(
                'Group ident must be a string'
            );
        }

        if (($group instanceof FormGroupInterface)) {
            $group->set_form($this);
            $this->groups[$group_ident] = $group;
        } else if (is_array($group)) {
            $widget_type = isset($group['widget_type']) ? $group['widget_type'] : 'charcoal/admin/widget/formgroup';
            $g = WidgetFactory::instance()->create($widget_type);
//            $g = new FormGroupWidget();
            $g->set_form($this);
            $g->set_data($group);
            $this->groups[$group_ident] = $g;
        } else {
            throw new InvalidArgumentException(
                'Group must be a FormGroup object or an array'
            );
        }

        return $this;
    }

    /**
    * @param string $url
    * @throws InvalidArgumentException if success is not a boolean
    * @return ActionInterface Chainable
    */
    public function set_next_url($url)
    {
        if (!is_string($url)) {
            throw new InvalidArgumentException(
                'URL needs to be a string'
            );
        }

        if (!$this->obj()) {
            $this->next_url = $url;
            return $this;
        }

        $this->next_url = $this->obj()->render( $url );
        return $this;
    }

    /**
    * @return bool
    */
    public function next_url()
    {
        return $this->next_url;
    }


    /**
    * Group generator
    */
    public function groups()
    {
        $groups = $this->groups;
        if (!is_array($this->groups)) {
            yield null;
        } else {
            uasort($groups, ['self', 'sort_groups_by_priority']);
            foreach ($groups as $group) {
                $GLOBALS['widget_template'] = $group->widget_type();
                yield $group->ident() => $group;
            }
        }
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
            $s = new FormSidebarWidget();
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
    * @param string $action
    * @throws InvalidArgumentException
    * @return FormWidget Chainable
    */
    public function set_action($action)
    {
        if (!is_string($action)) {
            throw new InvalidArgumentException(
                'Action must be a string'
            );
        }
        $this->action = $action;
        return $this;
    }

    /**
    * @return string
    */
    public function action()
    {
        return $this->action;
    }

    /**
    * @param string $method Either "post" or "get"
    * @throws InvalidArgumentException
    * @return FormWidget Chainable
    */
    public function set_method($method)
    {
        $method = strtolower($method);
        if (!in_array($method, ['post', 'get'])) {
            throw new InvalidArgumentException(
                'Method must be "post" or "get"'
            );
        }
        $this->method = $method;
        return $this;
    }

    /**
    * @return string Either "post" or "get"
    */
    public function method()
    {
        return $this->method;
    }

    /**
    * @param array $data
    * @return FormWidget Chainable
    */
    public function set_form_data(array $data)
    {
        $this->form_data = $data;
        return $this;
    }

    /**
    * @param string $key
    * @param mixed $val
    * @throws InvalidArgumentException
    * @return FormWidget Chainable
    */
    public function add_form_data($key, $val)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Key must be a string'
            );
        }
        $this->form_data[$key] = $val;
        return $this;
    }

    /**
    * @return array
    */
    public function form_data()
    {
        return $this->form_data;
    }

    public function set_form_properties(array $properties)
    {
        $this->form_properties = [];
        foreach ($properties as $property_ident => $property) {
            $this->add_form_property($property_ident, $property);
        }
        return $this;
    }

    public function add_form_property($property_ident, $property)
    {
        if (!is_string($property_ident)) {
            throw new InvalidArgumentException(
                'Property ident must be a string'
            );
        }

        if (($property instanceof FormPropertyWidget)) {
            $this->form_properties[$property_ident] = $property;
        } else if (is_array($property)) {
            $p = new FormPropertyWidget($property);
            $p->set_property_ident($property_ident);
            $p->set_data($property);
            $this->form_properties[$property_ident] = $p;
        } else {
            throw new InvalidArgumentException(
                'Property must be a FormProperty object or an array'
            );
        }

        return $this;
    }

    public function form_properties(array $group = null)
    {
        unset($group);

        foreach ($this->form_properties as $prop) {
            if ($prop->active() === false) {
                continue;
            }
            //var_dump($prop->property_ident());
            $GLOBALS['widget_template'] = $prop->input_type();
            yield $prop->property_ident() => $prop;
        }
    }

    /**
    * To be called with uasort()
    *
    * @param FormGroupInterface $a
    * @param FormGroupInterface $b
    * @return integer Sorting value: -1, 0, or 1
    */
    static protected function sort_groups_by_priority(FormGroupInterface $a, FormGroupInterface $b)
    {
        $a = $a->priority();
        $b = $b->priority();

        if ($a == $b) {
            return 1; // Should be 0?
        }

        return ($a < $b) ? (-1) : 1;
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
}
