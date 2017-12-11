<?php

namespace Charcoal\Admin\Property\Input;

use RuntimeException;
use InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-ui'
use Charcoal\Ui\FormGroup\FormGroupInterface;
use Charcoal\Ui\FormInput\FormInputInterface;

// From 'charcoal-app'
use Charcoal\App\Template\WidgetInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractPropertyInput;
use Charcoal\Admin\Ui\NestedWidgetContainerInterface;
use Charcoal\Admin\Ui\NestedWidgetContainerTrait;

/**
 * Nested Widget Form Field
 *
 * Allows UI widgets to be embedded into a form field and rendered using the current object, if any.
 *
 * Based on {@see \Charcoal\Admin\Widget\FormGroup\NestedWidgetFormGroup}.
 */
class NestedWidgetInput extends AbstractPropertyInput implements
    FormInputInterface,
    NestedWidgetContainerInterface
{
    use NestedWidgetContainerTrait;

    /**
     * Store the widget factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $widgetFactory;

    /**
     * Store the form group widget factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $formGroupFactory;

    /**
     * The form group the input belongs to.
     *
     * @var FormGroupInterface
     */
    private $formGroup;q

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
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
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
        if ($this->widgetFactory === null) {
            throw new RuntimeException(sprintf(
                'Widget Factory is not defined for "%s"',
                get_class($this)
            ));
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
        if ($this->formGroupFactory === null) {
            throw new RuntimeException(sprintf(
                'Form Group Factory is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->formGroupFactory;
    }

    /**
     * Create the nested widget.
     *
     * @return WidgetInterface
     */
    protected function createWidget()
    {
        $type   = $this->widgetData('type');
        $widget = $this->resolveWidget($type);

        if (!$widget->ident()) {
            $widget->setIdent($this->propertyIdent());
        }

        if ($this instanceof FormInputInterface) {
            if ($widget instanceof FormGroupInterface) {
                $widget->setForm($this->formGroup()->form());
            }

            if ($widget instanceof FormInputInterface) {
                $widget->setFormGroup($this->formGroup());
            }
        } elseif ($this instanceof FormGroupInterface) {
            if ($widget instanceof FormGroupInterface) {
                $widget->setForm($this->form());
            }

            if ($widget instanceof FormInputInterface) {
                $widget->setFormGroup($this);
            }
        }

        $widget->setData($this->widgetData());
        $widget->setData($this->renderableData());

        return $widget;
    }

    /**
     * Resolve the nested widget.
     *
     * @param  string $type The widget to create.
     * @throws InvalidArgumentException If the widget is invalid.
     * @return WidgetInterface
     */
    protected function resolveWidget($type)
    {
        if ($this->formGroupFactory()->isResolvable($type)) {
            return $this->formGroupFactory()->create($type);
        } elseif ($this->widgetFactory()->isResolvable($type)) {
            return $this->widgetFactory()->create($type);
        } else {
            throw new InvalidArgumentException(sprintf(
                'Invalid widget UI. Must be an instance of %s',
                WidgetInterface::class
            ));
        }
    }
}
