<?php

namespace Charcoal\Admin\Action\Widget;

use \Exception as Exception;

// From PSR-7
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// From `charcoal-core`
use \Charcoal\Charcoal as Charcoal;

// From `charcoal-admin`
use \Charcoal\Widget\WidgetFactory as WidgetFactory;

// From `charcoal-admin`
use \Charcoal\Admin\AdminAction as AdminAction;

/**
*
*/
class LoadAction extends AdminAction
{
    /**
    * @var string $_widget_id
    */
    protected $_widget_id = '';
    /**
    * @var string $_widget_html
    */
    protected $_widget_html = '';

    public function set_data(array $data)
    {
        unset($data);
        return $this;
    }

    /**
    * Make the class callable
    *
    * @param ServerRequestInterface $request
    * @param ResponseInterface $response
    * @return ResponseInterface
    */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $this->run($request, $response);
    }

    /**
    * @param ServerRequestInterface $request
    * @param ResponseInterface $response
    * @return ResponseInterface
    */
    public function run(ServerRequestInterface $request, ResponseInterface $response)
    {
        $widget_type = $request->getParam('widget_type');
        $widget_options = $request->getParam('widget_options');

        if (!$widget_type) {
            $this->set_success(false);
            $this->output(404);
            return $this->output($response->withStatus(404));
        }

        try {
            $widget = WidgetFactory::instance()->get($widget_type);

            if (is_array($widget_options)) {
                $widget->set_data($widget_options);
            }

            $this->_widget_html = $widget->render_template($widget_type);
            $this->_widget_id = $widget->widget_id();

            $this->set_success(true);
            return $this->output($response);
        } catch (Exception $e) {
            //var_dump($e);
            $this->set_success(false);
            return $this->output($response->withStatus(404));
        }
    }

    public function response()
    {
        $success = $this->success();

        $response = [
            'success'=>$this->success(),
            'widget_html'=>$this->_widget_html,
            'widget_id'=>$this->_widget_id,
            'feedbacks'=>$this->feedbacks()
        ];
        return $response;
    }
}
