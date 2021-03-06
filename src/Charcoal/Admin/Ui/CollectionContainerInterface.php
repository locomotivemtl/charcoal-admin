<?php

namespace Charcoal\Admin\Ui;

/**
 * Collection Container Interface
 */
interface CollectionContainerInterface
{
    /**
     * @param string $objType The object type.
     * @return CollectionContainerInterface Chainable
     */
    public function setObjType($objType);

    /**
     * @return string
     */
    public function objType();

    /**
     * @param string $collectionIdent The collection identifier.
     * @return CollectionContainerInterface Chainable
     */
    public function setCollectionIdent($collectionIdent);

    /**
     * @return string|null
     */
    public function collectionIdent();

    /**
     * @param mixed $config The collection config.
     * @return CollectionContainerInterface Chainable
     */
    public function setCollectionConfig($config);

    /**
     * @return mixed
     */
    public function collectionConfig();

    /**
     * @return integer
     */
    public function page();

    /**
     * @return integer
     */
    public function numPerPage();

    /**
     * @return integer
     */
    public function numPages();

    /**
     * @param mixed $collection The collection stucture or object.
     * @return CollectionContainerInterface Chainable
     */
    public function setCollection($collection);

    /**
     * @return Collection
     */
    public function collection();

    /**
     * @return array
     */
    public function objects();

    /**
     * @return boolean
     */
    public function hasObjects();

    /**
     * @return integer
     */
    public function numObjects();

    /**
     * @return integer
     */
    public function numTotal();

    /**
     * @return object
     */
    public function proto();
}
