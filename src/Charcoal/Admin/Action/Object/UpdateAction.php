<?php

namespace Charcoal\Admin\Action\Object;

use \Exception;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From `charcoal-core`
use \Charcoal\Model\ModelValidator;

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
class UpdateAction extends AbstractSaveAction
{
    /**
     * @var array $updateData
     */
    protected $updateData = [];

    /**
     * @param array|\ArrayAccess $data The update action data.
     * @return LoginAction Chainable
     */
    public function setData($data)
    {
        parent::setData($data);

        unset($data['obj_type']);
        unset($data['obj_id']);
        $this->setUpdateData($data);

        return $this;
    }

    /**
     * @param array $updateData The update data.
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
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $this->setData($request->getParams());

            // Load or reload object (From `ObjectContainerTrait`)
            $obj = $this->loadObj();

            $updateData = $this->updateData();

            $obj->mergeData($this->updateData());

            $valid = $obj->validate();

            if (!$valid) {
                $this->setSuccess(false);
                $this->addFeedbackFromValidation($obj);
                if (!$this->hasFeedbacks()) {
                    $this->addFeedback('error', 'Failed to update object: validation error(s).');
                }

                return $response->withStatus(404);
            }

            $authorIdent = $this->authorIdent();
            if (!$obj->lastModifiedBy()) {
                $obj->setLastModifiedBy($authorIdent);
            }

            $ret = $obj->update();

            if ($ret) {
                $this->setSuccess(true);
                $this->addFeedback('success', sprintf('Object was successfully updated. (ID: %s)', $obj->id()));
                $this->addFeedbackFromValidation($obj, ModelValidator::NOTICE);

                return $response;
            } else {
                $this->setSuccess(false);
                $this->addFeedback('error', 'Could not update objet. Unknown error');
                $this->addFeedbackFromValidation($obj);

                return $response->withStatus(404);
            }
        } catch (Exception $e) {
            $this->setSuccess(false);
            $this->addFeedback('error', $e->getMessage());

            return $response->withStatus(404);
        }
    }
}