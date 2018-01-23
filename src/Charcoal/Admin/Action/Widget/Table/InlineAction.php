<?php

namespace Charcoal\Admin\Action\Widget\Table;

use Exception;
use RuntimeException;
use UnexpectedValueException;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;
use Charcoal\Admin\Widget\ObjectFormWidget;
use Charcoal\Admin\Widget\FormPropertyWidget;

/**
 * Action: Inline editing of one object in Table Widget
 *
 * Renders an HTML form for editing an object in a table row.
 *
 * ## Required Parameters
 *
 * - `obj_type` (_string_) — The object type, as an identifier for a {@see \Charcoal\Model\ModelInterface}.
 * - `obj_id` (_mixed_) — The object ID to edit
 *
 * ## Response
 *
 * - `success` (_boolean_) — TRUE if the object was properly deleted, FALSE in case of any error.
 * - `properties` (_array_) — A group of form controls.
 *
 * ## HTTP Codes
 *
 * - `200` — Successful; Form widget built
 * - `400` — Client error; Invalid request data
 * - `404` — Storage error; Object nonexistent ID
 * - `500` — Server error; Form widget could not be built
 */
class InlineAction extends AdminAction
{
    /**
     * @var array $inlineProperties
     */
    protected $inlineProperties = [];

    /**
     * Store the widget factory.
     *
     * @var FactoryInterface
     */
    protected $widgetFactory;

    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @throws UnexpectedValueException If there are no form controls.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $failMessage = $this->translator()->translation('Failed to load quick edit');
        $errorThrown = strtr($this->translator()->translation('{{ errorMessage }}: {{ errorThrown }}'), [
            '{{ errorMessage }}' => $failMessage
        ]);
        $reqMessage  = $this->translator()->translation(
            '{{ parameter }} required, must be a {{ expectedType }}, received {{ actualType }}'
        );
        $typeMessage = $this->translator()->translation(
            '{{ parameter }} must be a {{ expectedType }}, received {{ actualType }}'
        );

        $objType = $request->getParam('obj_type');
        $objId   = $request->getParam('obj_id');

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

        if (!$objId) {
            $actualType = is_object($objId) ? get_class($objId) : gettype($objId);
            $this->addFeedback('error', strtr($reqMessage, [
                '{{ parameter }}'    => '"obj_id"',
                '{{ expectedType }}' => 'string or numeric',
                '{{ actualType }}'   => $actualType,
            ]));
            $this->setSuccess(false);

            return $response->withStatus(400);
        }

        try {
            $obj = $this->modelFactory()->create($objType);
            $obj->load($objId);

            if (!$obj->id()) {
                $this->addFeedback('error', strtr($errorThrown, [
                    '{{ errorThrown }}' => $this->translator()->translate('No object found.')
                ]));
                $this->setSuccess(false);

                return $response->withStatus(404);
            }

            $form = $this->widgetFactory()->create(ObjectFormWidget::class);
            $form->setObjType($objType);
            $form->setObjId($objId);

            $formProperties = $form->formProperties();
            foreach ($formProperties as $propertyIdent => $formProperty) {
                // Safeguard type
                if (!$formProperty instanceof FormPropertyWidget) {
                    continue;
                }

                $property = $obj->property($propertyIdent);
                $formProperty->setPropertyVal($property->val());
                $formProperty->setProperty($property);

                $inputType = $formProperty->inputType();
                $this->inlineProperties[$propertyIdent] = $formProperty->renderTemplate($inputType);
            }

            if (!$this->inlineProperties) {
                throw new UnexpectedValueException('No editable properties.');
            }

            $this->addFeedback('success', $this->translator()->translate('Widget Loaded'));
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
            'success'    => $this->success(),
            'properties' => $this->inlineProperties,
            'feedbacks'  => $this->feedbacks()
        ];
    }

    /**
     * @param Container $container DI container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setWidgetFactory($container['widget/factory']);
    }

    /**
     * Retrieve the widget factory.
     *
     * @throws RuntimeException If the widget factory was not previously set.
     * @return FactoryInterface
     */
    protected function widgetFactory()
    {
        if (!isset($this->widgetFactory)) {
            throw new RuntimeException('Widget Factory is not defined');
        }

        return $this->widgetFactory;
    }

    /**
     * Set the widget factory.
     *
     * @param  FactoryInterface $factory The factory to create widgets.
     * @return void
     */
    private function setWidgetFactory(FactoryInterface $factory)
    {
        $this->widgetFactory = $factory;
    }
}
