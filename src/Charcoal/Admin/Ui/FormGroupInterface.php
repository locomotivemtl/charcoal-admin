<?php

namespace Charcoal\Admin\Ui;
use \Charcoal\Admin\Widget\FormWidget;

interface FormGroupInterface
{
    /**
    * This should really be in the WidgetInterface...
    * @return string widget type
    */
    public function widget_type();

    /**
    * @param string $title
    * @return FormGroupInterface Chainable
    */
    public function set_title($data);

    /**
    * @return String
    */
    public function title();

    /**
    * @param string $subtitle
    * @return FormGroupInterface Chainable
    */
    public function set_subtitle($data);

    /**
    * @return String
    */
    public function subtitle();


    /**
    * All FormGroup-s should have a form associated
    * @param FormWidget
    * @return FormGroupInterface
    */
    public function set_form(FormWidget $form);

    /**
    * @return FormWidget
    */
    public function form();


    /**
    * @var integer $priority
    * @throws InvalidArgumentException
    * @return FormGroupWidget Chainable
    */
    public function set_priority($priority);

    /**
    * @return Integer
    */
    public function priority();

}
