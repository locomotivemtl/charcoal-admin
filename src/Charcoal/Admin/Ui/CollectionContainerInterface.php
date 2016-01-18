<?php

namespace Charcoal\Admin\Ui;

interface CollectionContainerInterface
{
    /**
    * @param string $objType
    * @return CollectionContainerInterface Chainable
    */
    public function setObjType($objType);

    /**
    * @return string
    */
    public function objType();

    /**
    * @param string $collectionIdent
    * @throws InvalidArgumentException
    * @return CollectionContainerInterface Chainable
    */
    public function setCollectionIdent($collectionIdent);

    /**
    * @return string|null
    */
    public function collectionIdent();

    /**
    * @param mixed $dashboardConfig
    * @return CollectionContainerInterface Chainable
    */
    public function setCollectionConfig($dashboardConfig);

    /**
    * @return mixed
    */
    public function collectionConfig();

    /**
    * @param array $data
    * @return mixed
    */
    //public function createCollectionConfig($data = null);



    /**
    * @param integer $page
    * @throws InvalidArgumentException
    * @return CollectionContainerInterface Chainable
    */
    public function setPage($page);

    /**
    * @return integer
    */
    public function page();

    /**
    * @param integer $numPerPage
    * @throws InvalidArgumentException
    * @return CollectionContainerInterface Chainable
    */
    public function setNumPerPage($numPerPage);

    /**
    * @return integer
    */
    public function numPerPage();

    /**
    * @param mixed $collection
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
    * @return Object
    */
    public function proto();

}
