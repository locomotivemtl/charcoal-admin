<?php

namespace Charcoal\Admin\Action\Object;

// Dependencies from `PHP`
use \Exception;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

use \Pimple\Container;

// From `charcoal-base`
use \Charcoal\User\authenticator;

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
     * @var Authenticator $authenticator
     */
    private $authenticator;

    /**
     * @var array $updateData
     */
    protected $updateData = [];

    /**
     * @param Container $container A DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setAuthenticator($container['admin/authenticator']);
    }

    /**
     * @param Authenticator $authenticator The authenticator service.
     * @return void
     */
    private function setAuthenticator(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }


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
     * @param ModelInterface|null $obj The object.
     * @return SaveAction Chainable
     */
    public function setObj($obj)
    {
        $this->obj = $obj;
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


            $authorIdent = $this->authorIdent();

            // Load or reload object (From `ObjectContainerTrait`)
            $obj = $this->loadObj();

            $updateData = $this->updateData();
            $updateData['last_modified_by'] = $authorIdent;

            $obj->mergeData($this->updateData());

            $valid = $obj->validate();

            if (!$valid) {
                $validation = $obj->validation();
                // @todo: Validation info to feedback
                $this->setSuccess(false);
                $this->addFeedback('error', 'Failed to update object: validation error(s).');
                return $response->withStatus(404);
            }

            $author = $this->authenticator->authenticate();
            if (!$obj->lastModifiedBy()) {
                $obj->setLastModifiedBy($author->id());
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
     * @return string
     */
    private function authorIdent()
    {
        $author = $this->authenticator->authenticate();
        return (string)$author->id();
    }

    /**
     * @return array
     */
    public function results()
    {
        return [
            'success'   => $this->success(),
            'obj_id'    => $this->obj()->id(),
            'obj'       => $this->obj(),
            'feedbacks' => $this->feedbacks()
        ];
    }
}
