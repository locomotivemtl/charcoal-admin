<?php

namespace Charcoal\Admin\Ui;

// Local namespace dependencies
use \Charcoal\Admin\Ui\FormInterface;

interface FormGroupInterface
{
    /**
     * All FormGroup-s should have a form associated
     * @param FormWidget
     * @return FormGroupInterface
     */
    public function setForm(FormInterface $form);

    /**
     * @return FormWidget
     */
    public function form();

    /**
     * This should really be in the WidgetInterface...
     * @return string widget type
     */
    public function widgetType();

    /**
     * @param string $title
     * @return FormGroupInterface Chainable
     */
    public function setTitle($data);

    /**
     * @return string
     */
    public function title();

    /**
     * @param string $subtitle
     * @return FormGroupInterface Chainable
     */
    public function setSubtitle($data);

    /**
     * @return string
     */
    public function subtitle();

    /**
     * @var integer $priority
     * @throws InvalidArgumentException
     * @return FormGroupWidget Chainable
     */
    public function setPriority($priority);

    /**
     * @return integer
     */
    public function priority();
}
