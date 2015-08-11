<?php

namespace Charcoal\Admin\Ui;

// From `charcoal-core`
use \Charcoal\Translation\TranslationString as TranslationString;

/**
*
*/
class MenuItem
{
    protected $_ident;
    protected $_label;
    protected $_url;
    protected $_children;

    /**
    * Accept an array of data as constructor.
    *
    * @param array $data
    */
    final public function __construct(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }
    }

    /**
    * @param array $data
    * @return MenuItem Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['ident']) && $data['ident'] !== null) {
            $this->set_ident($data['ident']);
        }
        if (isset($data['label']) && $data['label'] !== null) {
            $this->set_label($data['label']);
        }
        if (isset($data['url']) && $data['url'] !== null) {
            $this->set_url($data['url']);
        }
        if (isset($data['children']) && $data['children'] !== null) {
            $this->set_children($data['children']);
        }

        return $this;
    }

    /**
    * @param string $ident
    * @throws InvalidArgumentException
    * @return MenuItem Chainable
    */
    public function set_ident($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException('Ident must a string');
        }
        $this->_ident = $ident;
        return $this;
    }

    /**
    * @return string
    */
    public function ident()
    {
        return $this->_ident;
    }

    /**
    * @param string $label
    * @return MenuItem Chainable
    */
    public function set_label($label)
    {
        $this->_label = new TranslationString($label);
        return $this;
    }

    /**
    * @return string
    */
    public function label()
    {
        return $this->_label;
    }

    /**
    * @param string $url
    * @return MenuItem Chainable
    */
    public function set_url($url)
    {
        $this->_url = $url;
        return $this;
    }

    /**
    * @return string
    */
    public function url()
    {
        return $this->_url;
    }

    /**
    * @return boolean
    */
    public function has_url()
    {
        return !!($this->url());
    }

    /**
    * @param array $children
    * @throws InvalidArgumentException
    * @return MenuItem Chainable
    */
    public function set_children($children)
    {
        if (!is_array($children)) {
            throw new InvalidArgumentException('Children must be an array');
        }
        $this->_children = [];
        foreach ($children as $c) {
            $this->add_child($c);
        }
        return $this;
    }

    /**
    * @param array|MenuItem $child
    * @throws InvalidArgumentException
    * @return MenuItem Chainable
    */
    public function add_child($child)
    {
        if (is_array($child)) {
            $c = new MenuItem($child);
            $this->_children[] = $c;
        } else if ($child instanceof MenuItem) {
            $this->children[] = $child;
        } else {
            throw new InvalidArgumentException('Child must be an array or a MenuItem object');
        }
        return $this;
    }

    /**
    * @return array
    */
    public function children()
    {
        return $this->_children;
    }

    /**
    * @return boolean
    */
    public function has_children()
    {
        return count($this->_children > 0);
    }

    /**
    * @return integer
    */
    public function num_children()
    {
        return count($this->_children);
    }
}
