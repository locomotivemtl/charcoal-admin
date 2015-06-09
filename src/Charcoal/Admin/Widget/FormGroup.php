<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Widget as Widget;
use \Charcoal\Admin\Widget\Form as Form;

class FormGroup extends Widget
{
    private $_form;

    /**
    * @var integer $_priority
    */
    private $_priority = 0;

    /**
    * @var string
    */
    private $_widget_type = 'properties';

    /**
    * @var array $_group_properties
    */
    private $_group_properties = [];

    /**
    * @var boolean
    */
    private $_show_title = true;
    /**
    * @var boolean
    */
    private $_show_description = true;
    /**
    * @var boolean
    */
    private $_show_header = true;
    /**
    * @var boolean
    */
    private $_show_footer = true;
    /**
    * @var boolean
    */
    private $_show_notes = true;

    private $_title;
    private $_description;
    private $_notes;

    /**
    * @var string
    * @throws InvalidArgumentException
    * @return FormGroup Chainable
    */
    public function set_data($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }

        parent::set_data($data);

        if (isset($data['widget_type']) && $data['widget_type'] !== null) {
            $this->set_widget_type($data['widget_type']);
        }
        if (isset($data['properties']) && $data['properties'] !== null) {
            $this->set_group_properties($data['properties']);
        }
        if (isset($data['priority']) && $data['priority'] !== null) {
            $this->set_priority($data['priority']);
        }
        if (isset($data['show_title']) && $data['show_title'] !== null) {
            $this->set_show_title($data['show_title']);
        }
        if (isset($data['show_description']) && $data['show_description'] !== null) {
            $this->set_show_description($data['show_description']);
        }
        if (isset($data['show_header']) && $data['show_header'] !== null) {
            $this->set_show_header($data['show_header']);
        }
        if (isset($data['show_footer']) && $data['show_footer'] !== null) {
            $this->set_show_footer($data['show_footer']);
        }
        if (isset($data['show_notes']) && $data['show_notes'] !== null) {
            $this->set_show_notes($data['show_notes']);
        }
        if (isset($data['title']) && $data['title'] !== null) {
            $this->set_title($data['title']);
        }
        if (isset($data['description']) && $data['description'] !== null) {
            $this->set_description($data['description']);
        }
        if (isset($data['notes']) && $data['notes'] !== null) {
            $this->set_notes($data['notes']);
        }

        return $this;
    }

    /**
    * @param Form $form
    * @return FormGroup Chainable
    */
    public function set_form(Form $form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
    * @return Form or null
    */
    public function form()
    {
        return $this->_form;
    }

    public function set_widget_type($widget_type)
    {
        if (!is_string($widget_type)) {
            throw new InvalidArgumentException('Widget type must be a string');
        }
        $this->_widget_type = $widget_type;
        return $this;
    }

    public function widget_type()
    {
        return $this->_widget_type;
    }

    public function set_group_properties($properties)
    {
        $this->_group_properties = $properties;
        return $this;
    }

    public function group_properties()
    {
        return $this->_group_properties;

    }

    public function form_properties()
    {
        $group_properties = $this->group_properties();
        $form_properties = $this->form()->form_properties();

        $ret = [];
        foreach ($form_properties as $property_ident => $property) {
            if (in_array($property_ident, $group_properties)) {
                //var_dump($property);
                if (is_callable([$this->form(), 'obj'])) {
                    $val = $this->form()->obj()->p($property_ident)->val();
                    $property->set_property_val($val);
                }
                yield $property_ident => $property;
            }
        }
    }

    /**
    * @var integer $priority
    * @throws InvalidArgumentException
    * @return FormGroup Chainable
    */
    public function set_priority($priority)
    {
        if (!is_int($priority)) {
            throw new InvalidArgumentException('Priority must be an integer');
        }
        $priority = (int)$priority;
        $this->_priority = $priority;
        return $this;
    }

    /**
    * @return integer
    */
    public function priority()
    {
        return $this->_priority;
    }

    /**
    * @param boolean $show
    * @throws InvalidArgumentException
    * @return FormGroup Chainable
    */
    public function set_show_title($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show must be a boolean');
        }
        $this->_show_title = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_title()
    {
        return true;
    }

    /**
    * @param boolean $show
    * @throws InvalidArgumentException
    * @return FormGroup Chainable
    */
    public function set_show_description($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show must be a boolean');
        }
        $this->_show_description = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_description()
    {
        return true;
    }

    /**
    * @param boolean $show
    * @throws InvalidArgumentException
    * @return FormGroup Chainable
    */
    public function set_show_header($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show must be a boolean');
        }
        $this->_show_header = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_header()
    {
        return true;
    }

    /**
    * @param boolean $show
    * @throws InvalidArgumentException
    * @return FormGroup Chainable
    */
    public function set_show_footer($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show must be a boolean');
        }
        $this->_show_fooger = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_footer()
    {
        if ($this->_show_footer === false) {
            return false;
        } else {
            return $this->show_notes();
        }
    }

    /**
    * @param boolean $show
    * @throws InvalidArgumentException
    * @return FormGroup Chainable
    */
    public function set_show_notes($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show must be a boolean');
        }
        $this->_show_notes = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_notes()
    {
        if ($this->_show_notes === false) {
            return false;
        } else {
            $notes = $this->notes();
            return !!$notes;
        }
    }

    public function set_title($title)
    {
        $this->_title = $title;
        return $this;
    }

    public function title()
    {
        return 'Group Label';
    }

    public function set_description($description)
    {
        $this->_description = $description;
        return $this;
    }

    public function description()
    {
        return 'Group Description';
    }

    public function set_notes($notes)
    {
        $this->_notes = $notes;
        return $this;
    }

    public function notes()
    {
        return 'Group Notes';
    }
}
