<?php

namespace Charcoal\Admin\Action\Widget\Table;

use \Exception as Exception;

// From `charcoal-core`
use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Model\ModelFactory as ModelFactory;

use \Charcoal\Admin\Action as Action;
use \Charcoal\Admin\Widget\ObjectForm as ObjectForm;
use \Charcoal\Admin\Widget\FormProperty as FormProperty;

class Inline extends Action
{
    protected $_inline_properties;

    /**
    * Run the inline action
    * Set the inline properties from request's parameter
    */
    public function run()
    {
        $obj_type = Charcoal::app()->request->post('obj_type');
        $obj_id = Charcoal::app()->request->post('obj_id');
        //var_dump($obj_type);
        //var_dump($obj_id);

        if (!$obj_type || !$obj_id) {
            $this->set_success(false);
            $this->output(404);
        }
        
        try {
            $obj = ModelFactory::instance()->get($obj_type);
            $obj->load($obj_id);
            if (!$obj->id()) {
                $this->set_success(false);
                $this->output(404);
            }

            $obj_form = new ObjectForm();
            $obj_form->set_obj_type($obj_type);
            $obj_form->set_obj_id($obj_id);
            $form_properties = $obj_form->form_properties();
            foreach ($form_properties as $property_ident => $property) {
                if (!($property instanceof FormProperty)) {
                    continue;
                }
                $p = $obj->p($property_ident);
                $property->set_property_val($p->val());
                $property->set_prop($p);
                $input_type = $property->input_type();
                $this->_inline_properties[$property_ident] = $property->render_template($input_type);
            }
            $this->set_success(true);
            $this->output();

        } catch (Exception $e) {
            $this->set_success(false);
            $this->output(404);
        }
    }

    public function response()
    {
        $success = $this->success();

        $response = [
            'success'=>$this->success(),
            'inline_properties'=>$this->_inline_properties
        ];
        return $response;
    }
}
