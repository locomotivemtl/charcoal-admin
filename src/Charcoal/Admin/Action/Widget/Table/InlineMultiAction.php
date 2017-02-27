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
 * Action: Inline editing of multiple objects in Table Widget
 *
 * Renders an HTML form for editing objects across table rows.
 *
 * ## Required Parameters
 *
 * - `obj_type` (_string_) — The object type, as an identifier for a {@see \Charcoal\Model\ModelInterface}.
 * - `obj_ids` (_mixed_) — One or more object IDs to edit
 *
 * ## Response
 *
 * - `success` (_boolean_) — TRUE if the object was properly deleted, FALSE in case of any error.
 * - `objects` (_array_) — Form controls grouped by object.
 *
 * ## HTTP Codes
 *
 * - `200` — Successful; Form widgets built
 * - `400` — Client error; Invalid request data
 * - `404` — Storage error; Object nonexistent IDs
 * - `500` — Server error; Form widgets could not be built
 */
class InlineMultiAction extends AdminAction
{
    /**
     * @var array $objects
     */
    protected $objects = [];

    /**
     * Store the widget factory.
     *
     * @var FactoryInterface
     */
    private $widgetFactory;

    /**
     * @param Container $container DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setWidgetFactory($container['widget/factory']);
    }

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
        $objIds  = $request->getParam('obj_ids');

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

        if (!$objIds || !is_array($objIds)) {
            $actualType = is_object($objIds) ? get_class($objIds) : gettype($objIds);
            $this->addFeedback('error', strtr($reqMessage, [
                '{{ parameter }}'    => '"obj_ids"',
                '{{ expectedType }}' => 'array of object IDs',
                '{{ actualType }}'   => $actualType,
            ]));
            $this->setSuccess(false);

            return $response->withStatus(400);
        }

        try {
            foreach ($objIds as $objId) {
                $obj = $this->modelFactory()->create($objType);
                $obj->load($objId);

                if (!$obj->id()) {
                    continue;
                }

                $objForm = [ 'id' => $obj->id() ];

                $form = $this->widgetFactory()->create(ObjectFormWidget::class);
                $form->set_objType($objType);
                $form->set_objId($objId);

                $formProperties = $form->formProperties();
                foreach ($formProperties as $propertyIdent => $formProperty) {
                    if (!$formProperty instanceof FormPropertyWidget) {
                        continue;
                    }

                    $property = $obj->property($propertyIdent);
                    $formProperty->setPropertyVal($property->val());
                    $formProperty->setProp($property);

                    $inputType = $formProperty->inputType();
                    $objForm['properties'][$propertyIdent] = $formProperty->renderTemplate($inputType);
                }

                if (!$objForm['properties']) {
                    continue;
                }

                $this->objects[] = $objForm;
            }

            if (!$this->objects) {
                throw new UnexpectedValueException('No editable properties.');
            }

            $this->addFeedback('success', $this->translator()->translate('Widgets Loaded'));
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
            'objects'   => $this->objects,
            'feedbacks' => $this->feedbacks()
        ];
    }

    /**
     * Set the widget factory.
     *
     * @param  FactoryInterface $factory The factory to create widgets.
     * @return self
     */
    protected function setWidgetFactory(FactoryInterface $factory)
    {
        $this->widgetFactory = $factory;

        return $this;
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
}
