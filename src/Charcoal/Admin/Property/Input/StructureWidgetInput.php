<?php

namespace Charcoal\Admin\Property\Input;

use \RuntimeException;
use \InvalidArgumentException;

// From Pimple
use \Pimple\Container;

// From Mustache
use \Mustache_LambdaHelper as LambdaHelper;

// From 'charcoal-factory'
use \Charcoal\Factory\FactoryInterface;

// From 'charcoal-ui'
use \Charcoal\Ui\FormGroup\FormGroupInterface;
use \Charcoal\Ui\FormInput\FormInputInterface;

// From 'charcoal-app'
use \Charcoal\App\Template\WidgetInterface;

// From 'charcoal-admin'
use \Charcoal\Admin\Property\AbstractPropertyInput;
use \Charcoal\Admin\Widget\FormGroup\StructureFormGroup;

/**
 *
 */
class StructureWidgetInput extends AbstractPropertyInput implements
    FormInputInterface
{
    /**
     * Store the widget factory instance for the current class.
     *
     * @var FactoryInterface
     */
    protected $widgetFactory;

    /**
     * Store the form group widget factory instance for the current class.
     *
     * @var FactoryInterface
     */
    protected $formGroupFactory;

    /**
     * Store the structure widget instance.
     *
     * @var WidgetInterface
     */
    protected $structureWidget;

    /**
     * The form group the input belongs to.
     *
     * @var FormGroupInterface
     */
    private $formGroup;

    /**
     * Settings for the structure widget.
     *
     * @var array
     */
    private $structureOptions;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setWidgetFactory($container['widget/factory']);
        $this->setFormGroupFactory($container['form/group/factory']);
    }

    /**
     * Set the widget factory.
     *
     * @param FactoryInterface $factory The factory to create widgets.
     * @return self
     */
    protected function setWidgetFactory(FactoryInterface $factory)
    {
        $this->widgetFactory = $factory;

        return $this;
    }

    /**
     * Retrieve the widget factory.
     *
     * @throws RuntimeException If the widget factory was not previously set.
     * @return FactoryInterface
     */
    protected function widgetFactory()
    {
        if (!isset($this->widgetFactory)) {
            throw new RuntimeException(
                sprintf('Widget Factory is not defined for "%s"', get_class($this))
            );
        }

        return $this->widgetFactory;
    }

    /**
     * Set the form group factory.
     *
     * @param FactoryInterface $factory The factory to create form groups.
     * @return self
     */
    protected function setFormGroupFactory(FactoryInterface $factory)
    {
        $this->formGroupFactory = $factory;

        return $this;
    }

    /**
     * Retrieve the form group factory.
     *
     * @throws RuntimeException If the form group factory was not previously set.
     * @return FactoryInterface
     */
    protected function formGroupFactory()
    {
        if (!isset($this->formGroupFactory)) {
            throw new RuntimeException(
                sprintf('Form Group Factory is not defined for "%s"', get_class($this))
            );
        }

        return $this->formGroupFactory;
    }

    /**
     * Set the form input's parent group.
     *
     * @param  FormGroupInterface $formGroup The parent form group object.
     * @return FormInputInterface Chainable
     */
    public function setFormGroup(FormGroupInterface $formGroup)
    {
        $this->formGroup = $formGroup;

        return $this;
    }

    /**
     * Retrieve the input's parent group.
     *
     * @return FormGroupInterface
     */
    public function formGroup()
    {
        return $this->formGroup;
    }

    /**
     * Yield the structure widget.
     *
     * @return WidgetInterface|FormGroupInterface|Generator
     */
    public function structureWidget()
    {
        $widget = $this->getStructureWidget();

        $GLOBALS['widget_template'] = $widget->template();

        yield $widget;
    }

    /**
     * Retrieve the structure widget.
     *
     * @return WidgetInterface|FormGroupInterface
     */
    protected function getStructureWidget()
    {
        if ($this->structureWidget === null) {
            $type = $this->structureOption('widget_type');

            if (is_subclass_of($type, FormGroupInterface::class)) {
                $widget = $this->formGroupFactory()->create($type);
            } else {
                $widget = $this->widgetFactory()->create($type);
            }

            $widget->setForm($this->formGroup()->form());
            $widget->setStorageProperty($this->property());

            $this->structureWidget = $widget;
        }

        return $this->structureWidget;
    }

    /**
     * Set the structure widget's options.
     *
     * This method always merges default settings.
     *
     * @param  array $settings The structure widget options.
     * @return StructureWidgetInput Chainable
     */
    public function setStructureOptions(array $settings)
    {
        $this->structureOptions = array_merge($this->defaultStructureOptions(), $settings);

        return $this;
    }

    /**
     * Merge (replacing or adding) structure widget options.
     *
     * @param  array $settings The structure widget options.
     * @return StructureWidgetInput Chainable
     */
    public function mergeStructureOptions(array $settings)
    {
        $this->structureOptions = array_merge($this->structureOptions, $settings);

        return $this;
    }

    /**
     * Add (or replace) an structure widget option.
     *
     * @param  string $key The setting to add/replace.
     * @param  mixed  $val The setting's value to apply.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return StructureWidgetInput Chainable
     */
    public function addStructureOption($key, $val)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Setting key must be a string.'
            );
        }

        // Make sure default options are loaded.
        if ($this->structureOptions === null) {
            $this->structureOptions();
        }

        $this->structureOptions[$key] = $val;

        return $this;
    }

    /**
     * Retrieve the structure widget's options.
     *
     * @return array
     */
    public function structureOptions()
    {
        if ($this->structureOptions === null) {
            $this->structureOptions = $this->defaultStructureOptions();
        }

        return $this->structureOptions;
    }

    /**
     * Retrieve an option from the widget's options.
     *
     * @param  string $key     The option key to lookup.
     * @param  mixed  $default The fallback value to return if the $key doesn't exist.
     * @return mixed
     */
    public function structureOption($key, $default = null)
    {
        $options = $this->structureOptions();

        if (isset($options[$key])) {
            return $options[$key];
        }

        return $default;
    }

    /**
     * Retrieve the default structure widget options.
     *
     * @return array
     */
    public function defaultStructureOptions()
    {
        return [
            'widget_type' => StructureFormGroup::class
        ];
    }

    /**
     * Retrieve the structure widget's options as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function structureOptionsAsJson()
    {
        return json_encode($this->structureOptions());
    }
}