<?php

namespace Charcoal\Admin\Action\Object;

use Exception;
use PDOException;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-core'
use Charcoal\Model\ModelValidator;

// From 'charcoal-object'
use Charcoal\Object\ContentInterface;

/**
 * Action: Create an object and insert into storage.
 *
 * ## Required Parameters
 *
 * - `obj_type` (_string_) — The object type, as an identifier for a {@see \Charcoal\Model\ModelInterface}.
 *
 * ## Response
 *
 * - `success` (_boolean_) — TRUE if the object was properly saved, FALSE in case of any error.
 * - `obj_id` (_mixed_) — The created object ID, if any.
 * - `obj` (_array_) — The created object data.
 *
 * ## HTTP Status Codes
 *
 * - `200` — Successful; Object has been created
 * - `400` — Client error; Invalid request data
 * - `500` — Server error; Object could not be created
 */
class SaveAction extends AbstractSaveAction
{
    /**
     * Data for the target model.
     *
     * @var array
     */
    protected $saveData = [];

    /**
     * Set the action's dataset.
     *
     * Extract relevant model data from $data, excluding _object type_.
     * This {@see self::$saveData subset} is merged onto the target model.
     *
     * @param  array $data The update action data.
     * @return SaveAction Chainable
     */
    public function setData(array $data)
    {
        parent::setData($data);

        unset($data['obj_type']);
        $this->setSaveData($data);

        return $this;
    }

    /**
     * Set the dataset used to create the target model.
     *
     * @param  array $saveData The save data.
     * @return SaveAction Chainable
     */
    public function setSaveData(array $saveData)
    {
        $this->saveData = $saveData;

        return $this;
    }

    /**
     * Retrieve the dataset used to create the target model.
     *
     * @return array
     */
    public function saveData()
    {
        return $this->saveData;
    }

    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $failMessage = $this->translator()->translation('Failed to create object');
            $errorThrown = strtr($this->translator()->translation('{{ errorMessage }}: {{ errorThrown }}'), [
                '{{ errorMessage }}' => $failMessage
            ]);
            $reqMessage  = $this->translator()->translation(
                '{{ parameter }} required, must be a {{ expectedType }}, received {{ actualType }}'
            );

            $objType = $request->getParam('obj_type');
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

            // Create or load object (From `ObjectContainerTrait`)
            $obj = $this->obj();
            $obj->setFlatData($this->saveData());

            $valid = $obj->validate();
            if (!$valid) {
                if (!$this->hasFeedbacks()) {
                    $this->addFeedback('error', strtr($errorThrown, [
                        '{{ errorThrown }}' => $this->translator()->translation('Invalid Data')
                    ]));
                }

                $this->addFeedbackFromValidation($obj);
                $this->setSuccess(false);

                return $response->withStatus(400);
            }

            if ($obj instanceof ContentInterface) {
                $authorIdent = $this->authorIdent();
                if (!$obj->lastModifiedBy()) {
                    $obj->setLastModifiedBy($authorIdent);
                }

                if (!$obj->createdBy()) {
                    $obj->setCreatedBy($authorIdent);
                }
            }

            $result = $obj->save();

            if ($result) {
                $this->setObj($obj);

                $this->addFeedback('success', $this->translator()->translate('Object has been successfully created.'));
                $this->addFeedback('success', strtr($this->translator()->translate('Created Object: {{ objId }}'), [
                    '{{ objId }}' => $obj->id()
                ]));
                $this->addFeedbackFromValidation($obj, [ ModelValidator::NOTICE, ModelValidator::WARNING ]);
                $this->setSuccess(true);

                return $response;
            } else {
                $this->setObj(null);

                $this->addFeedback('error', $failMessage);
                $this->addFeedbackFromValidation($obj);
                $this->setSuccess(false);

                return $response->withStatus(500);
            }
        } catch (PDOException $e) {
            $this->setObj(null);

            if (isset($e->errorInfo[2])) {
                $message = $e->errorInfo[2];
            } else {
                $message = $e->getMessage();
            }

            $this->addFeedback('error', strtr($errorThrown, [
                '{{ errorThrown }}' => $message
            ]));
            $this->setSuccess(false);

            return $response->withStatus(500);
        } catch (Exception $e) {
            $this->setObj(null);

            $this->addFeedback('error', strtr($errorThrown, [
                '{{ errorThrown }}' => $e->getMessage()
            ]));
            $this->setSuccess(false);

            return $response->withStatus(500);
        }
    }
}
