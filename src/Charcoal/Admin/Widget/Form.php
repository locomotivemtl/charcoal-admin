<?php

namespace Charcoal\Admin\Widget;

use InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Widget as Widget;

class Form extends Widget
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

    private $_layout;
    private $_groups;

    private $_action = '';
    private $_method = 'post';

    private $_form_data = [];
    private $_form_properties = [];

    /*public $use_captcha;
    public $use_token;
    public $lang;*/

    /*public $read_only;
    public $next_actions;*/

    public function __construct($data = null)
    {
        //parent::__construct($data);

        if ($data !== null) {
            $this->set_data($data);
        }
    }

    /**
    * @var array $data
    * @throws InvalidArgumentException
    * @return Form (Chainable)
    */
    public function set_data($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }

        parent::set_data($data);

        if (isset($data['layout']) && $data['layout'] !== null) {
            $this->set_layout($data['layout']);
        }
        if (isset($data['groups']) && $data['groups'] !== null) {
            $this->set_groups($data['groups']);
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

    public function set_layout($layout)
    {
        if (($layout instanceof Layout)) {
            $this->_layout = $layout;
        } else if (is_array($layout)) {
            $layout = new Layout();
            $layout->set_data($layout);
            $this->_layout = $layout;
        } else {
            throw new InvalidArgumentException('Layout must be a Layout object or an array');
        }
    }

    public function layout()
    {
        return $this->_layout;
    }

    public function set_groups($groups)
    {
        if (!is_array($groups)) {
            throw new InvalidArgumentException('Groups need to be an array');
        }
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
        if (($group instanceof FormGroup)) {
            $group->set_form($this);
            $this->_groups[$group_ident] = $group;
        } else if (is_array($group)) {
            $g = new FormGroup();
            $g->set_form($this);
            $g->set_data($group);
            $this->_groups[$group_ident] = $g;
        } else {
            throw new InvalidArgumentException('Group must be a FormGroup object or an array');
        }

        return $this;
    }

    public function groups()
    {
        $groups = $this->_groups;
        if (!is_array($this->_groups)) {
            yield null;
        } else {
            uasort($groups, ['self', '_sort_groups_by_priority']);
            foreach ($groups as $group) {
                /*if ($group->widget_type() != '') {
                    $GLOBALS['widget_template'] = $group->widget_type();
                } else {
                    $GLOBALS['widget_template'] = 'charcoal/admin/widget/form.group';
                }*/
                $GLOBALS['widget_template'] = 'charcoal/admin/widget/form.group';
                //var_dump($GLOBALS['widget_template']);
                yield $group->ident() => $group;
            }
        }
    }

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

        if (($property instanceof FormProperty)) {
            $this->_form_properties[$property_ident] = $property;
        } else if (is_array($property)) {
            $p = new FormProperty($property);
            $p->set_property_ident($property_ident);
            $p->set_data($property);
            //$p->set_form($this);
            $this->_form_properties[$property_ident] = $p;
        } else {
            throw new InvalidArgumentException('Property must be a FormProperty object or an array');
        }

        return $this;
    }

    public function form_properties()
    {
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
}
