<?php

namespace Charcoal\Admin\Action\Object;

use Exception;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-core'
use Charcoal\Model\ModelValidator;

// From 'charcoal-object'
use Charcoal\Object\ContentInterface;

/**
 * Action: Save an object and update copy in storage.
 *
 * ## Required Parameters
 *
 * - `obj_type` (_string_) — The object type, as an identifier for a {@see \Charcoal\Model\ModelInterface}.
 * - `obj_id` (_mixed_) — The object ID to load and update
 *
 * ## Response
 *
 * - `success` (_boolean_) — TRUE if the object was properly saved, FALSE in case of any error.
 *
 * ## HTTP Status Codes
 *
 * - `200` — Successful; Object has been updated
 * - `400` — Client error; Invalid request data
 * - `500` — Server error; Object could not be updated
 */
class UpdateAction extends AbstractSaveAction
{
    /**
     * Data for the target model.
     *
     * @var array
     */
    protected $updateData = [];

    /**
     * Set the action's dataset.
     *
     * Extract relevant model data from $data, excluding _object type_ and _ID_.
     * This {@see self::$updateData subset} is merged onto the target model.
     *
     * @param  array $data The update action data.
     * @return UpdateAction Chainable
     */
    public function setData(array $data)
    {
        parent::setData($data);

        unset($data['obj_type']);
        unset($data['obj_id']);

        $this->setUpdateData($data);

        return $this;
    }

    /**
     * Set the dataset used to update the target model.
     *
     * @param  array $updateData The update data.
     * @return UpdateAction Chainable
     */
    public function setUpdateData(array $updateData)
    {
        $this->updateData = $updateData;

        return $this;
    }

    /**
     * Retrieve the dataset used to update the target model.
     *
     * @return array
     */
    public function updateData()
    {
        return $this->updateData;
    }

    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $failMessage = $this->translator()->translation('Failed to update object');
            $errorThrown = strtr($this->translator()->translation('{{ errorMessage }}: {{ errorThrown }}'), [
                '{{ errorMessage }}' => $failMessage
            ]);
            $reqMessage  = $this->translator()->translation(
                '{{ parameter }} required, must be a {{ expectedType }}, received {{ actualType }}'
            );
            $typeMessage = $this->translator()->translation(
                '{{ parameter }} must be a {{ expectedType }}, received {{ actualType }}'
            );

            $objType = $request->getParam('obj_type');
            $objId   = $request->getParam('obj_id');

            if (!$objType) {
                $actualType = is_object($objType) ? get_class($objType) : gettype($objType);
                $this->addFeedback('error', strtr($reqMessage, [
                    '{{ parameter }}'    => '"obj_type"',
                    '{{ expectedType }}' => 'string',
                    '{{ actualType }}'   => $actualType,
                ]));
                $this->setSuccess(false);

                return $response->withStatus(400);
            }

            if (!$objId) {
                $actualType = is_object($objId) ? get_class($objId) : gettype($objId);
                $this->addFeedback('error', strtr($reqMessage, [
                    '{{ parameter }}'    => '"obj_id"',
                    '{{ expectedType }}' => 'ID',
                    '{{ actualType }}'   => $actualType,
                ]));
                $this->setSuccess(false);

                return $response->withStatus(400);
            }

            // Load or reload object (From `ObjectContainerTrait`)
            $obj = $this->loadObj();
            $obj->mergeData($this->updateData());

            $valid = $obj->validate();
            if (!$valid) {
                if (!$this->hasFeedbacks()) {
                    $this->addFeedback('error', strtr($errorThrown, [
                        '{{ errorThrown }}' => $this->translator()->translate('Invalid Data')
                    ]));
                }

                $this->addFeedbackFromValidation($obj);
                $this->setSuccess(false);

                return $response->withStatus(400);
            }

            if ($obj instanceof ContentInterface) {
                $obj->setLastModifiedBy($this->authorIdent());
            }

            $result = $obj->update();

            if ($result) {
                $this->addFeedback('success', $this->translator()->translate('Object has been successfully updated.'));
                $this->addFeedback('success', strtr($this->translator()->translate('Updated Object: {{ objId }}'), [
                    '{{ objId }}' => $obj->id()
                ]));
                $this->addFeedbackFromValidation($obj, [ ModelValidator::NOTICE, ModelValidator::WARNING ]);
                $this->setSuccess(true);

                return $response;
            } else {
                $this->addFeedback('error', $failMessage);
                $this->addFeedbackFromValidation($obj);
                $this->setSuccess(false);

                return $response->withStatus(500);
            }
        } catch (Exception $e) {
            $this->addFeedback('error', strtr($errorThrown, [
                '{{ errorThrown }}' => $e->getMessage()
            ]));
            $this->setSuccess(false);

            return $response->withStatus(500);
        }
    }
}
