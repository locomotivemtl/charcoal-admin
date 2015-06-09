<?php

namespace Charcoal\Admin\Action\Widget\Table;

use \Exception as Exception;

// From `charcoal-core`
use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Model\ModelFactory as ModelFactory;

use \Charcoal\Admin\Action as Action;
use \Charcoal\Admin\Widget\ObjectForm as ObjectForm;
use \Charcoal\Admin\Widget\FormProperty as FormProperty;

class Inlinemulti extends Action
{
    protected $_objects;

    /**
    * Run the inline action
    * Set the inline properties from request's parameter
    */
    public function run()
    {
        $obj_type = Charcoal::app()->request->post('obj_type');
        $obj_ids = Charcoal::app()->request->post('obj_ids');
        //var_dump($obj_type);
        //var_dump($obj_id);

        if (!$obj_type || !$obj_ids) {
            $this->set_success(false);
            $this->output(404);
        }
        
        try {
            $this->_objects = [];
            foreach ($obj_ids as $obj_id) {
                $obj = ModelFactory::instance()->get($obj_type);
                $obj->load($obj_id);
                if (!$obj->id()) {
                    continue;
                }

                $o = [];
                $o['id'] = $obj->id();

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
                    $o['inline_properties'][$property_ident] = $property->render_template($input_type);
                }
                $this->_objects[] = $o;
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
            'success' => $this->success(),
            'objects' => $this->_objects
        ];
        return $response;
    }
}
