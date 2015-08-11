<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Widget\FormWidget as FormWidget;
use \Charcoal\Admin\Widget\FormPropertyWidget as FormPropertyWidget;

use \Charcoal\Admin\Ui\ObjectContainerInterface as ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait as ObjectContainerTrait;

/**
*
*/
class ObjectFormWidget extends FormWidget implements ObjectContainerInterface
{
    use ObjectContainerTrait;

    /**
    * @var string
    */
    protected $_form_ident;

    /**
    * @param array $data
    * @return ObjectForm Chainable
    */
    public function set_data(array $data)
    {
        $this->set_obj_data($data);

        if (isset($data['form_ident']) && $data['form_ident'] !== null) {
            $this->set_form_ident($data['form_ident']);
        }

        $obj_data = $this->data_from_object();
        $data = array_merge_recursive($obj_data, $data);

        parent::set_data($data);

        return $this;
    }

    public function action()
    {
        $action = parent::action();
        if (!$action) {
            $obj = $this->obj();
            $obj_id = $obj->id();
            if ($obj_id) {
                return 'action/object/update';
            } else {
                return 'action/object/save';
            }
        } else {
            return $action;
        }
    }

    /**
    * @param string $form_ident
    * @throws InvalidArgumentException
    * @return ObjectForm Chainable
    */
    public function set_form_ident($form_ident)
    {
        if (!is_string($form_ident)) {
            throw new InvalidArgumentException('Form ident must be a string');
        }
        $this->_form_ident = $form_ident;
        return $this;
    }

    /**
    * @return string
    */
    public function form_ident()
    {
        return $this->_form_ident;
    }

    public function data_from_object()
    {
        $obj = $this->obj();
        $metadata = $obj->metadata();
        $admin_metadata = isset($metadata['admin']) ? $metadata['admin'] : null;
        $form_ident = $this->form_ident();
        if (!$form_ident) {
            $form_ident = isset($admin_metadata['default_form']) ? $admin_metadata['default_form'] : '';
;
        }

        $obj_form_data = isset($admin_metadata['forms'][$form_ident]) ? $admin_metadata['forms'][$form_ident] : [];
        return $obj_form_data;
    }

    /**
    * FormProperty Generator
    *
    * @todo Merge with property_options
    */
    public function form_properties(array $group = null)
    {
        $obj = $this->obj();
        $props = $obj->metadata()->properties();

        // We need to sort form properties by form group property order if a group exists
        if (!empty($group)) {
            $props = array_merge(array_flip( $group ), $props);
        }

        foreach ($props as $property_ident => $property) {
            $p = new FormPropertyWidget($property);
            $p->set_property_ident($property_ident);
            $p->set_data($property);
            yield $property_ident => $p;
        }
    }

    /**
    * @return array
    */
    public function form_data()
    {
        $obj = $this->obj();
        $form_data = $obj->data();
        return $form_data;
    }
}
