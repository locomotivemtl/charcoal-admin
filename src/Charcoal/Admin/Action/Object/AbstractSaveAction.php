<?php

namespace Charcoal\Admin\Action\Object;

use InvalidArgumentException;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;
use Charcoal\Validator\ValidatableInterface;

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
    protected function getAuthorIdent()
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
     * Determines if object is validatable.
     *
     * @param  ModelInterface $obj The object to validate.
     * @return boolean
     */
    public function isValidatable(ModelInterface $obj)
    {
        return ($obj instanceof ValidatableInterface);
    }

    /**
     * Defer object validation.
     *
     * This methos is useful for subclasses.
     *
     * @param  ModelInterface $obj The object to validate.
     * @return boolean
     */
    public function validate(ModelInterface $obj)
    {
        if ($this->isValidatable($obj)) {
            return $obj->validate();
        }

        return true;
    }

    /**
     * Add feedback from an object's validation results.
     *
     * Based on {@see \Charcoal\Admin\Ui\FeedbackContainerTrait::addFeedbackFromValidator()}.
     *
     * @param  ModelInterface       $obj     The validatable object.
     * @param  string[]|string|null $filters Filter the levels to merge.
     * @throws InvalidArgumentException If the filters are invalid.
     * @return self
     */
    public function addFeedbackFromModel(ModelInterface $obj, $filters = null)
    {
        if (!$this->isValidatable($obj)) {
            return $this;
        }

        return $this->addFeedbackFromValidatable($obj, $filters);
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
