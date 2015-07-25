<?php

namespace Charcoal\Admin\Template;

// Dependencies from `PHP`
use \InvalidArgumentException as InvalidArgumentException;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Ui\ObjectContainerInterface as ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait as ObjectContainerTrait;

// Local parent namespace dependencies
use \Charcoal\Admin\AdminTemplate as AdminTemplate;


class ObjectTemplate extends AdminTemplate implements ObjectContainerInterface
{
    use ObjectContainerTrait;

    /**
    * @param array $data
    * @return Object Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);

        $this->set_obj_data($data);

        return $this;
    }

}
