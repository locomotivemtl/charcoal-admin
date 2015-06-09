<?php

namespace Charcoal\Admin\Action\Object;

use \Exception as Exception;

// From `charcoal-core`
use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Model\ModelFactory as ModelFactory;

use \Charcoal\Admin\Action as Action;

class Delete extends Action
{
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
            $res = $obj->delete();
            if ($res) {
                $this->log_object_delete();
                $this->set_success(true);
                $this->output();
            }
        } catch (Exception $e) {
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

    public function log_object_delete()
    {
        // @todo
    }
}
