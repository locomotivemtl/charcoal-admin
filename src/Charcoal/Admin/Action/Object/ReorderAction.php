<?php

namespace Charcoal\Admin\Action\Object;

use \Exception;

// Dependencies from PSR-7 (HTTP Messaging)
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Dependency from Pimple
use \Pimple\Container;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminAction;
use \Charcoal\Admin\Ui\ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait;

/**
 * ## Required Parameters
 *
 * - `obj_type`
 * - `obj_orders`
 *
 * ## Optional Parameters
 *
 * - `starting_order`
 *
 */
class ReorderAction extends AdminAction implements ObjectContainerInterface
{
    use ObjectContainerTrait;

    /**
     * @var array $objOrders
     */
    private $objOrders;

    /**
     * @var integer $startingOrder
     */
    private $startingOrder = 1;

    /**
     * @param Container $container A DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Fulfills `ObjectContainerTrait` dependencies.
        $this->setModelFactory($container['model/factory']);
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $objType = $request->getParam('obj_type');
        $objOrders = $request->getParam('obj_orders');
        $startingOrder = (int)$request->getParam('start_order');

        if (!$objType) {
            $this->setSuccess(false);
            $this->addFeedback('error', 'obj_type required');
            return $response->withStatus(404);
        }
        $this->setObjType($objType);

        if (!$objOrders || !is_array($objOrders)) {
            $this->setSuccess(false);
            $this->addFeedback('error', 'obj_orders required / must be an array');
            return $response->withStatus(404);
        }

        try {
            $proto = $this->obj();

            $pos = $startingOrder;
            foreach ($objOrders as $orderId) {
                $q = '
                update
                    `'.$proto->source()->table().'`
                set
                    `position` = :position
                where
                    `'.$proto->key().'` = :id';
                $proto->source()->dbQuery($q, [
                    'id' => $orderId,
                    'position' => $pos
                ]);
                $pos++;
            }

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
     * @return array
     */
    public function results()
    {
        return [
            'success'           => $this->success(),
            'feedbacks'         => $this->feedbacks()
        ];
    }
}
