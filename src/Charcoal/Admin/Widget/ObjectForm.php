<?php

namespace Charcoal\Admin\Widget;

use \Charcoal\Admin\Widget\Form as Form;

use \Charcoal\Admin\Ui\ObjectContainerInterface as ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait as ObjectContainerTrait;

class ObjectForm extends Form implements ObjectContainerInterface
{
    use ObjectContainerTrait;

    public function set_data($data)
    {
        parent::set_data($data);
        $this->set_obj_data($data);
        return $this;
    }

    /**
    * FormProperty Generator
    *
    * @todo Merge with property_options
    */
    public function form_properties()
    {
       $obj = $this->obj();
       $props = $obj->metadata()->properties();
       foreach ($props as $property_ident => $property) {
            $p = new FormProperty($property);
            $p->set_data($property);
            yield $property_ident => $p;
       }
    }

    public function form_data()
    {
        $obj = $this->obj();
        return $obj->data();
    }
}
