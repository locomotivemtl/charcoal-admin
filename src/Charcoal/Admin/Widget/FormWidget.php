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

    protected $_layout;
    protected $_groups = [];
    protected $_next_url;
    protected $_sidebars = [];

    private $_action = '';
    private $_method = 'post';

    private $_form_data = [];
    private $_form_properties = [];



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
    * @var array $data
    * @return Form Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);

        if (isset($data['layout']) && $data['layout'] !== null) {
            $this->set_layout($data['layout']);
        }
        if (isset($data['groups']) && $data['groups'] !== null) {
            $this->set_groups($data['groups']);
        }
        if (isset($data['next_url']) && $data['next_url'] !== null) {
            $this->set_next_url($data['next_url']);
        }
        if (isset($data['sidebars']) && $data['sidebars'] !== null) {
            $this->set_sidebars($data['sidebars']);
        }
        if (isset($data['form_properties']) && $data['form_properties'] !== null) {
            $this->set_form_properties($data['form_properties']);
        }
        if (isset($data['form_data']) && $data['form_data'] !== null) {
            $this->set_form_data($data['form_data']);
        }
        if (isset($data['action']) && $data['action'] !== null) {
            $this->set_action($data['action']);
        }
        if (isset($data['method']) && $data['method'] !== null) {
            $this->set_method($data['method']);
        }

        return $this;
    }

    /**
    * @param LayoutWidget|array
    * @throws InvalidArgumentException
    * @return FormWidget Chainable
    */
    public function set_layout($layout)
    {
        if (($layout instanceof LayoutWidget)) {
            $this->_layout = $layout;
        } else if (is_array($layout)) {
            $l = new LayoutWidget();
//            $l->set_parent($this);
            $l->set_data($layout);
            $this->_layout = $l;
        } else {
            throw new InvalidArgumentException('Layout must be a LayoutWidget object or an array');
        }
        return $this;
    }

    /**
    * @return LayoutWidget|null
    */
    public function layout()
    {
        return $this->_layout;
    }

    public function set_groups(array $groups)
    {
        $this->_groups = [];
        foreach ($groups as $group_ident => $group) {
            $this->add_group($group_ident, $group);
        }
        return $this;
    }

    public function add_group($group_ident, $group)
    {
        if (!is_string($group_ident)) {
            throw new InvalidArgumentException('Group ident must be a string');
        }

        if (($group instanceof FormGroupInterface)) {
            $group->set_form($this);
            $this->_groups[$group_ident] = $group;
        } else if (is_array($group)) {
            $widget_type = isset($group['widget_type']) ? $group['widget_type'] : 'charcoal/admin/widget/formgroup';
            $g = WidgetFactory::instance()->create($widget_type);
//            $g = new FormGroupWidget();
            $g->set_form($this);
            $g->set_data($group);
            $this->_groups[$group_ident] = $g;
        } else {
            throw new InvalidArgumentException('Group must be a FormGroup object or an array');
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
            $this->_next_url = $url;
            return $this;
        }

        $this->_next_url = $this->obj()->render( $url );
        return $this;
    }

    /**
    * @return bool
    */
    public function next_url()
    {
        return $this->_next_url;
    }


    /**
    * Group generator
    */
    public function groups()
    {
        $groups = $this->_groups;
        if (!is_array($this->_groups)) {
            yield null;
        } else {
            uasort($groups, ['self', '_sort_groups_by_priority']);
            foreach ($groups as $group) {
                $GLOBALS['widget_template'] = $group->widget_type();
                yield $group->ident() => $group;
            }
        }
    }


    public function set_sidebars(array $sidebars)
    {
        $this->_sidebars = [];
        foreach ($sidebars as $sidebar_ident => $sidebar) {
            //var_dump($sidebar_ident);
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
            throw new InvalidArgumentException('Sidebar ident must be a string');
        }
        if (($sidebar instanceof FormSidebarWidget)) {
            $this->_sidebars[$sidebar_ident] = $sidebar;
        } else if (is_array($sidebar)) {
            $s = new FormSidebarWidget();
            $s->set_form($this);
            $s->set_data($sidebar);
            $this->_sidebars[$sidebar_ident] = $s;
        } else {
            throw new InvalidArgumentException('Sidebar must be a FormSidebarWidget object or an array');
        }
        return $this;
    }

    /**
    * @return FormSidebarWidget
    */
    public function sidebars()
    {
        $sidebars = $this->_sidebars;
        if (!is_array($this->_sidebars)) {
            yield null;
        } else {
            uasort($sidebars, ['self', '_sort_sidebars_by_priority']);
            foreach ($sidebars as $sidebar) {
                /*if ($sidebar->widget_type() != '') {
                    $GLOBALS['widget_template'] = $sidebar->widget_type();
                } else {
                    $GLOBALS['widget_template'] = 'charcoal/admin/widget/form.sidebar';
                }*/
                $GLOBALS['widget_template'] = 'charcoal/admin/widget/form.sidebar';
                //var_dump($GLOBALS['widget_template']);
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
            throw new InvalidArgumentException('Action must be a string');
        }
        $this->_action = $action;
        return $this;
    }

    public function action()
    {
        return $this->_action;
    }

    public function set_method($method)
    {
        $method = strtolower($method);
        if (!in_array($method, ['post', 'get'])) {
            throw new InvalidArgumentException('Method must be "post" or "get"');
        }
        $this->_method = $method;
        return $this;
    }

    public function method()
    {
        return $this->_method;
    }

    public function set_form_data($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Form data must be an array');
        }
        $this->_form_data = $data;
        return $this;
    }

    public function add_form_data($key, $val)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('Key must be a string');
        }
        $this->_form_data[$key] = $val;
        return $this;
    }

    public function form_data()
    {
        return $this->_form_data;
    }

    public function set_form_properties($properties)
    {
        if (!is_array($properties)) {
            throw new InvalidArgumentException('Properties must be an array');
        }
        $this->_form_properties = [];
        foreach ($properties as $property_ident => $property) {
            $this->add_form_property($property_ident, $property);
        }
        return $this;
    }

    public function add_form_property($property_ident, $property)
    {
        if (!is_string($property_ident)) {
            throw new InvalidArgumentException('Property ident must be a string');
        }

        if (($property instanceof FormPropertyWidget)) {
            $this->_form_properties[$property_ident] = $property;
        } else if (is_array($property)) {
            $p = new FormPropertyWidget($property);
            $p->set_property_ident($property_ident);
            $p->set_data($property);
//            $p->set_form($this);
            $this->_form_properties[$property_ident] = $p;
        } else {
            throw new InvalidArgumentException('Property must be a FormProperty object or an array');
        }

        return $this;
    }

    public function form_properties(array $group = null)
    {
        unset($group);

        foreach ($this->_form_properties as $prop) {
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
    */
    static protected function _sort_groups_by_priority($a, $b)
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
    */
    static protected function _sort_sidebars_by_priority($a, $b)
    {
        $a = $a->priority();
        $b = $b->priority();

        if ($a == $b) {
            return 1; // Should be 0?
        }

        return ($a < $b) ? (-1) : 1;
    }
}
