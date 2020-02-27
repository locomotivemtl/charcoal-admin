<?php

namespace Charcoal\Admin\Ui;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-object'
use Charcoal\Object\ObjectRevisionInterface;

/**
 * An implementation, as Trait, of the {@see \Charcoal\Admin\Ui\ObjectRevisionsInterface}.
 */
trait ObjectRevisionsTrait
{
    /**
     * @return ObjectRevisionInterface[]
     */
    public function objectRevisions()
    {
        if (!$this->objType() || !$this->objId()) {
            return [];
        }

        $obj = $this->modelFactory()->create($this->objType());
        $obj->setId($this->objId());

        $lastRevision = $obj->latestRevision();
        $propLabel    = '<span title="%1$s">%2$s</code>';

        $callback = function(ObjectRevisionInterface &$revision) use ($lastRevision, $obj, $propLabel) {
            $dataDiff = $revision['dataDiff'];
            $revision->revTsDisplay = $revision['revTs']->format('Y-m-d H:i:s');
            $revision->revUserDisplay = $revision->p('revUser')->displayVal($revision['revUser']);
            $revision->numDiff = count($dataDiff);

            if (isset($dataDiff[0])) {
                $props = array_keys($dataDiff[0]);
                $props = array_diff($props, [
                    'created',
                    'created_by',
                    'last_modified',
                    'last_modified_by',
                    'created',
                    'createdBy',
                    'lastModified',
                    'lastModifiedBy',
                ]);

                $changedProps = [];
                $droppedProps = [];
                foreach ($props as $p) {
                    if ($obj->hasProperty($p)) {
                        $label = $obj->p($p)['label'];
                        $changedProps[] = sprintf($propLabel, $p, $label);
                    } else {
                        $label = ucwords(str_replace([ '.', '_' ], ' ', $p));
                        $droppedProps[] = sprintf($propLabel, $p, $label);
                    }
                }
                $revision->changedProperties = implode(', ', $changedProps);
                $revision->droppedProperties = implode(', ', $droppedProps);
            } else {
                $revision->changedProperties = '';
                $revision->droppedProperties = '';
            }

            $revision->allowRevert = ($lastRevision['revNum'] !== $revision['revNum']);
        };

        return $obj->allRevisions($callback);
    }

    /**
     * Retrieve the object type to be revised.
     *
     * @return string
     */
    abstract public function objType();

    /**
     * Retrieve the object ID to be revised.
     *
     * @return string||integer
     */
    abstract public function objId();

    /**
     * @return FactoryInterface
     */
    abstract protected function modelFactory();
}
