<?php

namespace Charcoal\Admin\Action\Widget;

use Exception;
use RuntimeException;
use InvalidArgumentException;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-view'
use Charcoal\View\ViewInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;

/**
 * Action: Build a widget.
 *
 * ## Required Parameters
 *
 * - `widget_type` (_string_) — The widget type, as an identifier for a {@see \Charcoal\App\Template\WidgetInterface}.
 *
 * ## Optional Parameters
 *
 * - `widget_options` (_array_) — Data to set on the built widget.
 *
 * ## Response
 *
 * - `success` (_boolean_) — TRUE if the widget was built, FALSE in case of any error.
 * - `widget_html` (_string_) — The widget's rendered view.
 * - `widget_id` (_string_) — The widget's ID.
 *
 * ## HTTP Status Codes
 *
 * - `200` — Successful; Widget built
 * - `400` — Client error; Invalid request data
 * - `500` — Server error; Widget could not be built
 */
class LoadAction extends AdminAction
{
    /**
     * The widget's current ID.
     *
     * @var string
     */
    protected $widgetId;

    /**
     * The widget's Data for JS.
     *
     * @var array|mixed
     */
    protected $widgetData;

    /**
     * The widget's current type.
     *
     * @var string
     */
    protected $widgetType;

    /**
     * The widget's renderered view.
     *
     * @var string
     */
    protected $widgetHtml;

    /**
     * Store the view renderer.
     *
     * @var ViewInterface
     */
    protected $widgetView;

    /**
     * Store the widget factory.
     *
     * @var FactoryInterface
     */
    protected $widgetFactory;

    /**
     * Execute the endpoint.
     *
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $previousId    = $request->getParam('widget_id');
        $widgetType    = $request->getParam('widget_type');
        $widgetOptions = $request->getParam('widget_options');
        $withData      = $request->getParam('with_data');

        if ($previousId) {
            $failMessage = $this->translator()->translation('Failed to reload widget');
        } else {
            $failMessage = $this->translator()->translation('Failed to load widget');
        }
        $errorThrown = strtr($this->translator()->translation('{{ errorMessage }}: {{ errorThrown }}'), [
            '{{ errorMessage }}' => $failMessage
        ]);
        $reqMessage  = $this->translator()->translation(
            '{{ parameter }} required, must be a {{ expectedType }}, received {{ actualType }}'
        );
        $typeMessage = $this->translator()->translation(
            '{{ parameter }} must be a {{ expectedType }}, received {{ actualType }}'
        );

        try {
            if (!$widgetType) {
                $actualType = is_object($widgetType) ? get_class($widgetType) : gettype($widgetType);
                $this->addFeedback('error', strtr($reqMessage, [
                    '{{ parameter }}'    => '"obj_type"',
                    '{{ expectedType }}' => 'string',
                    '{{ actualType }}'   => $actualType,
                ]));
                $this->setSuccess(false);

                return $response->withStatus(400);
            }

            $widget = $this->widgetFactory()->create($widgetType);
            $widget->setView($this->widgetView());

            if (isset($widgetOptions)) {
                if (!is_array($widgetOptions)) {
                    $actualType = is_object($widgetOptions) ? get_class($widgetOptions) : gettype($widgetOptions);
                    $this->addFeedback('error', strtr($typeMessage, [
                        '{{ parameter }}'    => '"widget_options"',
                        '{{ expectedType }}' => 'array',
                        '{{ actualType }}'   => $actualType,
                    ]));
                    $this->setSuccess(false);

                    return $response->withStatus(400);
                }

                if (!isset($widgetOptions['type'])) {
                    $widgetOptions['type'] = $widgetType;
                }

                $widget->setData($widgetOptions);
            }

            $widgetHtml = $widget->renderTemplate($widget->template());
            $widgetId   = $widget->widgetId();

            $this->setWidgetHtml($widgetHtml);
            $this->setWidgetId($widgetId);

            if ($withData) {
                $widgetData = $widget->widgetDataForJs();
                $this->setWidgetData($widgetData);
            }

            if ($previousId) {
                $doneMessage = $this->translator()->translation('Widget Reloaded');
            } else {
                $doneMessage = $this->translator()->translation('Widget Loaded');
            }
            $this->addFeedback('success', $doneMessage);
            $this->setSuccess(true);

            return $response;
        } catch (Exception $e) {
            $this->addFeedback('error', strtr($errorThrown, [
                '{{ errorThrown }}' => $e->getMessage()
            ]));
            $this->setSuccess(false);

            return $response->withStatus(500);
        }
    }

    /**
     * Set the widget's ID.
     *
     * @param  string $id The widget ID.
     * @throws InvalidArgumentException If the widget ID argument is not a string.
     * @return LoadAction Chainable
     */
    public function setWidgetId($id)
    {
        if (!is_string($id)) {
            throw new InvalidArgumentException(
                'Widget ID must be a string'
            );
        }

        $this->widgetId = $id;

        return $this;
    }

    /**
     * Retrieve the widget's ID.
     *
     * @return string
     */
    public function widgetId()
    {
        return $this->widgetId;
    }

    /**
     * Set the widget's DATA.
     *
     * @param array|mixed $widgetData WidgetData for LoadAction.
     * @return self
     */
    public function setWidgetData($widgetData)
    {
        $this->widgetData = $widgetData;

        return $this;
    }

    /**
     * Retrieve the widget's DATA.
     *
     * @return array|mixed
     */
    public function widgetData()
    {
        return $this->widgetData;
    }

    /**
     * Set the widget's type.
     *
     * @param  string $type The widget type.
     * @throws InvalidArgumentException If the widget type argument is not a string.
     * @return LoadAction Chainable
     */
    public function setWidgetType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Widget Type must be a string'
            );
        }

        $this->widgetType = $type;

        return $this;
    }

    /**
     * Retrieve the widget's type.
     *
     * @return string
     */
    public function widgetType()
    {
        return $this->widgetType;
    }

    /**
     * Set the widget's rendered view.
     *
     * @param string $html The widget HTML.
     * @throws InvalidArgumentException If the widget HTML is not a string.
     * @return LoadAction Chainable
     */
    public function setWidgetHtml($html)
    {
        if (!is_string($html)) {
            throw new InvalidArgumentException(
                'Widget HTML must be a string'
            );
        }

        $this->widgetHtml = $html;

        return $this;
    }

    /**
     * Retrieve the widget's rendered view.
     *
     * @return string
     */
    public function widgetHtml()
    {
        return $this->widgetHtml;
    }

    /**
     * @return array
     */
    public function results()
    {
        return [
            'success'       => $this->success(),
            'widget_html'   => $this->widgetHtml(),
            'widget_data'   => $this->widgetData(),
            'widget_id'     => $this->widgetId(),
            'feedbacks'     => $this->feedbacks()
        ];
    }

    /**
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setdependencies($container);

        $this->setWidgetFactory($container['widget/factory']);
        $this->setWidgetView($container['view']);
    }


    /**
     * Retrieve the widget renderer.
     *
     * @throws RuntimeException If the widget renderer was not previously set.
     * @return ViewInterface
     */
    protected function widgetView()
    {
        if (!isset($this->widgetView)) {
            throw new RuntimeException('Widget Renderer is not defined');
        }

        return $this->widgetView;
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
            throw new RuntimeException('Widget Factory is not defined');
        }

        return $this->widgetFactory;
    }

    /**
     * Set the widget renderer.
     *
     * @param  ViewInterface $view The view renderer to create widgets.
     * @return void
     */
    private function setWidgetView(ViewInterface $view)
    {
        $this->widgetView = $view;
    }

    /**
     * Set the widget factory.
     *
     * @param  FactoryInterface $factory The factory to create widgets.
     * @return void
     */
    private function setWidgetFactory(FactoryInterface $factory)
    {
        $this->widgetFactory = $factory;
    }
}
