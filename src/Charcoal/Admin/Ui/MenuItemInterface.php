<?php

namespace Charcoal\Admin\Ui;

interface MenuItemInterface
{
    /**
    * @param array $data
    * @return MenuItemInterface Chainable
    */
    public function set_data(array $data);

    /**
    * @param string $ident
    * @return MenuItem Chainable
    */
    public function set_ident($ident);

    /**
    * @return string
    */
    public function ident();

    /**
    * @param string $label
    * @return MenuItem Chainable
    */
    public function set_label($label);

    /**
    * @return string
    */
    public function label();

    /**
    * @param string $url
    * @return MenuItem Chainable
    */
    public function set_url($url);

    /**
    * @return string
    */
    public function url();

    /**
    * @return boolean
    */
    public function has_url();

    /**
    * @param array $children
    * @return MenuItem Chainable
    */
    public function set_children($children);

    /**
    * @param array|MenuItem $child
    * @return MenuItem Chainable
    */
    public function add_child($child);

    /**
    * @return array
    */
    public function children();

    /**
    * @return boolean
    */
    public function has_children();

    /**
    * @return integer
    */
    public function num_children();
}
