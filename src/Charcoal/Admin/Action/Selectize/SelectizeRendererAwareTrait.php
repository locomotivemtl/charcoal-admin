<?php

namespace Charcoal\Admin\Action\Selectize;

// Dependencies from `charcoal-admin`
use Charcoal\Admin\Property\Input\SelectizeInput;
use Charcoal\Admin\Property\PropertyInputInterface;
use Charcoal\Admin\Service\SelectizeRenderer;

// Dependencies from `charcoal-factory`
use Charcoal\Factory\FactoryInterface;

// Dependencies from `charcoal-property`
use Charcoal\Property\PropertyInterface;

// PSR-7 dependencies
use RuntimeException;

trait SelectizeRendererAwareTrait
{
    /**
     * @var SelectizeRenderer
     */
    protected $selectizeRenderer;

    /**
     * Store the factory instance.
     *
     * @var FactoryInterface
     */
    protected $propertyInputFactory;

    /**
     * @var string
     */
    protected $selectizeObjType;

    /**
     * @var string
     */
    protected $selectizePropIdent;

    /**
     * @var PropertyInterface
     */
    protected $selectizeProperty;

    /**
     * @return PropertyInterface
     */
    protected function selectizeProperty()
    {
        if ($this->selectizeProperty) {
            return $this->selectizeProperty;
        }

        $objType = $this->selectizeObjType();
        $propertyIdent = $this->selectizePropIdent();

        if ($objType && $propertyIdent) {
            $model = $this->modelFactory()->create($objType);
            $prop = $model->property($propertyIdent);

            $this->selectizeProperty = $prop;
        }

        return $this->selectizeProperty;
    }

    /**
     * @return PropertyInputInterface
     */
    protected function selectizeInput()
    {
        $prop = $this->selectizeProperty();
        $type = isset($prop['input_type']) ? $prop['input_type'] : null;

        $input = $this->propertyInputFactory()->create($type);
        $input->setInputType($type);
        $input->setProperty($prop);
        $input->setData($prop->data());

        return $input;
    }

    /**
     * Retrieves the output from SelectizeInput::selectizeVal.
     *
     * @param mixed $val The value(s) to parse as selectize choices.
     * @return array
     */
    protected function selectizeVal($val)
    {
        if ($val === null) {
            return [];
        }

        $input = $this->selectizeInput();
        $choices = [];

        if ($input instanceof SelectizeInput) {
            $choices = $input->selectizeVal($val);
        }

        return $choices;
    }

    // ==========================================================================
    // SUPPORT
    // ==========================================================================

    /**
     * Set a property control factory.
     *
     * @param  FactoryInterface $factory The factory to create form controls for property values.
     * @return self
     */
    protected function setPropertyInputFactory(FactoryInterface $factory)
    {
        $this->propertyInputFactory = $factory;

        return $this;
    }

    /**
     * Retrieve the property control factory.
     *
     * @throws RuntimeException If the property control factory is missing.
     * @return FactoryInterface
     */
    public function propertyInputFactory()
    {
        if (!isset($this->propertyInputFactory)) {
            throw new RuntimeException(sprintf(
                'Property Control Factory is not defined for [%s]',
                get_class($this)
            ));
        }

        return $this->propertyInputFactory;
    }

    // ==========================================================================
    // GETTERS AND SETTERS
    // ==========================================================================

    /**
     * @param string $data The data set by setData().
     * @return $this
     */
    public function setSelectizeObjType($data)
    {
        $this->selectizeObjType = $data;

        return $this;
    }

    /**
     * @param string $data The data set by setData().
     * @return $this
     */
    public function setSelectizePropIdent($data)
    {
        $this->selectizePropIdent = $data;

        return $this;
    }

    /**
     * @return mixed
     */
    public function selectizeObjType()
    {
        return $this->selectizeObjType;
    }

    /**
     * @return mixed
     */
    public function selectizePropIdent()
    {
        return $this->selectizePropIdent;
    }

    /**
     * @return SelectizeRenderer
     */
    public function selectizeRenderer()
    {
        return $this->selectizeRenderer;
    }

    /**
     * @param SelectizeRenderer $selectizeRenderer Selectize renderer.
     * @return self
     */
    public function setSelectizeRenderer(SelectizeRenderer $selectizeRenderer)
    {
        $this->selectizeRenderer = $selectizeRenderer;

        return $this;
    }

    /**
     * @throws Exception If the model factory was not set before being accessed.
     * @return FactoryInterface
     */
    abstract protected function modelFactory();
}
