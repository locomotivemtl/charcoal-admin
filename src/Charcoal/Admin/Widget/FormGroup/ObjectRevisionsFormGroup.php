<?php

namespace Charcoal\Admin\Widget\FormGroup;

// From 'pimple/pimple'
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Model\ModelFactoryTrait;

// From 'charcoal-object'
use Charcoal\Object\ObjectRevisionInterface;

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
        if ($this->objType === null) {
            $this->objType = filter_input(INPUT_GET, 'obj_type', FILTER_SANITIZE_STRING);
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
        if ($this->objId === null) {
            $this->objId = filter_input(INPUT_GET, 'obj_id', FILTER_SANITIZE_STRING);
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
    }
}
