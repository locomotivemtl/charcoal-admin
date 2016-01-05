<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException;

use \Charcoal\Admin\Widget\FormWidget;
use \Charcoal\Admin\Widget\FormPropertyWidget;

use \Charcoal\Admin\Ui\ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait;

/**
* @todo This class needs to be renamed to "ObjectFormWidget" (object-form)
*/
class ObjectFormWidget extends FormWidget implements ObjectContainerInterface
{
    use ObjectContainerTrait;

    /**
    * @var string
    */
    protected $form_ident;

    /**
    * @return string
    */
    public function widget_type()
    {
        return 'charcoal/admin/widget/objectForm';
    }

    /**
    * @param array $data
    * @return ObjectForm Chainable
    */
    public function set_data(array $data)
    {
        // @TODO Remove once RequirementContainer is implemented
        // Needed this to be able to output {{obj_id}}
        $data = array_merge($_GET, $data);

        $this->set_obj_data($data);

        if (isset($data['form_ident']) && $data['form_ident'] !== null) {
            $this->set_form_ident($data['form_ident']);
        }

        $obj_data = $this->data_from_object();
        $data = array_merge_recursive($obj_data, $data);

        parent::set_data($data);

        return $this;
    }

     /**
    * @param string $url
    * @throws InvalidArgumentException if success is not a boolean
    * @return ActionInterface Chainable
    */
    public function set_next_url($url)
    {
        if (!is_string($url)) {
            throw new InvalidArgumentException(
                'URL needs to be a string'
            );
        }

        if (!$this->obj()) {
            $this->next_url = $url;
            return $this;
        }

        $this->next_url = $this->obj()->render( $url );
        return $this;
    }

    /**
    * Form action (target URL)
    *
    * @return string Relative URL
    */
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
            throw new InvalidArgumentException(
                'Form ident must be a string'
            );
        }
        $this->form_ident = $form_ident;
        return $this;
    }

    /**
    * @return string
    */
    public function form_ident()
    {
        return $this->form_ident;
    }

    public function data_from_object()
    {
        $obj = $this->obj();
        $metadata = $obj->metadata();
        $admin_metadata = isset($metadata['admin']) ? $metadata['admin'] : null;
        $form_ident = $this->form_ident();
        if (!$form_ident) {
            $form_ident = isset($admin_metadata['default_form']) ? $admin_metadata['default_form'] : '';
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
            $p = new FormPropertyWidget([
                'logger'=>$this->logger
            ]);
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
