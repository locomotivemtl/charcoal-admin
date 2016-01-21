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
     * @var array $updateData
     */
    protected $updateData = [];

    /**
     * @param array $data
     * @return LoginAction Chainable
     */
    public function setData(array $data)
    {
        parent::setData($data);
        # $this->setObjData($data);

        if (isset($data['next_url'])) {
            $this->set_next_url($data['next_url']);
            unset($data['next_url']);
        }

        unset($data['obj_type']);
        unset($data['obj_id']);
        $this->setUpdateData($data);

        return $this;
    }

    /**
     * @param array $updateData
     * @return SaveAction Chainable
     */
    public function setUpdateData(array $updateData)
    {
        $this->updateData = $updateData;
        return $this;
    }

    /**
     * @return array
     */
    public function updateData()
    {
        return $this->updateData;
    }

    /**
     * @param ModelInterface|null $saveData
     * @return SaveAction Chainable
     */
    public function setObj($obj)
    {
        $this->Obj = $obj;
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
            $this->setData($params);

            // Load (or reload) object (From `ObjectContainerTrait`)
            $obj = $this->loadObj();

            $obj->setData($this->updateData());
            $valid = $obj->validate();

            if (!$valid) {
                $validation = $obj->validation();
                // @todo: Validation info to feedback
                $this->setSuccess(false);
                $this->addFeedback('error', 'Failed to update object: validation error(s).');
                return $response->withStatus(404);
            }

            $ret = $obj->update();

            if ($ret) {
                $this->setSuccess(true);
                $this->addFeedback('success', sprintf('Object was successfully updated. (ID: %s)', $obj->id()));
                return $response;
            } else {
                $this->setSuccess(false);
                $this->addFeedback('error', 'Could not update objet. Unknown error');
                return $response->withStatus(404);
            }
        } catch (Exception $e) {
            $this->setSuccess(false);
            $this->addFeedback('error', $e->getMessage());
            return $response->withStatus(404);
        }

    }

    /**
     * @return array
     */
    public function results()
    {
        $results = [
            'success'=>$this->success(),
            'obj_id'=>$this->obj()->id(),
            'obj'=>$this->obj(),
            'feedbacks'=>$this->feedbacks()
        ];
        return $results;
    }
}
