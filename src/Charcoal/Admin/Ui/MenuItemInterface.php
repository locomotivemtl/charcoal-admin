<?php

namespace Charcoal\Admin\Ui;

interface MenuItemInterface
{
    /**
    * @param array $data
    * @return MenuItemInterface Chainable
    */
    public function setData(array $data);

    /**
    * @param string $ident
    * @return MenuItem Chainable
    */
    public function setIdent($ident);

    /**
    * @return string
    */
    public function ident();

    /**
    * @param string $label
    * @return MenuItem Chainable
    */
    public function setLabel($label);

    /**
    * @return string
    */
    public function label();

    /**
    * @param string $url
    * @return MenuItem Chainable
    */
    public function setUrl($url);

    /**
    * @return string
    */
    public function url();

    /**
    * @return boolean
    */
    public function hasUrl();

    /**
    * @param array $children
    * @return MenuItem Chainable
    */
    public function setChildren($children);

    /**
    * @param array|MenuItem $child
    * @return MenuItem Chainable
    */
    public function addChild($child);

    /**
    * @return array
    */
    public function children();

    /**
    * @return boolean
    */
    public function hasChildren();

    /**
    * @return integer
    */
    public function numChildren();
}
