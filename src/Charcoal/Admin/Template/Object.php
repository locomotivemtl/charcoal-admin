<?php

namespace Charcoal\Admin\Template;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Template as Template;
use \Charcoal\Admin\Ui\ObjectContainerInterface as ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait as ObjectContainerTrait;

class Object extends Template implements ObjectContainerInterface
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
