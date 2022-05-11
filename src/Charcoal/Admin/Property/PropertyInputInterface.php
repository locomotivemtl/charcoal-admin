<?php

namespace Charcoal\Admin\Property;

// From 'charcoal-admin'
use Charcoal\Admin\Property\PropertyInterface as AdminPropertyInterface;

/**
 * Defines a model property form control Admin decorator.
 */
interface PropertyInputInterface extends AdminPropertyInterface
{
    /**
     * @param  string $inputType The input type.
     * @return self
     */
    public function setInputType($inputType);

    /**
     * @return string
     */
    public function inputType();

    /**
     * @param  string $inputId The input id.
     * @return self
     */
    public function setInputId($inputId);

    /**
     * @return string
     */
    public function inputId();

    /**
     * @return string
     */
    public function inputName();

    /**
     * @return string
     */
    public function inputVal();

    /**
     * @param  boolean $readOnly The readonly flag.
     * @return self
     */
    public function setReadOnly($readOnly);

    /**
     * @return boolean
     */
    public function readOnly();

    /**
     * @param  boolean $required The required flag.
     * @return self
     */
    public function setRequired($required);

    /**
     * @return boolean
     */
    public function required();

    /**
     * @param  boolean $disabled The disabled flag.
     * @return self
     */
    public function setDisabled($disabled);

    /**
     * @return boolean
     */
    public function disabled();
}
