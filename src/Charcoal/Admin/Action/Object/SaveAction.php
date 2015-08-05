<?php

namespace Charcoal\Admin\Action\Object;

use \Exception as Exception;

// Module `charcoal-core` dependencies
use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Model\ModelFactory as ModelFactory;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminAction as AdminAction;
use \Charcoal\Admin\Ui\ObjectContainerInterface as ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait as ObjectContainerTrait;

/**
* Admin Save Action: Save an object in its Storage.
*
* ## Required Parameters
* - `obj_type`
*
* ## Response
* - `success` _boolean_ True if the object was properly saved, false in case of any error.
* - `obj_id` _mixed_ The created object ID, if any.
*
* ## HTTP Codes
* - `200` in case of a successful login
* - `404` if any error occurs
*/
class SaveAction extends AdminAction implements ObjectContainerInterface
{
    use ObjectContainerTrait;

    protected $_save_data = [];

    /**
    * @param array $data
    * @return LoginAction Chainable
    */
    public function set_data(array $data)
    {
        //parent::set_data($data);
        $this->set_obj_data($data);

        unset($data['obj_type']);
        $this->set_save_data($data);

        return $this;
    }

    /**
    * @param array $save_data
    * @return SaveAction Chainable
    */
    public function set_save_data(array $save_data)
    {
        $this->_save_data = $save_data;
        return $this;
    }

    /**
    * @return array
    */
    public function save_data()
    {
        return $this->_save_data;
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
    *
    * @see \Charcoal\Model\ModelFactory::get()
    */
    public function run()
    {
        $this->set_data(Charcoal::app()->request->post());

        try {
            // Create or load object (From `ObjectContainerTrait`)
            $obj = $this->obj();

            $obj->set_flat_data($this->save_data());
            $validation = $obj->validate();
            // @todo Handle validation

            $ret = $obj->save();

            if ($ret) {
                $this->set_obj($obj);
                $this->log_object_save();
                $this->set_success(true);
                $this->output();
            } else {
                $this->set_obj(null);
                $this->set_success(false);
                $this->output(404);
            }
        } catch (Exception $e) {
            $this->set_obj(null);
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
            'success'=>$this->success(),
            'obj_id'=>$this->obj()->id(),
            'obj'=>$this->obj()
        ];
        return $response;
    }

    /**
    *
    */
    public function log_object_save()
    {
        // @todo
    }
}
