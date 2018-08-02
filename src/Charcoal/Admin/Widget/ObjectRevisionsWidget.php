<?php

namespace Charcoal\Admin\Widget;

use Charcoal\Admin\AdminWidget;

/**
 * Class ObjectRevisionWidget
 */
class ObjectRevisionsWidget extends AdminWidget
{
    /**
     * @var string
     */
    protected $objType;

    /**
     * @var string|integer
     */
    protected $objId;

    /**
     * @return boolean
     */
    public function active()
    {
        return parent::active() && $this->objType() && $this->objId();
    }

    /**
     * @return string
     */
    public function objType()
    {
        return $this->objType;
    }

    /**
     * @param string $objType ObjType for ObjectRevisionsWidget.
     * @return self
     */
    public function setObjType($objType)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * @return integer|string
     */
    public function objId()
    {
        return $this->objId;
    }

    /**
     * @param integer|string $objId ObjId for ObjectRevisionsWidget.
     * @return self
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * @return array
     */
    public function objectRevisions()
    {
        if (!$this->objType() || !$this->objId()) {
            return [];
        }

        $target = $this->modelFactory()->create($this->objType());
        $target->setId($this->objId());

        $lastRevision = $target->latestRevision();
        $propLabel    = '<span title="%1$s">%2$s</code>';

        $callback = function(&$obj) use ($lastRevision, $target, $propLabel) {
            $dataDiff = $obj->dataDiff();
            $obj->revTsDisplay = $obj->revTs()->format('Y-m-d H:i:s');
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

            $obj->allowRevert = ($lastRevision->revNum() != $obj->revNum());
        };

        return $target->allRevisions($callback);
    }

    /**
     * Retrieve the default data sources (when setting data on an entity).
     *
     * @return string[]
     */
    protected function defaultDataSources()
    {
        return [static::DATA_SOURCE_REQUEST, static::DATA_SOURCE_OBJECT];
    }

    /**
     * Retrieve the accepted metadata from the current request.
     *
     * @return array
     */
    protected function acceptedRequestData()
    {
        return array_merge(
            ['obj_type', 'obj_id', 'template']
        );
    }
}
