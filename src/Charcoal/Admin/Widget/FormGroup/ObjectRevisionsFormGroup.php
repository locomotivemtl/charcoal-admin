<?php

namespace Charcoal\Admin\Widget\FormGroup;

// From 'pimple/pimple'
use Pimple\Container;

// From 'locomotivemtl/charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'locomotivemtl/charcoal-ui'
use Charcoal\Ui\FormGroup\AbstractFormGroup;

/**
 * Form Group: Object Revisions List
 */
class ObjectRevisionsFormGroup extends AbstractFormGroup
{
    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $modelFactory;

    /**
     * @return boolean
     */
    public function active()
    {
        return parent::active() && $this->objType() && $this->objId();
    }

    /**
     * Retrieve the current object type from the GET parameters.
     *
     * @return string
     */
    public function objType()
    {
        return filter_input(INPUT_GET, 'obj_type', FILTER_SANITIZE_STRING);
    }

    /**
     * Retrieve the current object ID from the GET parameters.
     *
     * @return string
     */
    public function objId()
    {
        return filter_input(INPUT_GET, 'obj_id', FILTER_SANITIZE_STRING);
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
        $propLabel    = '<span title="%1$s">%2$s</code>';

        $callback = function(&$obj) use ($lastRevision, $target, $propLabel) {
            $dataDiff = $obj['dataDiff'];
            $obj->revTsDisplay = $obj['revTs']->format('Y-m-d H:i:s');
            $obj->numDiff = count($dataDiff);

            if (isset($dataDiff[0])) {
                $props = array_keys($dataDiff[0]);
                $props = array_diff($props, [ 'last_modified', 'last_modified_by' ]);

                $changedProps = [];
                $droppedProps = [];
                foreach ($props as $p) {
                    if ($target->hasProperty($p)) {
                        $label = $target->p($p)->label();
                        $changedProps[] = sprintf($propLabel, $p, $label);
                    } else {
                        $label = ucwords(str_replace([ '.', '_' ], ' ', $p));
                        $droppedProps[] = sprintf($propLabel, $p, $label);
                    }
                }
                $obj->changedProperties = implode(', ', $changedProps);
                $obj->droppedProperties = implode(', ', $droppedProps);
            } else {
                $obj->changedProperties = '';
                $obj->droppedProperties = '';
            }

            $obj->allowRevert = ($lastRevision['revNum'] != $obj['revNum']);
        };

        return $target->allRevisions($callback);
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

        $this->modelFactory = $container['model/factory'];
    }
}
