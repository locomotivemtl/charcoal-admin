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
     * Set the object type.
     *
     * @param  string $objType The object type.
     * @return ObjectContainerInterface Chainable
     */
    public function setObjType($objType);

    /**
     * Retrieve the object type.
     *
     * @return string|null
     */
    public function objType();

    /**
     * Set the object ID.
     *
     * @param  mixed $objId The object ID to load.
     * @return ObjectContainerInterface Chainable
     */
    public function setObjId($objId);

    /**
     * Retrieve the object ID.
     *
     * @return mixed
     */
    public function objId();

    /**
     * Determine if the class has a concrete object.
     *
     * @return boolean
     */
    public function hasObj();

    /**
     * Retrieve the object.
     *
     * @return ModelInterface
     */
    public function obj();
}
