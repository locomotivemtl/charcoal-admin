<?php

namespace Charcoal\Admin\Action\Object;

// Dependencies from `PHP`
use \Exception as Exception;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminAction;
use \Charcoal\Admin\Ui\ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait;

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

    /**
    * @var array $update_data
    */
    protected $update_data = [];

    /**
    * @param array $data
    * @return LoginAction Chainable
    */
    public function set_data(array $data)
    {
        //parent::set_data($data);
        $this->set_obj_data($data);

        if (isset($data['next_url'])) {
            $this->set_next_url($data['next_url']);
            unset($data['next_url']);
        }

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
        $this->update_data = $update_data;
        return $this;
    }

    /**
    * @return array
    */
    public function update_data()
    {
        return $this->update_data;
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
            $params = $request->getParams();
            $this->set_data($params);

            // Load (or reload) object (From `ObjectContainerTrait`)
            $obj = $this->load_obj();

            $obj->set_data($this->update_data());
            $valid = $obj->validate();

            if (!$valid) {
                $validation = $obj->validation();
                // @todo: Validation info to feedback
                $this->set_success(false);
                $this->add_feedback('error', 'Failed to update object: validation error(s).');
                return $response->withStatus(404);
            }

            $ret = $obj->update();

            if ($ret) {
                $this->log_object_update();
                $this->set_success(true);
                $this->add_feedback('success', sprintf('Object was successfully updated. (ID: %s)', $obj->id()));
                return $response;
            } else {
                $this->set_success(false);
                $this->add_feedback('error', 'Could not update objet. Unknown error');
                return $response->withStatus(404);
            }
        } catch (Exception $e) {
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
        $response = [
            'success'=>$this->success(),
            'obj_id'=>$this->obj()->id(),
            'obj'=>$this->obj(),
            'feedbacks'=>$this->feedbacks(),
            'next_url'=>$this->next_url()
        ];
        return $results;
    }

    /**
    *
    */
    public function log_object_update()
    {
        // @todo
    }
}
