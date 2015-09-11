<?php

namespace Charcoal\Admin\Widget;

use \Charcoal\Translation\TranslationString;

use \Charcoal\Admin\AdminWidget;
use \Charcoal\Template\TemplateViewController as TemplateViewController;

class FormSidebarWidget extends AdminWidget
{
    /**
    * In-memory copy of the parent form widget.
    * @var FormWidget $_form
    */
    private $_form;

    /**
    * @var string
    */
    private $_widget_type = 'properties';

    /**
    * @var Object $_actions
    */
    private $_actions;

    protected $_sidebar_sidebar_properties = [];
    protected $_priority;

    /**
    * @var TranslationString $_title
    */
    protected $_title;

    public function set_form(FormWidget $form)
    {
        $this->_form = $form;
        return $this;
    }

    public function form()
    {
        return $this->_form;
    }

    public function set_data(array $data)
    {
        parent::set_data($data);

        if (isset($data['properties'])) {
            $this->set_sidebar_properties($data['properties']);
        }
        if (isset($data['priority']) && $data['priority'] !== null) {
            $this->set_priority($data['priority']);
        }
        if (isset($data['title'])) {
            $this->set_title($data['title']);
        }
        if (isset($data['actions'])) {
            $this->set_actions($data['actions']);
        }
        return $this;
    }



    public function set_subtitle($subtitle)
    {
        if ($subtitle === null) {
            $this->_title = null;
        } else {
            $this->_title = new TranslationString($subtitle);
        }
    }

    public function set_sidebar_properties($properties)
    {
        $this->_sidebar_properties = $properties;
        return $this;
    }

    public function sidebar_properties()
    {
        return $this->_sidebar_properties;
    }

    public function form_properties()
    {
        $sidebar_properties = $this->sidebar_properties();
        $form_properties = $this->form()->form_properties($sidebar_properties);
        $ret = [];
        foreach ($form_properties as $property_ident => $property) {
            if (in_array($property_ident, $sidebar_properties)) {
                if (is_callable([$this->form(), 'obj'])) {
                    $val = $this->form()->obj()->p($property_ident)->val();
                    $property->set_property_val($val);
                }
                yield $property_ident => $property;
            }
        }
    }

    /**
    * Defined the form actions
    * @param Object $actions
    * @return FormGroupWidget Chainable
    */
    public function set_actions($actions)
    {
        if (!$actions) {
            return $this;
        }
        $this->_actions = [];

        foreach ($actions as $ident => $action) {
            if (!isset($action['url']) || !isset($action['label'])) {
                continue;
            }
            $label = new TranslationString($action['label']);
            $url = $action['url'];
            // Info = default
            // Possible: danger, info
            $btn = isset( $action['type'] ) ? $action['type'] : 'info';
            $this->_actions[] = [ 'label' => $label, 'url' => $url, 'btn' => $btn ];
        }

        return $this;
    }

    /**
    * Returns the actions as an ArrayIterator
    * [ ['label' => $label, 'url' => $url] ]
    * @see $this->set_actions()
    * @return object actions
    */
    public function actions()
    {
        return $this->_actions;
    }

    /**
    * @var integer $priority
    * @throws InvalidArgumentException
    * @return FormGroupWidget Chainable
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



    public function set_title($title)
    {
        if ($title === null) {
            $this->_title = null;
        } else {
            $this->_title = new TranslationString($title);
        }
        return $this;
    }

    public function title()
    {
        if ($this->_title === null) {
            $this->set_title('Actions');
        }
        return $this->_title;
    }
}
