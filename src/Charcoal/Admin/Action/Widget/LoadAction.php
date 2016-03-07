<?php

namespace Charcoal\Admin\Action\Widget;

use \Exception;
use \InvalidArgumentException;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From `pimple`
use \Pimple\Container;

// From `charcoal-app`
use \Charcoal\App\Template\WidgetFactory;

// From `charcoal-admin`
use \Charcoal\Admin\AdminAction;

/**
 *
 */
class LoadAction extends AdminAction
{
    /**
     * @var string $widgetId
     */
    protected $widgetId = '';

    /**
     * @var string $widgetHtml
     */
    protected $widgetHtml = '';

    protected $widgetView;

    public function setDependencies(Container $dependencies)
    {
        $this->widgetView = $dependencies['view'];
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {

        $widget_type = $request->getParam('widget_type');
        $widget_options = $request->getParam('widget_options');

        if (!$widget_type) {
            $this->setSuccess(false);
            return $response->withStatus(404);
        }

        try {
            $widget_factory = new WidgetFactory();
            $widget = $widget_factory->create($widget_type, [
                'logger'=>$this->logger
            ]);
            $widget->setView($this->widgetView);

            if (is_array($widget_options)) {
                $widget->setData($widget_options);
            }
            $widgetHtml = $widget->renderTemplate($widget_type);
            $widgetId = $widget->widgetId();

            $this->setWidgetHtml($widgetHtml);
            $this->setWidgetId($widgetId);

            $this->setSuccess(true);
            return $response;

        } catch (Exception $e) {
            $this->addFeedback(sprintf('An error occured trying to reload the widget: "%s"', $e->getMessage()), 'error');
            $this->addFeedback($e->getMessage(), 'error');
            $this->setSuccess(false);
            return $response->withStatus(404);
        }
    }

    /**
     * @param string $widgetId
     * @throws InvalidArgumentException
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
     * @return string
     */
    public function widgetId()
    {
        return $this->widgetId;
    }

    /**
     * @param string $widgetHtml
     * @throws InvalidArgumentException
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
     * @return string
     */
    public function widgetHtml()
    {
        return $this->widgetHtml;
    }

    /**
     * @return string
     */
    public function results()
    {
        $results = [
            'success'       => $this->success(),
            'widget_html'    => $this->widgetHtml(),
            'widget_id'      => $this->widgetId(),
            'feedbacks'     => $this->feedbacks()
        ];
        return $results;
    }
}
