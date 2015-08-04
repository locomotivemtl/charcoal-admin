<?php

namespace Charcoal\Admin\Action\Widget;

use \Exception as Exception;

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
    protected $_widget_id = '';
    protected $_widget_html = '';

    public function run()
    {
        $widget_type = Charcoal::app()->request->post('widget_type');
        $widget_options = Charcoal::app()->request->post('widget_options');

        if (!$widget_type) {
            $this->set_success(false);
            $this->output(404);
            return;
        }

        try {
            $widget = WidgetFactory::instance()->get($widget_type);

            if (is_array($widget_options)) {
                $widget->set_data($widget_options);
            }

            $this->_widget_html = $widget->render_template($widget_type);
            $this->_widget_id = $widget->widget_id();

            $this->set_success(true);
            $this->output();
        } catch (Exception $e) {
            //var_dump($e);
            $this->set_success(false);
            $this->output(404);
        }
    }

    public function response()
    {
        $success = $this->success();

        $response = [
            'success'=>$this->success(),
            'widget_html'=>$this->_widget_html,
            'widget_id'=>$this->_widget_id
        ];
        return $response;
    }
}
