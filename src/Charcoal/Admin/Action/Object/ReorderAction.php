<?php

namespace Charcoal\Admin\Action\Object;

use Exception;

// Dependencies from PSR-7 (HTTP Messaging)
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// Dependency from Pimple
use Pimple\Container;

// Intra-module (`charcoal-admin`) dependencies
use Charcoal\Admin\AdminAction;
use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Ui\ObjectContainerTrait;

/**
 * Reorder a collection of objects.
 *
 * ## Required Parameters
 *
 * - `obj_type`
 * - `obj_orders`
 *
 * ## Optional Parameters
 *
 * - `order_property`
 * - `start_order`
 *
 */
class ReorderAction extends AdminAction implements ObjectContainerInterface
{
    use ObjectContainerTrait;

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
        $objType       = $request->getParam('obj_type');
        $objOrders     = $request->getParam('obj_orders');
        $orderProperty = $request->getParam('order_property', 'position');
        $startingOrder = (int)$request->getParam('start_order');

        if (!$objType) {
            $this->setSuccess(false);
            $this->addFeedback('error', '"obj_type" required, must be a string');
            return $response->withStatus(400);
        }
        $this->setObjType($objType);

        if (!$objOrders || !is_array($objOrders)) {
            $this->setSuccess(false);
            $this->addFeedback('error', '"obj_orders" required, must be an array of object IDs');
            return $response->withStatus(400);
        }

        if (!is_string($orderProperty)) {
            $this->setSuccess(false);
            $this->addFeedback('error', sprintf(
                '"order_property" must be a string, received %s',
                (is_object($prop) ? get_class($prop) : gettype($prop))
            ));
            return $response->withStatus(400);
        }

        try {
            $proto = $this->obj();

            if (!$proto->hasProperty($orderProperty)) {
                $this->setSuccess(false);
                $this->addFeedback('error', sprintf(
                    'Missing "%s" property for sorting on [%s]',
                    $orderProperty,
                    $objType
                ));
                return $response->withStatus(400);
            }

            $pos = $startingOrder;
            $sql = 'UPDATE `%table` SET `%pos` = :pos WHERE `%key` = :id';
            $sql = strtr($sql, [
                '%table' => $proto->source()->table(),
                '%pos'   => $orderProperty,
                '%key'   => $proto->key()
            ]);

            foreach ($objOrders as $orderId) {
                $proto->source()->dbQuery($sql, [
                    'id'  => $orderId,
                    'pos' => $pos
                ]);

                $pos++;
            }

            $this->setSuccess(true);
            return $response;
        } catch (Exception $e) {
            $this->addFeedback('error', 'An error occured while sorting the objects');
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
            'success'   => $this->success(),
            'feedbacks' => $this->feedbacks()
        ];
    }
}
