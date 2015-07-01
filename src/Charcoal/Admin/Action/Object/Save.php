<?php

namespace Charcoal\Admin\Action\Object;

use \Exception as Exception;

// From `charcoal-core`
use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Model\ModelFactory as ModelFactory;

use \Charcoal\Admin\Action as Action;

class Save extends Action
{
    public function run()
    {

        $obj_type = Charcoal::app()->request->post('obj_type');
        $obj_data = Charcoal::app()->request->post();
        if (!$obj_type) {
            $this->set_success(false);
            $this->output(404);
        }

        try {
            $obj = ModelFactory::instance()->get($obj_type);

            $obj->set_flat_data($obj_data);
            $validation = $obj->validate();
            // @todo Handle validation

            $ret = $obj->save();
            if ($ret) {
                $this->log_object_save();
                $this->set_success(true);
                $this->output();
            } else {
                $this->set_success(false);
                $this->output(404);
            }
        } catch (Exception $e) {
            var_dump($e);
            $this->set_success(false);
            $this->output(404);
        }

    }

    public function response()
    {
        $success = $this->success();

        $response = [
            'success'=>$this->success()
        ];
        return $response;
    }

    public function log_object_save()
    {
        // @todo
    }
}
