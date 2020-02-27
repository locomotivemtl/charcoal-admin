<?php

namespace Charcoal\Admin\Widget;

// From 'charcoal-object'
use Charcoal\Object\ObjectRevisionInterface;

// From 'charcoal-admim'
use Charcoal\Admin\AdminWidget;
use Charcoal\Admin\Ui\ObjectRevisionsInterface;
use Charcoal\Admin\Ui\ObjectRevisionsTrait;

/**
 * Class ObjectRevisionWidget
 */
class ObjectRevisionsWidget extends AdminWidget implements
    ObjectRevisionsInterface
{
    use ObjectRevisionsTrait;

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
     * @param  string $objType ObjType for ObjectRevisionsWidget.
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
     * @param  string|integer $objId ObjId for ObjectRevisionsWidget.
     * @return self
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Retrieve the default data sources (when setting data on an entity).
     *
     * @return string[]
     */
    protected function defaultDataSources()
    {
        return [
            static::DATA_SOURCE_REQUEST,
            static::DATA_SOURCE_OBJECT,
        ];
    }

    /**
     * Retrieve the accepted metadata from the current request.
     *
     * @return array
     */
    protected function acceptedRequestData()
    {
        return array_merge([
            'obj_type',
            'obj_id',
            'template',
        ]);
    }
}
