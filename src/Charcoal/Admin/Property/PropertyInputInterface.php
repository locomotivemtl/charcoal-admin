<?php

namespace Charcoal\Admin\Property;

use Traversable;

// Dependency from 'charcoal-base'
use Charcoal\Property\PropertyInterface;

/**
 *
 */
interface PropertyInputInterface
{
    /**
     * @param array|Traversable $data The object (input) data.
     * @return Input Chainable
     */
    public function setData(array $data);

    /**
     * @param string $ident The input identifier.
     * @return PropertyInputInterface Chainable
     */
    public function setIdent($ident);

    /**
     * @return string
     */
    public function ident();

    /**
     * @param boolean $readOnly The readonly flag.
     * @return PropertyInputInterface Chainable
     */
    public function setReadOnly($readOnly);

    /**
     * @return boolean
     */
    public function readOnly();

    /**
     * @param boolean $required The required flag.
     * @return PropertyInputInterface Chainable
     */
    public function setRequired($required);

    /**
     * @return boolean
     */
    public function required();


    /**
     * @param boolean $disabled The disabled flag.
     * @return PropertyInputInterface Chainable
     */
    public function setDisabled($disabled);

    /**
     * @return boolean
     */
    public function disabled();

    /**
     * @param string $inputId The input id.
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
     * @param string $inputType The input type.
     * @return PropertyInputInterface Chainable
     */
    public function setInputType($inputType);

    /**
     * @return string
     */
    public function inputType();

    /**
     * @param PropertyInterface $p The property.
     * @return PropertyInputInterface Chainable
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
