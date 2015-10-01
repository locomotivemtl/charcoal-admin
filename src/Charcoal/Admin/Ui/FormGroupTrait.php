<?php

namespace Charcoal\Admin\Ui;

use \Charcoal\Admin\Widget\FormWidget;

use \InvalidArgumentException as InvalidArgumentException;

/**
* FormGroupTrait with basic methods to make
* sure when it's looped in the form widget,
* it has all the basic methods it requires
* to display as expected.
* @see FormGroupInterface
*/
trait FormGroupTrait
{
    /**
    * In-memory copy of the parent form widget.
    * @var FormWidget $_form
    */
    private $_form;

    /**
    * Should always be declared in every widget
    * using that trait.
    * @var string $_widget_type
    */
    private $_widget_type;

    /**
    * These might never be used in the widget
    * but this will be called by the Form widget
    *
    * @var string $_title
    * @var string $_subtitle
    */
    private $_title;
    private $_subtitle;

    /**
    * Order / sorting is done with the "priority".
    * @var integer $_priority
    */
    private $_priority = 0;

    /**
    * @return String || null
    */
    public function widget_type()
    {
        return '';
    }

    /**
    * @param String $title
    * @return $this (chainable)
    */
    public function set_title($title)
    {
        $this->_title = $title;
        return $this;
    }

    /**
    * @return String || null
    */
    public function title()
    {
        return $this->_title;
    }

    /**
    * @param String $title
    * @return $this (chainable)
    */
    public function set_subtitle($subtitle)
    {
        $this->_subtitle = $subtitle;
        return $this;
    }

    /**
    * @return String || null
    */
    public function subtitle()
    {
        return $this->_subtitle;
    }

    /**
    * @param Form $form
    * @return FormGroupWidget Chainable
    */
    public function set_form(FormWidget $form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
    * @return FormWidget or null
    */
    public function form()
    {
        return $this->_form;
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


}
