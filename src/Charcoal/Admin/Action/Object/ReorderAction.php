<?php

namespace Charcoal\Admin\Action\Object;

use Exception;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;
use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Ui\ObjectContainerTrait;

/**
 * Action: Reorder a collection of objects.
 *
 * ## Required Parameters
 *
 * - `obj_type` (_string_) — The object type, as an identifier for a {@see \Charcoal\Model\ModelInterface}.
 * - `obj_orders` (_array_) — One or more object IDs to be sorted
 *
 * ## Optional Parameters
 *
 * - `order_property` (_string_) — The object property, for sorting, to update
 * - `start_order` (_integer_) — The initial value to increment from
 *
 * ## Response
 *
 * - `success` (_boolean_) — TRUE if the object(s) was/were reordered, FALSE in case of any error.
 *
 * ## HTTP Status Codes
 *
 * - `200` — Successful; Objects reordered
 * - `400` — Client error; Invalid request data
 * - `500` — Server error; Objects could not be reordered
 */
class ReorderAction extends AdminAction implements ObjectContainerInterface
{
    use ObjectContainerTrait;

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    protected function validDataFromRequest()
    {
        return array_merge([
            'obj_type', 'obj_id'
        ], parent::validDataFromRequest());
    }

    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $translator = $this->translator();

        $failMessage = $translator->translate('Failed to sort object(s)');
        $errorThrown = strtr($translator->translate('{{ errorMessage }}: {{ errorThrown }}'), [
            '{{ errorMessage }}' => $failMessage
        ]);
        $reqMessage = $translator->translate(
            '{{ parameter }} required, must be a {{ expectedType }}, received {{ actualType }}'
        );
        $typeMessage = $translator->translate(
            '{{ parameter }} must be a {{ expectedType }}, received {{ actualType }}'
        );

        $objType       = $request->getParam('obj_type');
        $objOrders     = $request->getParam('obj_orders');
        $orderProperty = $request->getParam('order_property', 'position');
        $startingOrder = (int)$request->getParam('start_order');

        try {
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
            $this->setObjType($objType);

            if (!$objOrders || !is_array($objOrders)) {
                $actualType = is_object($objOrders) ? get_class($objOrders) : gettype($objOrders);
                $this->addFeedback('error', strtr($reqMessage, [
                    '{{ parameter }}'    => '"obj_orders"',
                    '{{ expectedType }}' => 'array of object IDs',
                    '{{ actualType }}'   => $actualType,
                ]));
                $this->setSuccess(false);

                return $response->withStatus(400);
            }

            if (!is_string($orderProperty)) {
                $actualType = is_object($orderProperty) ? get_class($orderProperty) : gettype($orderProperty);
                $this->addFeedback('error', strtr($typeMessage, [
                    '{{ parameter }}'    => '"obj_property"',
                    '{{ expectedType }}' => 'string',
                    '{{ actualType }}'   => $actualType,
                ]));
                $this->setSuccess(false);

                return $response->withStatus(400);
            }

            $proto = $this->proto();

            if (!$proto->hasProperty($orderProperty)) {
                $feedback = $translator->translate('Missing "{{ propIdent }}" property for sorting on {{ objType }}');
                $this->addFeedback('error', strtr($feedback, [
                    '{{ propIdent }}' => $orderProperty,
                    '{{ objType }}'   => $objType
                ]));
                $this->setSuccess(false);

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

            if (count($objOrders) === 1) {
                $this->addFeedback('success', $translator->translate('Object has been successfully updated.'));
            } else {
                $this->addFeedback('success', $translator->translate('Objects have been successfully reordered.'));
            }
            $this->setSuccess(true);

            return $response;
        } catch (Exception $e) {
            $this->addFeedback('error', strtr($errorThrown, [
                '{{ errorThrown }}' => $e->getMessage()
            ]));
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

    /**
     * @param  Container $container A DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Fulfills `ObjectContainerTrait` dependencies.
        $this->setModelFactory($container['model/factory']);
    }
}
