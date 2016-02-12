<?php

namespace Charcoal\Admin\Ui;

// Dependencies from `PHP`
use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Admin\Ui\FormGroupInterface;
use \Charcoal\Admin\Ui\FormPropertyInterface;

/**
*
*/
trait FormTrait
{
    /**
     * @var string $action
     */
    private $action = '';

    /**
     * @var string $method
     */
    private $method = 'post';

    /**
     * @var string $nextUrl
     */
    private $nextUrl = '';

    /**
     * @var array $groups
     */
    protected $groups = [];

    /**
     * @var array $formData
     */
    private $formData = [];
    /**
     * @var array $formProperties
     */
    private $formProperties = [];

    /**
     * @param string $action
     * @throws InvalidArgumentException
     * @return FormInterface Chainable
     */
    public function setAction($action)
    {
        if (!is_string($action)) {
            throw new InvalidArgumentException(
                'Action must be a string'
            );
        }
        $this->action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function action()
    {
        return $this->action;
    }

    /**
     * @param string $method Either "post" or "get"
     * @throws InvalidArgumentException
     * @return FormInterface Chainable
     */
    public function setMethod($method)
    {
        $method = strtolower($method);
        if (!in_array($method, ['post', 'get'])) {
            throw new InvalidArgumentException(
                'Method must be "post" or "get"'
            );
        }
        $this->method = $method;
        return $this;
    }

    /**
     * @return string Either "post" or "get"
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * @param string $url
     * @throws InvalidArgumentException if success is not a boolean
     * @return ActionInterface Chainable
     */
    public function setNextUrl($url)
    {
        if (!is_string($url)) {
            throw new InvalidArgumentException(
                'URL needs to be a string'
            );
        }

        $this->nextUrl = $url;
        return $this;
    }

    /**
     * @return boolean
     */
    public function nextUrl()
    {
        return $this->nextUrl;
    }

    /**
     * @param array $groups
     * @return FormInterface Chainable
     */
    public function setGroups(array $groups)
    {
        $this->groups = [];
        foreach ($groups as $groupIdent => $group) {
            $this->addGroup($groupIdent, $group);
        }
        return $this;
    }

    /**
     * @param string                   $groupIdent
     * @param array|FormGroupInterface
     * @throws InvalidArgumentException
     * @return FormInterface Chainable
     */
    public function addGroup($groupIdent, $group)
    {
        if (!is_string($groupIdent)) {
            throw new InvalidArgumentException(
                'Group ident must be a string'
            );
        }

        if (($group instanceof FormGroupInterface)) {
            $group->setForm($this);
            $this->groups[$groupIdent] = $group;
        } else if (is_array($group)) {
            $g = $this->createGroup($group);
            $this->groups[$groupIdent] = $g;
        } else {
            throw new InvalidArgumentException(
                'Group must be a Form Group object or an array'
            );
        }

        return $this;
    }

    /**
     * @param array|null $data
     * @return FormGroupInterface
     */
    abstract public function createGroup(array $data = null);

    /**
     * Group generator
     */
    public function groups()
    {
        $groups = $this->groups;
        if (!is_array($this->groups)) {
            yield null;
        } else {
            uasort($groups, ['self', 'sortGroupsByPriority']);
            foreach ($groups as $group) {
                $GLOBALS['widget_template'] = $group->widgetType();
                yield $group->ident() => $group;
            }
        }
    }

    /**
     * @return boolean
     */
    public function hasGroups()
    {
        return (count($this->groups) > 0);
    }

    /**
     * @return integer
     */
    public function numGroups()
    {
        return count($this->groups);
    }

    /**
     * To be called with uasort()
     *
     * @param FormGroupInterface $a
     * @param FormGroupInterface $b
     * @return integer Sorting value: -1, 0, or 1
     */
    static protected function sortGroupsByPriority(FormGroupInterface $a, FormGroupInterface $b)
    {
        $a = $a->priority();
        $b = $b->priority();

        if ($a == $b) {
            return 1;
// Should be 0?
        }

        return ($a < $b) ? (-1) : 1;
    }

    /**
     * @param array $formData
     * @return FormInterface Chainable
     */
    public function setFormData(array $formData)
    {
        $this->formData = $formData;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $val
     * @throws InvalidArgumentException
     * @return FormInterface Chainable
     */
    public function addFormData($key, $val)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Key must be a string'
            );
        }
        $this->formData[$key] = $val;
        return $this;
    }

    /**
     * @return array
     */
    public function formData()
    {
        return $this->formData;
    }

    /**
     * @param array $properties
     * @return FormInterface Chainable
     */
    public function setFormProperties(array $properties)
    {
        $this->formProperties = [];
        foreach ($properties as $propertyIdent => $property) {
            $this->addFormProperty($propertyIdent, $property);
        }
        return $this;
    }

    /**
     * @param string                      $propertyIdent
     * @param array|FormPropertyInterface
     * @throws InvalidArgumentException
     * @return FormInterface Chainable
     */
    public function addFormProperty($propertyIdent, $property)
    {
        if (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property ident must be a string'
            );
        }

        if (($property instanceof FormPropertyInterface)) {
            $this->formProperties[$propertyIdent] = $property;
        } else if (is_array($property)) {
            $p = $this->createFormProperty($property);
            $p->setPropertyIdent($propertyIdent);
            $this->formProperties[$propertyIdent] = $p;
        } else {
            throw new InvalidArgumentException(
                'Property must be a FormProperty object or an array'
            );
        }

        return $this;
    }

    /**
     * @param array|null $data
     * @return FormPropertyInterface
     */
    abstract public function createFormProperty(array $data = null);

    /**
     * Properties generator
     */
    public function formProperties()
    {
        $sidebars = $this->sidebars;
        if (!is_array($this->sidebars)) {
            yield null;
        } else {
            foreach ($this->formProperties as $prop) {
                if ($prop->active() === false) {
                    continue;
                }
                $GLOBALS['widget_template'] = $prop->inputType();
                yield $prop->propertyIdent() => $prop;
            }
        }
    }
}
