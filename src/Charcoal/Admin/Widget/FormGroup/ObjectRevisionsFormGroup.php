<?php

namespace Charcoal\Admin\Widget\FormGroup;

// From 'pimple/pimple'
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Model\ModelFactoryTrait;

// From 'charcoal-admin'
use Charcoal\Admin\Ui\ObjectRevisionsInterface;
use Charcoal\Admin\Ui\ObjectRevisionsTrait;

// From 'charcoal-ui'
use Charcoal\Admin\Ui\ObjectContainerInterface;

// From 'locomotivemtl/charcoal-ui'
use Charcoal\Ui\FormGroup\AbstractFormGroup;

/**
 * Form Group: Object Revisions List
 */
class ObjectRevisionsFormGroup extends AbstractFormGroup implements
    ObjectRevisionsInterface
{
    use ObjectRevisionsTrait;
    use ModelFactoryTrait;

    /**
     * @var string
     */
    private $objType;

    /**
     * @var string|integer
     */
    private $objId;

    /**
     * @var string $widgetId
     */
    public $widgetId;


    /**
     * @param string $widgetId The widget identifier.
     * @return self
     */
    public function setWidgetId($widgetId)
    {
        $this->widgetId = $widgetId;

        return $this;
    }

    /**
     * @return string
     */
    public function widgetId()
    {
        if (!$this->widgetId) {
            $this->widgetId = 'widget_'.uniqid();
        }

        return $this->widgetId;
    }

    /**
     * @return boolean
     */
    public function active()
    {
        return parent::active() && $this->objType() && $this->objId();
    }


    /**
     * Retrieve the object type to be revised.
     *
     * @return string
     */
    public function objType()
    {
        if ($this->objType === null && $this->form() instanceof ObjectContainerInterface) {
            $this->objType = $this->form()->objType();
        }

        return $this->objType;
    }


    /**
     * Retrieve the object ID to be revised.
     *
     * @return string|integer
     */
    public function objId()
    {
        if ($this->objId === null && $this->form() instanceof ObjectContainerInterface) {
            $this->objId = $this->form()->objId();
        }

        return $this->objId;
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setModelFactory($container['model/factory']);

        $this->objType = $container['request']->getParam('obj_type');
        $this->objId = $container['request']->getParam('obj_id');
    }
}
