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
     * @param mixed $collectionConfig The collection config.
     * @return CollectionContainerInterface Chainable
     */
    public function setCollectionConfig($collectionConfig);

    /**
     * @return mixed
     */
    public function collectionConfig();

    /**
     * @param integer $page The page number.
     * @return CollectionContainerInterface Chainable
     */
    public function setPage($page);

    /**
     * @return integer
     */
    public function page();

    /**
     * @param integer $numPerPage The number of items per page.
     * @return CollectionContainerInterface Chainable
     */
    public function setNumPerPage($numPerPage);

    /**
     * @return integer
     */
    public function numPerPage();

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
     * @return object
     */
    public function proto();
}
