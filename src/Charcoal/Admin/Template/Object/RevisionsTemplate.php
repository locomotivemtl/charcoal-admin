<?php

namespace Charcoal\Admin\Template\Object;

// Local parent namespace dependencies
use \Charcoal\Admin\AdminTemplate;

/**
 *
 */
class RevisionsTemplate extends AdminTemplate
{
    /**
     * @var string $objType
     */
    protected $objType;
    /**
     * @var string $objId
     */
    protected $objId;

    /**
     * @param string $objType
     * @throws InvalidArgumentException if provided argument is not of type 'string'.
     * @return ObjectContainerInterface Chainable
     */
    public function setObjType($objType)
    {
        if (!is_string($objType)) {
            throw new InvalidArgumentException(
                'Obj type needs to be a string'
            );
        }
        $this->objType = str_replace(['.', '_'], '/', $objType);
        return $this;
    }

    /**
     * @return string
     */
    public function objType()
    {
        return $this->objType;
    }

    /**
     * @param string|numeric $objId
     * @throws InvalidArgumentException if provided argument is not of type 'scalar'.
     * @return ObjectContainerInterface Chainable
     */
    public function setObjId($objId)
    {
        if (!is_scalar($objId)) {
            throw new InvalidArgumentException(
                'Obj ID must be a string or numerical value.'
            );
        }
        $this->objId = $objId;
        return $this;
    }

    /**
     * Assign the Object ID
     *
     * @return string|numeric
     */
    public function objId()
    {
        return $this->objId;
    }
}
