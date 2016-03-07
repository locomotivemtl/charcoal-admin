<?php

namespace Charcoal\Admin\Ui;

// Dependencies from `PHP`
use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Ui\Form\FormInterface;

/**
* FormGroupTrait with basic methods to make
* sure when it's looped in the form widget,
* it has all the basic methods it requires
* to display as expected.
*
* @see FormGroupInterface
*/
trait FormGroupTrait
{
    /**
     * In-memory copy of the parent form widget.
     * @var FormInterface $form
     */
    private $form;

    /**
     * Should always be declared in every widget
     * using that trait.
     * @var string $widgetType
     */
    private $widgetType;

    /**
     * These might never be used in the widget
     * but this will be called by the Form widget
     *
     * @var string $title
     */
    private $title;

    /**
     * @var string $subtitle
     */
    private $subtitle;

    /**
     * Order / sorting is done with the "priority".
     * @var integer $priority
     */
    private $priority = 0;


    /**
     * @param string $type
     * @throws InvalidArgumentException
     * @return AdminWidget Chainable
     */
    public function setWidgetType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Widget type must be a string'
            );
        }
        $this->widgetType = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function widgetType()
    {
        return $this->widgetType;
    }

    /**
     * @param string $title
     * @return $this (chainable)
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return String || null
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this (chainable)
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
        return $this;
    }

    /**
     * @return String || null
     */
    public function subtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param Form $form
     * @return FormGroupWidget Chainable
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @return FormInterface|null Associated form.
     */
    public function form()
    {
        return $this->form;
    }

    /**
     * @var integer $priority
     * @throws InvalidArgumentException
     * @return FormGroupInterface Chainable
     */
    public function setPriority($priority)
    {
        if (!is_int($priority)) {
            throw new InvalidArgumentException(
                'Priority must be an integer'
            );
        }
        $priority = (int)$priority;
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return integer
     */
    public function priority()
    {
        return $this->priority;
    }
}
