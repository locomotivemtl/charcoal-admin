<?php

namespace Charcoal\Admin\Template;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Template as Template;

class Object extends Template
{
    /**
    * @var string $_obj_type
    */
    private $_obj_type;

    /**
    * @param array $data
    * @throws InvalidArgumentException
    * @return Object Chainable
    */
    public function set_data($data)
    {
        if(!is_array($data)) {
            throw new InvalidArgumentException('Daata must be an array');
        }
        parent::set_data($data);

        if (isset($data['obj_type'])) {
            $this->set_obj_type($data['obj_type']);
        }

        return $this;
    }

    public function set_obj_type($obj_type)
    {
        if (!is_string($obj_type)) {
            throw new \InvalidArgumentException('Obj type needs to be a string');
        }
        $this->_obj_type = $obj_type;
        return $this;
    }

    public function obj_type()
    {
        return str_replace(['.', '_'], '/', $this->_obj_type);
    }

    public function obj_proto()
    {

    }
}
