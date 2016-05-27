<?php

namespace Charcoal\Admin\Action\Widget\Table;

// Dependencies from `PHP`
use \Exception;

// Dependencies from `pimple/pimple`
use \Pimple\Container;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Dependency from 'charcoal-factory'
use \Charcoal\Factory\FactoryInterface;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminAction;
use \Charcoal\Admin\Widget\ObjectForm;
use \Charcoal\Admin\Widget\FormProperty;

/**
 *
 */
class InlineMultiAction extends AdminAction
{
    /**
     * @var array $objects
     */
    protected $objects;

    /**
     * @var FactoryInterface $widgetFactory
     */
    private $widgetFactory;

    /**
     * @param Container $container DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Required ObjectContainerInterface dependencies
        $this->setWidgetFactory($container['widget/factory']);
    }

    /**
     * @param FactoryInterface $factory The widget factory, to create the dashboard and sidemenu widgets.
     * @return InlineAction Chainable
     */
    protected function setWidgetFactory(FactoryInterface $factory)
    {
        $this->widgetFactory = $factory;
        return $this;
    }

    /**
     * @throws Exception If the factory is not set.
     * @return FactoryInterface
     */
    protected function widgetFactory()
    {
        if ($this->widgetFactory === null) {
            throw new Exception(
                'Widget factory is not set on inline action widget.'
            );
        }
        return $this->widgetFactory;
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $objType = $request->getParam('obj_type');
        $objIds = $request->getParam('obj_ids');

        if (!$objType || !$objIds) {
            $this->setSuccess(false);
            return $response->withStatus(404);
        }

        try {
            $this->objects = [];
            foreach ($objIds as $objId) {
                $obj = $this->modelFactory()->create($objType);
                $obj->load($objId);
                if (!$obj->id()) {
                    continue;
                }

                $o = [];
                $o['id'] = $obj->id();

                $objForm = $this->widgetFactory()->create('charcoal/admin/widget/object-form');
                $objForm->set_objType($objType);
                $objForm->set_objId($objId);
                $formProperties = $objForm->formProperties();
                foreach ($formProperties as $propertyIdent => $property) {
                    if (!($property instanceof FormProperty)) {
                        continue;
                    }
                    $p = $obj->p($propertyIdent);
                    $property->setProperty_val($p->val());
                    $property->setProp($p);
                    $inputType = $property->inputType();
                    $o['inlineProperties'][$propertyIdent] = $property->renderTemplate($inputType);
                }
                $this->objects[] = $o;
            }
            $this->setSuccess(true);
            return $response;
        } catch (Exception $e) {
            $this->setSuccess(false);
            return $response->withStatus(404);
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
}
