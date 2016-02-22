<?php

namespace Charcoal\Admin\Ui;

/**
 *
 */
interface FormInterface
{
    /**
     * @param string $action
     * @throws InvalidArgumentException
     * @return FormInterface Chainable
     */
    public function setAction($action);

    /**
     * @return string
     */
    public function action();

    /**
     * @param string $method Either "post" or "get"
     * @throws InvalidArgumentException
     * @return FormInterface Chainable
     */
    public function setMethod($method);

    /**
     * @return string Either "post" or "get"
     */
    public function method();

    /**
     * @param string $url
     * @throws InvalidArgumentException if success is not a boolean
     * @return ActionInterface Chainable
     */
    public function setNextUrl($url);

    /**
     * @return boolean
     */
    public function nextUrl();

    /**
     * @param array $groups
     * @return FormInterface Chainable
     */
    public function setGroups(array $groups);

    /**
     * @param string                   $group_ident
     * @param array|FormGroupInterface
     * @throws InvalidArgumentException
     * @return FormInterface Chainable
     */
    public function addGroup($groupIdent, $group);

    /**
     * Group generator
     */
    public function groups();

    /**
     * @param array $data
     * @return FormInterface Chainable
     */
    public function setFormData(array $data);

    /**
     * @param string $key
     * @param mixed  $val
     * @throws InvalidArgumentException
     * @return FormInterface Chainable
     */
    public function addFormData($key, $val);

    /**
     * @return array
     */
    public function formData();

    /**
     * @param array $properties
     * @return FormInterface Chainable
     */
    public function setFormProperties(array $properties);

    /**
     * @param string                   $propertyIdent
     * @param array|FormPropertyWidget
     * @throws InvalidArgumentException
     * @return FormInterface Chainable
     */
    public function addFormProperty($propertyIdent, $property);

    /**
     * @param array|null $data
     * @return FormPropertyInterface
     */
    public function createFormProperty(array $data = null);

    /**
     * Properties generator
     */
    public function formProperties();
}
