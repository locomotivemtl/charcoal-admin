<?php

namespace Charcoal\Admin\Action\Object;

// Dependencies from `PHP`
use \Exception as Exception;

// Module `charcoal-core` dependencies
use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Model\ModelFactory as ModelFactory;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminAction as AdminAction;
use \Charcoal\Admin\Ui\ObjectContainerInterface as ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait as ObjectContainerTrait;

/**
* Admin Update Action: Save an object in its Storage.
*
* ## Required Parameters
* - `obj_type` _string_ The object type, as an identifier for a `ModelInterface`.
* - `obj_id` _mixed_ The object ID to load and update
*
* ## Response
* - `success` _boolean_ True if the object was properly saved, false in case of any error.
*
* ## HTTP Codes
* - `200` in case of a successful login
* - `404` if any error occurs
*
* Ident: `charcoal/admin/action/object/update`
*/
class UpdateAction extends AdminAction implements ObjectContainerInterface
{
    use ObjectContainerTrait;

    protected $_update_data = [];

    /**
    * @param array $data
    * @return LoginAction Chainable
    */
    public function set_data(array $data)
    {
        //parent::set_data($data);
        $this->set_obj_data($data);

        unset($data['obj_type']);
        unset($data['obj_id']);
        $this->set_update_data($data);

        return $this;
    }

    /**
    * @param array $update_data
    * @return SaveAction Chainable
    */
    public function set_update_data(array $update_data)
    {
        $this->_update_data = $update_data;
        return $this;
    }

    /**
    * @return array
    */
    public function update_data()
    {
        return $this->_update_data;
    }

    /**
    * @param ModelInterface|null $save_data
    * @return SaveAction Chainable
    */
    public function set_obj($obj)
    {
        $this->_obj = $obj;
        return $this;
    }

    /**
    * @return void
    */
    public function run()
    {
        $this->set_data(Charcoal::app()->request->post());
        //$obj_type = Charcoal::app()->request->post('obj_type');
        //$obj_id = Charcoal::app()->request->post('obj_id');
        $update_data = Charcoal::app()->request->post();

        try {
            $obj = $this->load_obj();

            $obj->set_flat_data($this->update_data());
            $validation = $obj->validate();
            // @todo Handle validation

            $ret = $obj->update();
            if ($ret) {
                $this->log_object_update();
                $this->set_success(true);
                $this->output();
            } else {
                $this->set_success(false);
                $this->output(404);
            }
        } catch (Exception $e) {
            $this->set_success(false);
            $this->output(404);
        }

    }

    /**
    * @return array
    */
    public function response()
    {
        $success = $this->success();

        $response = [
            'success'=>$this->success()
        ];
        return $response;
    }

    /**
    *
    */
    public function log_object_update()
    {
        // @todo
    }
}
