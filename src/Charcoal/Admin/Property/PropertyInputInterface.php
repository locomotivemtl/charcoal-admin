<?php

namespace Charcoal\Admin\Property;

// From `charcoal-base`
use \Charcoal\Property\PropertyInterface as PropertyInterface;

/**
 *
 */
interface PropertyInputInterface
{
    /**
     * @param array $data
     * @return Input Chainable
     */
    public function setData(array $data);

    /**
     * @param string $ident
     * @throws InvalidArgumentException if the ident is not a string
     * @return PropertyInputInterface Chainable
     */
    public function setIdent($ident);

    /**
     * @return string
     */
    public function ident();

    /**
     * @param boolean $readOnly
     * @throws InvalidArgumentException if the readOnly is not a string
     * @return PropertyInputInterface Chainable
     */
    public function setReadOnly($readOnly);

    /**
     * @return boolean
     */
    public function readOnly();

    /**
     * @param boolean $required
     * @throws InvalidArgumentException if the required is not a string
     * @return PropertyInputInterface Chainable
     */
    public function setRequired($required);

    /**
     * @return boolean
     */
    public function required();


    /**
     * @param boolean $disabled
     * @throws InvalidArgumentException if the disabled is not a string
     * @return PropertyInputInterface Chainable
     */
    public function setDisabled($disabled);

    /**
     * @return boolean
     */
    public function disabled();

    /**
     * @param string $inputId
     * @return Input Chainable
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
     * @param string $input_type
     */
    public function setInputType($input_type);

    public function inputType();

    /**
     * @param PropertyInterface $p
     */
    public function setProperty(PropertyInterface $p);

    /**
     * @return PropertyInterface
     */
    public function property();

    /**
     * @return PropertyInterface
     */
    public function p();
}
