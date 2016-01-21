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
     * @var array $saveData
     */
    private $saveData = [];

    /**
     * @param array $data
     * @return LoginAction Chainable
     */
    public function setData(array $data)
    {
        parent::setData($data);
        # $this->setObjData($data);

        unset($data['obj_type']);
        $this->setSaveData($data);

        return $this;
    }

    /**
     * @param array $saveData
     * @return SaveAction Chainable
     */
    public function setSaveData(array $saveData)
    {
        $this->saveData = $saveData;
        return $this;
    }

    /**
     * @return array
     */
    public function saveData()
    {
        return $this->saveData;
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
            $this->setData($request->getParams());

            // Create or load object (From `ObjectContainerTrait`)
            $obj = $this->obj();

            $obj->setFlatData($this->saveData());
            $valid = $obj->validate();
            $valid = true;
            if (!$valid) {
                $validation = $obj->validation();
                // @todo: Validation info to feedback
                $this->setSuccess(false);
                $this->addFeedback('error', 'Failed to save object: validation error(s).');
                return $response->withStatus(404);
            }

            $ret = $obj->save();

            if ($ret) {
                $this->setObj($obj);
                $this->logObjectSave();
                $this->setSuccess(true);
                $this->addFeedback('success', 'Object saved successfully');
                return $response;
            } else {
                $this->setObj(null);
                $this->setSuccess(false);
                return $response->withStatus(404);
            }
        } catch (Exception $e) {
            //var_dump($e);
            $this->setObj(null);
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
            'success'   => $this->success(),
            'obj_id'    => $this->obj()->id(),
            'obj'       => $this->obj(),
            'feedbacks' => $this->feedbacks()
        ];
        return $results;
    }
}
