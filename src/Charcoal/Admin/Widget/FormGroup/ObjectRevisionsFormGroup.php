<?php

namespace Charcoal\Admin\Widget\FormGroup;

use \Pimple\Container;

use \Charcoal\Loader\CollectionLoader;

use \Charcoal\Ui\FormGroup\AbstractFormGroup;

use \Charcoal\Admin\Widget\TableWidget;

/**
 *
 */
class ObjectRevisionsFormGroup extends AbstractFormGroup
{
    /**
     * @var FactoryInterface $modelFactory
     */
    private $modelFatory;

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->modelFactory = $container['model/factory'];
    }

    /**
     * @return string
     */
    public function objType()
    {
        return $_GET['obj_type'];
    }

    /**
     * @return string
     */
    public function objId()
    {
        return $_GET['obj_id'];
    }

    /**
     * @return array
     */
    public function objectRevisions()
    {
        if (!$this->objType() || !$this->objId()) {
            return [];
        }

        $target = $this->modelFactory->create($this->objType());
        $target->setId($this->objId());

        $lastRevision = $target->latestRevision();

        $callback = function(&$obj) use ($lastRevision, $target) {
            $dataDiff = $obj->dataDiff();
            $obj->revTsDisplay = $obj->revTs()->format('Y-m-d H:i:s');
            $obj->numDiff = count($dataDiff);
            if (isset($dataDiff[0])) {
                $props = array_keys($dataDiff[0]);
                $props = array_diff($props, ['last_modified']);
                $propNames = [];
                foreach ($props as $p) {
                    $propNames[] = $target->p($p)->label();
                }
                $obj->changedProperties = implode(', ', $propNames);
            } else {
                $obj->changedProperties = '';
            }
            $obj->allowRevert = ($lastRevision->revNum() != $obj->revNum());
        };

        return $target->allRevisions($callback);
    }
}
