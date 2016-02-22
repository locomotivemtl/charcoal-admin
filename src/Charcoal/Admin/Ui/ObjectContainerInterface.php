<?php

namespace Charcoal\Admin\Ui;

interface ObjectContainerInterface
{

    /**
     * @param string $objType
     * @return ObjectContainerInterface Chainable
     */
    public function setObjType($objType);

    /**
     * @return string
     */
    public function objType();

    /**
     * @param mixed $objId
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
