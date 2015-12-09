<?php

namespace Charcoal\Admin\Action\Object;

// Dependencies from `PHP`
use \Exception;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminAction;
use \Charcoal\Admin\Ui\ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait;

/**
* Admin Save Action: Save an object in its Storage.
*
* ## Required Parameters
* - `obj_type`
*
* ## Response
* - `success` _boolean_ True if the object was properly saved, false in case of any error.
* - `obj_id` _mixed_ The created object ID, if any.
* - `obj` _array_ The created object data.
*
* ## HTTP Codes
* - `200` in case of a successful login
* - `404` if any error occurs
*/
class SaveAction extends AdminAction implements ObjectContainerInterface
{
    use ObjectContainerTrait;

    /**
    * @var array $save_data
    */
    private $save_data = [];

    /**
    * @param array $data
    * @return LoginAction Chainable
    */
    public function set_data(array $data)
    {

        parent::set_data($data);
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
        $this->save_data = $save_data;
        return $this;
    }

    /**
    * @return array
    */
    public function save_data()
    {
        return $this->save_data;
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
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $this->set_data($request->getParams());

            // Create or load object (From `ObjectContainerTrait`)
            $obj = $this->obj();

            $obj->set_flat_data($this->save_data());
            $valid = $obj->validate();
            $valid = true;
            if (!$valid) {
                $validation = $obj->validation();
                // @todo: Validation info to feedback
                $this->set_success(false);
                $this->add_feedback('error', 'Failed to save object: validation error(s).');
                return $response->withStatus(404);
            }

            $ret = $obj->save();

            if ($ret) {
                $this->set_obj($obj);
                $this->log_object_save();
                $this->set_success(true);
                $this->add_feedback('success', 'Object saved successfully');
                return $response;
            } else {
                $this->set_obj(null);
                $this->set_success(false);
                return $response->withStatus(404);
            }
        } catch (Exception $e) {
            //var_dump($e);
            $this->set_obj(null);
            $this->set_success(false);
            $this->add_feedback('error', $e->getMessage());
            return $response->withStatus(404);
        }

    }

    /**
    * @return array
    */
    public function results()
    {
        $results = [
            'success'   => $this->success(),
            'obj_id'    => $this->obj()->id(),
            'obj'       => $this->obj(),
            'feedbacks' => $this->feedbacks()
        ];
        return $results;
    }

    /**
    *
    */
    public function log_object_save()
    {
        // @todo
    }
}
