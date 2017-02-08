<?php

namespace Charcoal\Admin\Ui;

/**
 * Defines awareness of a primary object.
 *
 * Implementation, as trait, provided by {@see \Charcoal\Admin\Ui\ObjectContainerTrait}.
 */
interface ObjectContainerInterface
{
    /**
     * @param string $objType The object type.
     * @return ObjectContainerInterface Chainable
     */
    public function setObjType($objType);

    /**
     * @return string
     */
    public function objType();

    /**
     * @param mixed $objId The object id, to load.
     * @return ObjectContainerInterface Chainable
     */
    public function setObjId($objId);

    /**
     * @return mixed
     */
    public function objId();

    /**
     * @return ModelInterface
     */
    public function obj();
}
