<?php

namespace Charcoal\Admin\Action\Object;

use Exception;
use UnexpectedValueException;
use InvalidArgumentException;
use RuntimeException;

// PSR-7 (http messaging) dependencies
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Pimple\Container;

// Module `charcoal-factory` depenencie

// Moduele  `charcoal-core` dependencies
use Charcoal\Model\Collection;
use Charcoal\Loader\CollectionLoader;

// from `charcoal-admin`
use Charcoal\Admin\AdminAction;

/**
 * Admin Load Action: Load an object from database.
 *
 * ## Required Parameters
 *
 * - `obj_type`
 *
 * ## Optional Parameters
 *
 * - `obj_id`
 *
 * ## Response
 *
 * - `success` _boolean_ True if the object was properly loaded, false in case of any error.
 * - `collection` _Charcoal\Model\Collection_ The created collection, if any.
 *
 * ## HTTP Codes
 * - `400` if any bad requests occurs
 *
 * Ident: `charcoal/admin/action/object/load`
 */
class LoadAction extends AdminAction
{
    /**
     * Store the collection loader for the current class.
     *
     * @var CollectionLoader
     */
    private $collectionLoader;

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $modelFactory;

    /**
     * @var string
     */
    protected $objType;

    /**
     * @var Collection
     */
    protected $objCollection;

    // ==========================================================================
    // FUNCTIONS
    // ==========================================================================

    /**
     * Dependencies
     * @param Container $container DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setCollectionLoader($container['model/collection/loader']);
    }

    /**
     * @param  RequestInterface  $request  The request options.
     * @param  ResponseInterface $response The response to return.
     * @return ResponseInterface
     * @throws UnexpectedValueException If "obj_id" is passed as $request option.
     * @todo   Implement obj_id support for load object action
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $objType = $request->getParam('obj_type');
        $objId   = $request->getParam('obj_id');

        if ($objId) {
            throw new UnexpectedValueException(
                'An error occured loading the object: obj_id is not yet supported in LoadAction'
            );
        }

        if (!$objType) {
            $this->setSuccess(false);
            $this->addFeedback('error', '"obj_type" required');
            return $response->withStatus(400);
        }

        try {
            $this->setObjType($objType);
            $this->objCollection = $this->loadObjectCollection($objType);
            $this->setSuccess(true);
            return $response;
        } catch (Exception $e) {
            $this->addFeedback('error', sprintf('An error occured loading the object: "%s"', $e->getMessage()));
            $this->addFeedback('error', $e->getMessage());
            $this->setSuccess(false);
            return $response->withStatus(500);
        }
    }

    /**
     * Load Object Collection
     *
     * @param string $objType The object type as string.
     * @return \Charcoal\Model\Collection
     */
    protected function loadObjectCollection($objType)
    {
        $proto = $this->modelFactory()->get($objType);
        $loader = $this->collectionLoader();
        $loader->setModel($proto);
        $loader->addFilter('active', true);

        return $loader->load();
    }

    // ==========================================================================
    // SETTERS
    // ==========================================================================

    /**
     * @param string $objType The object type as string.
     * @return self
     * @throws InvalidArgumentException If $objType is not a string.
     */
    public function setObjType($objType)
    {
        if (!is_string($objType)) {
            throw new InvalidArgumentException(
                'obj_type must be a string'
            );
        }
        $this->objType = $objType;
        return $this;
    }

    /**
     * Set a model collection loader.
     *
     * @param CollectionLoader $loader The collection loader.
     * @return self
     */
    protected function setCollectionLoader(CollectionLoader $loader)
    {
        $this->collectionLoader = $loader;

        return $this;
    }

    // ==========================================================================
    // GETTERS
    // ==========================================================================

    /**
     * @return string The object type as string.
     */
    public function objType()
    {
        return $this->objType;
    }

    /**
     * @return array The object collection parsed as array
     */
    public function objCollection()
    {
        if (!$this->objCollection) {
            return [];
        }
        return $this->objCollection->objects();
    }

    /**
     * Retrieve the model collection loader.
     *
     * @throws RuntimeException If the collection loader was not previously set.
     * @return CollectionLoader
     */
    public function collectionLoader()
    {
        if (!isset($this->collectionLoader)) {
            throw new RuntimeException(
                sprintf('Collection Loader is not defined for "%s"', get_class($this))
            );
        }

        return $this->collectionLoader;
    }

    // ==========================================================================
    // RESULTS
    // ==========================================================================

    /**
     * @return array
     */
    public function results()
    {
        return [
            'success'    => $this->success(),
            'feedbacks'  => $this->feedbacks(),
            'collection' => $this->objCollection()
        ];
    }
}
