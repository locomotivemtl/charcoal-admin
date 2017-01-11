<?php

namespace Charcoal\Admin\Action\Object;

use \Exception;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From `charcoal-core`
use \Charcoal\Model\ModelValidator;

/**
 * Admin Create Action: Create an object in its Storage.
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
class SaveAction extends AbstractSaveAction
{
    /**
     * @var array $saveData
     */
    protected $saveData = [];

    /**
     * @param array|\ArrayAccess $data The action data.
     * @return LoginAction Chainable
     */
    public function setData($data)
    {
        parent::setData($data);

        unset($data['obj_type']);
        $this->setSaveData($data);

        return $this;
    }

    /**
     * @param array $saveData The save data.
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

            $saveData = $this->saveData();

            $obj->setFlatData($this->saveData());

            $valid = $obj->validate();

            if (!$valid) {
                $this->setSuccess(false);
                $this->addFeedbackFromValidation($obj);
                if (!$this->hasFeedbacks()) {
                    $this->addFeedback('error', 'Failed to create object: validation error(s).');
                }

                return $response->withStatus(404);
            }

            $authorIdent = $this->authorIdent();
            if (!$obj->lastModifiedBy()) {
                $obj->setLastModifiedBy($authorIdent);
            }

            if (!$obj->createdBy()) {
                $obj->setCreatedBy($authorIdent);
            }

            $ret = $obj->save();

            if ($ret) {
                $this->setObj($obj);
                $this->setSuccess(true);
                $this->addFeedback('success', 'Object was successfully created');
                $this->addFeedbackFromValidation($obj, ModelValidator::NOTICE);

                return $response;
            } else {
                $this->setObj(null);
                $this->setSuccess(false);
                $this->addFeedback('error', 'Could not create objet. Unknown error');
                $this->addFeedbackFromValidation($obj);

                return $response->withStatus(404);
            }
        } catch (Exception $e) {
            $this->setObj(null);
            $this->setSuccess(false);
            $this->addFeedback('error', $e->getMessage());

            return $response->withStatus(404);
        }
    }
}