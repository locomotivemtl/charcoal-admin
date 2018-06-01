<?php

namespace Charcoal\Admin\Action\Object;

use InvalidArgumentException;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;
use Charcoal\Model\ModelValidator;

// From 'charcoal-user'
use Charcoal\User\Authenticator;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;
use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Ui\ObjectContainerTrait;

/**
 * Base Admin Save Action
 *
 * Common methods between Update and Save.
 */
abstract class AbstractSaveAction extends AdminAction implements ObjectContainerInterface
{
    use ObjectContainerTrait;

    /**
     * @return string
     */
    protected function authorIdent()
    {
        $user = $this->getAuthenticatedUser();
        return (string)$user->id();
    }

    /**
     * @param ModelInterface|null $obj The object.
     * @return SaveAction Chainable
     */
    public function setObj($obj)
    {
        $this->obj = $obj;

        if ($obj instanceof ModelInterface) {
            $this->objId = $obj->id();
        } else {
            $this->objId = null;
        }

        return $this;
    }

    /**
     * Merge the given object's validation results the response feedback.
     *
     * @param  ModelInterface       $obj     The validated object.
     * @param  string[]|string|null $filters Filter the levels to merge.
     * @throws InvalidArgumentException If the filters are invalid.
     * @return SaveAction Chainable
     */
    public function addFeedbackFromValidation(ModelInterface $obj, $filters = null)
    {
        $validator = $obj->validator();
        $levels    = [ ModelValidator::ERROR, ModelValidator::WARNING, ModelValidator::NOTICE ];

        if (is_string($filters) && in_array($filters, $levels)) {
            $results = call_user_func([ $validator, $filters.'Results' ]);
            foreach ($results as $result) {
                $this->addFeedback($result->level(), $result->message());
            }

            return $this;
        }

        if (!is_array($filters) && $filters !== null) {
            throw new InvalidArgumentException(
                'Filters must be an array of validation levels or NULL'
            );
        }

        $validation = $validator->results();
        foreach ($validation as $level => $results) {
            if ($filters === null || in_array($level, $filters)) {
                foreach ($results as $result) {
                    $this->addFeedback($result->level(), $result->message());
                }
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function results()
    {
        $results =  [
            'success'   => $this->success(),
            'obj_id'    => null,
            'obj'       => null,
            'feedbacks' => $this->feedbacks()
        ];

        if ($this->success() === true) {
            $results['obj_id'] = $this->obj()->id();
            $results['obj'] = $this->obj();
        }

        return $results;
    }
}
