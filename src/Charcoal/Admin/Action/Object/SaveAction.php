<?php

namespace Charcoal\Admin\Action\Object;

use Exception;
use PDOException;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;
use Charcoal\Model\ModelValidator;
use Charcoal\Source\StorableInterface;

// From 'charcoal-property'
use Charcoal\Property\DescribablePropertyInterface;

// From 'charcoal-object'
use Charcoal\Object\AuthorableInterface;

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
     * Sets the action data from a PSR Request object.
     *
     * Extract relevant model data from $data, excluding _object type_.
     * This {@see self::$saveData subset} is merged onto the target model.
     *
     * @param  RequestInterface $request A PSR-7 compatible Request instance.
     * @return self
     */
    protected function setDataFromRequest(RequestInterface $request)
    {
        parent::setDataFromRequest($request);

        $data = $this->filterSaveData($request->getParams());

        $this->setSaveData($data);

        return $this;
    }

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    protected function validDataFromRequest()
    {
        return array_merge([
            'obj_type'
        ], parent::validDataFromRequest());
    }

    /**
     * Filter the dataset used to create the target model.
     *
     * @param  array $data The save data to filter.
     * @return array
     */
    public function filterSaveData(array $data)
    {
        unset(
            $data['obj_type'],
            $data['objType']
        );

        return $data;
    }

    /**
     * Set the dataset used to create the target model.
     *
     * @param  array $data The save data.
     * @return SaveAction Chainable
     */
    public function setSaveData(array $data)
    {
        $this->saveData = $data;

        return $this;
    }

    /**
     * Retrieve the dataset used to create the target model.
     *
     * @return array
     */
    public function getSaveData()
    {
        return $this->saveData;
    }

    /**
     * @param  ModelInterface $obj The object to validate.
     * @return boolean
     */
    public function validate(ModelInterface $obj)
    {
        $this->parsePrimaryKey($obj);
        $result = parent::validate($obj);

        return $result;
    }

    /**
     * Prepare the primary key for the object.
     *
     * @param  ModelInterface $obj The object to validate.
     * @return void
     */
    public function parsePrimaryKey(ModelInterface $obj)
    {
        if (($obj instanceof StorableInterface) && ($obj instanceof DescribablePropertyInterface)) {
            $pk = $obj->key();
            $id = $obj[$pk];

            $id = $obj->property($pk)->save($id);
            if (!empty($id)) {
                $obj[$pk] = $id;
            }
        }
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
            $obj->setFlatData($this->getSaveData());

            $valid = $this->validate($obj);
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

            if ($obj instanceof AuthorableInterface) {
                $authorIdent = $this->getAuthorIdent();
                if (!$obj['lastModifiedBy']) {
                    $obj->setLastModifiedBy($authorIdent);
                }

                if (!$obj['createdBy']) {
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
