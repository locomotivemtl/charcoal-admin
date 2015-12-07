<?php

namespace Charcoal\Admin\Action\Widget;

use \Exception;
use \InvalidArgumentException;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;


// From `charcoal-core`
use \Charcoal\Charcoal;

// From `charcoal-admin`
use \Charcoal\Widget\WidgetFactory;

// From `charcoal-admin`
use \Charcoal\Admin\AdminAction;

/**
*
*/
class LoadAction extends AdminAction
{
    /**
    * @var string $_widget_id
    */
    protected $widget_id = '';

    /**
    * @var string $_widget_html
    */
    protected $widget_html = '';

    /**
    * @param ServerRequestInterface $request
    * @param ResponseInterface $response
    * @return ResponseInterface
    */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $app = $this->app();
        $container = $app->getContainer();

        $widget_type = $request->getParam('widget_type');
        $widget_options = $request->getParam('widget_options');

        if (!$widget_type) {
            $this->set_success(false);
            $this->output(404);
            return $this->output($response->withStatus(404));
        }

        try {
            $widget_factory = new WidgetFactory();
            $widget = $widget_factory->create($widget_type, [

            ]);
            $widget->set_view($container['charcoal/view']);

            if (is_array($widget_options)) {
                $widget->set_data($widget_options);
            }
            $widget_html = $widget->render_template($widget_type);
            $widget_id = $widget->widget_id();

            $this->set_widget_html($widget_html);
            $this->set_widget_id($widget_id);

            $this->set_success(true);
            return $this->output($response);
        } catch (Exception $e) {
            //var_dump($e);
            $this->set_success(false);
            return $response->withStatus(404);
        }
    }

    /**
    * @param string $widget_id
    * @throws InvalidArgumentException
    * @return LoadAction Chainable
    */
    public function set_widget_id($id)
    {
        if (!is_string($id)) {
            throw new InvalidArgumentException(
                'Widget ID must be a string'
            );
        }
        $this->widget_id = $id;
        return $this;
    }

    /**
    * @return string
    */
    public function widget_id()
    {
        return $this->widget_id;
    }

    /**
    * @param string $widget_html
    * @throws InvalidArgumentException
    * @return LoadAction Chainable
    */
    public function set_widget_html($html)
    {
        if (!is_string($html)) {
            throw new InvalidArgumentException(
                'Widget HTML must be a string'
            );
        }
        $this->widget_html = $html;
        return $this;
    }

    /**
    * @return string
    */
    public function widget_html()
    {
        return $this->widget_html;
    }



    /**
    * @return string
    */
    public function results()
    {
        $success = $this->success();

        $results = [
            'success'=>$this->success(),
            'widget_html'=>$this->widget_html(),
            'widget_id'=>$this->widget_id(),
            'feedbacks'=>$this->feedbacks()
        ];
        return $results;
    }
}
