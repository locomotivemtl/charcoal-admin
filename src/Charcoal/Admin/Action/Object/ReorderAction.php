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
 * - `obj_type`
 * - `obj_orders`
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
    private $startingOrder;

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
     * @param array $orders The object orders.
     * @return ReorderAction Chainable
     */
    public function setObjOrders(array $orders)
    {
        $this->objOrders = $orders;
        return $this;
    }

    /**
     * @return array
     */
    public function objOrders()
    {
        return $this->objOrders;
    }

    /**
     * @param integer $order The starting order.
     * @return ReorderAction Chainable
     */
    public function setStartingOrder($order)
    {
        $this->startingOrder = (int)$order;
        return $this;
    }

    /**
     * @return integer
     */
    public function startingOrder()
    {
        return $this->startingOrder;
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $this->setData($request->getParams());

        $proto = $this->obj();

        $objOrders = $this->objOrders();
        $pos = $this->startingOrder();
        foreach ($objOrders as $orderId) {
            $q = 'update `'.$proto->source()->table().'` set `position` = :position where `'.$proto->key().'` = :id';
            $proto->source()->dbQuery($q, [
                'id' => $orderId,
                'position' => $pos
            ]);
            $pos++;
        }

        $this->setSuccess(true);
        return $response;

    }

    /**
     * @return array
     */
    public function results()
    {
        return [
            'success'           => $this->success(),
            'obj_orders'        => $this->objOrders(),
            'starting_order'    => $this->startingOrder(),
            'feedbacks'         => $this->feedbacks()
        ];
    }
}
