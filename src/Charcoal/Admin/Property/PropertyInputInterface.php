<?php

namespace Charcoal\Admin\Property;

interface PropertyInputInterface
{
    /**
    * @param array $data
    * @return Input Chainable
    */
    public function set_data(array $data);

    /**
    * @param string $ident
    * @throws InvalidArgumentException if the ident is not a string
    * @return PropertyInputInterface Chainable
    */
    public function set_ident($ident);

    /**
    * @return string
    */
    public function ident();

    /**
    * @param boolean $read_only
    * @throws InvalidArgumentException if the read_only is not a string
    * @return PropertyInputInterface Chainable
    */
    public function set_read_only($read_only);

    /**
    * @return boolean
    */
    public function read_only();

    /**
    * @param boolean $required
    * @throws InvalidArgumentException if the required is not a string
    * @return PropertyInputInterface Chainable
    */
    public function set_required($required);

    /**
    * @return boolean
    */
    public function required();


    /**
    * @param boolean $disabled
    * @throws InvalidArgumentException if the disabled is not a string
    * @return PropertyInputInterface Chainable
    */
    public function set_disabled($disabled);

    /**
    * @return boolean
    */
    public function disabled();

    /**
    * @param string $input_id
    * @return Input Chainable
    */
    public function set_input_id($input_id);

    /**
    * @return string
    */
    public function input_id();

    /**
    * @return string
    */
    public function input_name();

    /**
    * @return string
    */
    public function input_val();

    /**
    * @param string $input_type
    */
    public function set_input_type($input_type);

    public function input_type();

    /**
    * @param PropertyInterface $p
    */
    public function set_property($p);

    /**
    * @return PropertyInterface
    */
    public function property();

    /**
    * @return PropertyInterface
    */
    public function p();
}
