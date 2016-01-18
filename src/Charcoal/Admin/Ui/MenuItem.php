<?php

namespace Charcoal\Admin\Ui;

use \InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Translation\TranslationString;

/**
*
*/
class MenuItem
{
    /**
    * @var string $ident
    */
    protected $ident;
    /**
    * @var TranslationString $label
    */
    protected $label;
    /**
    * @var string $url
    */
    protected $url;
    /**
    * @var array $children
    */
    protected $children;

    /**
    * Accept an array of data as constructor.
    *
    * @param array $data
    */
    final public function __construct(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }
    }

    /**
    * @param array $data
    * @return MenuItem Chainable
    */
    public function setData(array $data)
    {
        if (isset($data['ident']) && $data['ident'] !== null) {
            $this->setIdent($data['ident']);
        }
        if (isset($data['label']) && $data['label'] !== null) {
            $this->setLabel($data['label']);
        }
        if (isset($data['url']) && $data['url'] !== null) {
            $this->setUrl($data['url']);
        }
        if (isset($data['children']) && $data['children'] !== null) {
            $this->setChildren($data['children']);
        }

        return $this;
    }

    /**
    * @param string $ident
    * @throws InvalidArgumentException
    * @return MenuItem Chainable
    */
    public function setIdent($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Ident must a string'
            );
        }
        $this->ident = $ident;
        return $this;
    }

    /**
    * @return string
    */
    public function ident()
    {
        return $this->ident;
    }

    /**
    * @param string $label
    * @return MenuItem Chainable
    */
    public function setLabel($label)
    {
        $this->label = new TranslationString($label);
        return $this;
    }

    /**
    * @return string
    */
    public function label()
    {
        return $this->label;
    }

    /**
    * @param string $url
    * @return MenuItem Chainable
    */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
    * @return string
    */
    public function url()
    {
        return $this->url;
    }

    /**
    * @return boolean
    */
    public function hasUrl()
    {
        return !!($this->url());
    }

    /**
    * @param array $children
    * @throws InvalidArgumentException
    * @return MenuItem Chainable
    */
    public function setChildren($children)
    {
        if (!is_array($children)) {
            throw new InvalidArgumentException(
                'Children must be an array'
            );
        }
        $this->children = [];
        foreach ($children as $c) {
            $this->addChild($c);
        }
        return $this;
    }

    /**
    * @param array|MenuItem $child
    * @throws InvalidArgumentException
    * @return MenuItem Chainable
    */
    public function addChild($child)
    {
        if (is_array($child)) {
            $c = new MenuItem($child);
            $this->children[] = $c;
        } else if ($child instanceof MenuItem) {
            $this->children[] = $child;
        } else {
            throw new InvalidArgumentException(
                'Child must be an array or a MenuItem object'
            );
        }
        return $this;
    }

    /**
    * @return array
    */
    public function children()
    {
        return $this->children;
    }

    /**
    * @return boolean
    */
    public function hasChildren()
    {
        return count($this->children > 0);
    }

    /**
    * @return integer
    */
    public function numChildren()
    {
        return count($this->children);
    }
}
