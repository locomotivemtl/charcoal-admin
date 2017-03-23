<?php

namespace Charcoal\Admin\Widget\FormGroup;

use Traversable;
use RuntimeException;
use InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-app'
use Charcoal\App\Template\WidgetInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Ui\ObjectContainerInterface;

// From 'charcoal-ui'
use Charcoal\Ui\FormGroup\AbstractFormGroup;

// From 'charcoal-view'
use Charcoal\View\ViewableInterface;

/**
 * Nested Widget Form Group
 *
 * Allows UI widgets to be embedded into a form group and rendered using the current object, if any.
 *
 * Based on {@link https://bitbucket.org/beneroch/charcoal-utils/src/faa819a/src/Utils/Widget/FormGroup/WidgetFormGroup.php `WidgetFormGroup`}
 * from _beneroch/charcoal-utils_.
 *
 * Usage:
 * ```json
 * {
 *     "title": "My Nested Collection",
 *     "type": "charcoal/admin/widget/form-group/nested-widget",
 *     "widget_data": {
 *         "type": "charcoal/admin/widget/table",
 *         "obj_type": "foobar/model/item",
 *         "collection_ident": "grouped",
 *         "sortable": true
 *     },
 *     "renderable_data": {
 *         "collection_config": {
 *             "filters": [
 *                 {
 *                     "property": "category",
 *                     "val": "{{ id }}"
 *                 }
 *             ],
 *             "list_actions": [
 *                 {
 *                     "ident": "create",
 *                     "url": "object/edit?obj_type=foobar/model/item&form_data[category]={{ id }}"
 *                 }
 *             ]
 *         }
 *     }
 * }
 * ```
 */
class NestedWidgetFormGroup extends AbstractFormGroup
{
    /**
     * @var string
     */
    protected $widgetId;

    /**
     * Store the nested widget.
     *
     * @var WidgetInterface
     */
    protected $widget;

    /**
     * Settings for the nested widget.
     *
     * @var array
     */
    private $widgetData;

    /**
     * @var array
     */
    protected $renderableData = [];

    /**
     * Store the widget factory instance for the current class.
     *
     * @var FactoryInterface
     */
    protected $widgetFactory;

    /**
     * Whether notes shoudl be display before or after the form fields.
     *
     * @var boolean
     */
    private $showNotesAbove = false;

    /**
     * @param  Container $container The DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setWidgetFactory($container['widget/factory']);

        // Satisfies ViewableInterface dependencies
        $this->setView($container['view']);
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
            throw new RuntimeException(sprintf(
                'Widget Factory is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->widgetFactory;
    }

    /**
     * Retrieve the widget's ID.
     *
     * @return string
     */
    public function widgetId()
    {
        if (!$this->widgetId) {
            $this->widgetId = uniqid();
        }

        return $this->widgetId;
    }

    /**
     * Set the widget's ID.
     *
     * @param  string $widgetId The widget identifier.
     * @return self
     */
    public function setWidgetId($widgetId)
    {
        $this->widgetId = $widgetId;

        return $this;
    }

    /**
     * Yield the nested widget.
     *
     * @return WidgetInterface|Generator
     */
    public function widget()
    {
        $widget = $this->getWidget();

        $GLOBALS['widget_template'] = $widget->template();

        yield $widget;
    }

    /**
     * Retrieve the nested widget.
     *
     * @throws InvalidArgumentException If the widget is invalid.
     * @return WidgetInterface
     */
    public function getWidget()
    {
        if ($this->widget === null) {
            $type = $this->widgetData('type');

            if ($this->widgetFactory()->isResolvable($type)) {
                $widget = $this->widgetFactory()->create($type);
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Invalid widget UI. Must be an instance of %s',
                    WidgetInterface::class
                ));
            }

            $widget->setData($this->widgetData());
            $widget->setData($this->renderableData());

            $this->widget = $widget;
        }

        return $this->widget;
    }

    /**
     * Set the nested widget's options.
     *
     * This method always merges default settings.
     *
     * @param  array $settings The nested widget options.
     * @return self
     */
    public function setWidgetData(array $settings)
    {
        $this->widgetData = array_merge($this->defaultWidgetData(), $settings);

        return $this;
    }

    /**
     * Merge (replacing or adding) nested widget options.
     *
     * @param  array $settings The nested widget options.
     * @return self
     */
    public function mergeWidgetData(array $settings)
    {
        $this->widgetData = array_merge($this->widgetData, $settings);

        return $this;
    }

    /**
     * Add (or replace) an nested widget option.
     *
     * @param  string $key The setting to add/replace.
     * @param  mixed  $val The setting's value to apply.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return self
     */
    public function addWidgetData($key, $val)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Setting key must be a string.'
            );
        }

        // Make sure default options are loaded.
        if ($this->widgetData === null) {
            $this->widgetData();
        }

        $this->widgetData[$key] = $val;

        return $this;
    }

    /**
     * Retrieve the nested widget's options or a single option.
     *
     * @param  string|null $key     The option key to lookup.
     * @param  mixed|null  $default The fallback value to return if the $key doesn't exist.
     * @return mixed
     */
    public function widgetData($key = null, $default = null)
    {
        if ($this->widgetData === null) {
            $this->widgetData = $this->defaultWidgetData();
        }

        if ($key) {
            if (isset($this->widgetData[$key])) {
                return $this->widgetData[$key];
            } else {
                if (!is_string($default) && is_callable($default)) {
                    return $default();
                } else {
                    return $default;
                }
            }
        }

        return $this->widgetData;
    }

    /**
     * Retrieve the default nested widget options.
     *
     * @return array
     */
    public function defaultWidgetData()
    {
        return [];
    }

    /**
     * Retrieve the nested widget's options as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function widgetDataAsJson()
    {
        return json_encode($this->widgetData());
    }

    /**
     * Retrieve the nested widget's renderable options.
     *
     * @return array
     */
    public function renderableData()
    {
        return $this->renderableData;
    }

    /**
     * Set the nested widget's renderable options.
     *
     * @param  array $data The data to render.
     * @return self
     */
    public function setRenderableData(array $data)
    {
        $this->renderableData = $this->renderDataRecursive($data);

        return $this;
    }

    /**
     * Render the given data recursively.
     *
     * @param  array|Traversable $data The data to render.
     * @throws InvalidArgumentException If the data is not iterable.
     * @throws RuntimeException If the form doesn't have a model.
     * @return array|Traversable The rendered data.
     */
    private function renderDataRecursive($data)
    {
        if (!is_array($data) && !($data instanceof Traversable)) {
            throw new InvalidArgumentException('The renderable data must be iterable.');
        }

        if (!$this->form() instanceof ObjectContainerInterface) {
            throw new RuntimeException(sprintf(
                'The [%s] widget has no data model.',
                static::CLASS
            ));
        }

        foreach ($data as $key => $val) {
            if (is_string($val)) {
                $data[$key] = $this->renderData($val);
            } elseif (is_array($val) || ($val instanceof Traversable)) {
                $data[$key] = $this->renderDataRecursive($val);
            } else {
                continue;
            }
        }

        return $data;
    }

    /**
     * Render the given data.
     *
     * @param  string $data The data to render.
     * @return string The rendered data.
     */
    private function renderData($data)
    {
        $obj = $this->form()->obj();

        // Make sure there's an "out"
        if ($obj instanceof ViewableInterface && $obj->view() !== null) {
            $data = $obj->view()->render($data, $obj->viewController());
        } else {
            $data = preg_replace_callback('~\{\{\s*(.*?)\s*\}\}~i', [ $this, 'parseDataToken' ], $data);
        }

        return $data;
    }

    /**
     * Parse the given slug (URI token) for the current object.
     *
     * @uses    self::filterDataToken() For customize the route value filtering,
     * @param   string|array $token The token to parse relative to the model entry.
     * @throws  InvalidArgumentException If a route token is not a string.
     * @return  string
     */
    private function parseDataToken($token)
    {
        // Processes matches from a regular expression operation
        if (is_array($token) && isset($token[1])) {
            $token = $token[1];
        }

        $token = trim($token);
        $method = [ $this, $token ];

        if (is_callable($method)) {
            $value = call_user_func($method);
            /** @see \Charcoal\Config\AbstractEntity::offsetGet() */
        } elseif (isset($this[$token])) {
            $value = $this[$token];
        } else {
            return '';
        }

        $value = $this->filterDataToken($value, $token);
        if (!is_string($value) && !is_numeric($value)) {
            throw new InvalidArgumentException(sprintf(
                'Data token "%1$s" must be a string with %2$s; received %3$s',
                $token,
                get_called_class(),
                (is_object($value) ? get_class($value) : gettype($value))
            ));
        }

        return $value;
    }

    /**
     * Filter the given value for a URI.
     *
     * @used-by self::parseDataToken() To resolve the token's value.
     * @param   mixed  $value A value to filter.
     * @param   string $token The parsed token.
     * @return  string The filtered $value.
     */
    private function filterDataToken($value, $token = null)
    {
        unset($token);

        if ($value instanceof \Closure) {
            $value = $value();
        }

        if ($value instanceof \DateTime) {
            $value = $value->format('Y-m-d-H:i');
        }

        if (method_exists($value, '__toString')) {
            $value = strval($value);
        }

        return $value;
    }

    /**
     * @return Translation|string|null
     */
    public function description()
    {
        return $this->renderTemplate((string)parent::description());
    }

    /**
     * @return Translation|string|null
     */
    public function notes()
    {
        return $this->renderTemplate((string)parent::notes());
    }

    /**
     * Show/hide the widget's notes.
     *
     * @param  boolean|string $show Whether to show or hide notes.
     * @return FormGroupWidget Chainable
     */
    public function setShowNotes($show)
    {
        $this->showNotesAbove = ($show === 'above');

        return parent::setShowNotes($show);
    }

    /**
     * @return boolean
     */
    public function showNotesAbove()
    {
        return $this->showNotesAbove;
    }
}