<?php

namespace Charcoal\Admin;

use \InvalidArgumentException as InvalidArgumentException;

// From PSR-7
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// From `charcoal-core`
use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Module\AbstractModule as AbstractModule;

// From `charcoal-base`
use \Charcoal\Template\TemplateView as TemplateView;
use \Charcoal\Action\ActionFactory as ActionFactory;

use \Charcoal\Admin\Config as AdminConfig;

/**
* The base class for the `admin` Module
*/
class AdminModule
{
    /**
    * @var AdminConfig $_config
    */
    private $_config;
    /**
    * @var \Slim\App $_app
    */
    private $_app;

    static public function setup($app)
    {
        // A session is necessary for the admin module
        if (session_id() === '') {
            session_start();
        }

        $container = $app->getContainer();
        $container['charcoal/admin/module'] = function($c) use ($app) {
            return new AdminModule([
                'config'=>$c['charcoal/admin/config'],
                'app'=>$app
            ]);
        };

        $container['charcoal/admin/config'] = function($c) {
            $config = new AdminConfig();
            $config->set_data($c['config']->get('admin'));
            return $config;
        };

        // Admin module
        $app->get('/admin', 'charcoal/admin/module:default_route');
        $app->group('/admin', 'charcoal/admin/module:setup_routes');
    }

    public function __construct($data)
    {
        $this->_config = $data['config'];
        $this->_app = $data['app'];

        // Hack
        $metadata_path = realpath(__DIR__.'/../../../metadata/');
        $templates_path = realpath(__DIR__.'/../../../templates/');
        Charcoal::config()->add_metadata_path($metadata_path);
        Charcoal::config()->add_template_path($templates_path);
    }

    /**
    * @return Module Chainable
    */
    public function setup_routes()
    {
        $this->add_template_route('home');
        $this->add_template_route('contents');
        $this->add_template_route('login');

        $this->add_template_route('object/edit');
        $this->add_template_route('object/collection');

        // Admin catch-all (load template if it exists)
        $this->app()->get('/:actions+?', function ($actions = ['home'], ServerRequestInterface $request, ResponseInterface $response, $args = null) {
            $template = implode('/', $actions);
            $view = new TemplateView();
            $content = $view->from_ident('charcoal/admin/template/'.$template)->render();
            if ($content) {
                echo $content;
            } else {
                Charcoal::app()->halt(404, 'Admin Page not found');
            }
        });

        $this->add_action_route('login');
        $this->add_action_route('object/delete');
        $this->add_action_route('object/save');
        $this->add_action_route('object/update');
        $this->add_action_route('widget/load');
        $this->add_action_route('widget/table/inline');
        $this->add_action_route('widget/table/inlinemulti');
        $this->add_action_route('messaging/send-sms');

        return $this;
    }

    public function default_route(ServerRequestInterface $request, ResponseInterface $response, $args = null)
    {
        $view = new TemplateView();
        $content = $view->from_ident('charcoal/admin/template/home')->render();
        $response->write($content);
        return $response;
    }

    /**
    * @return Module Chainable
    */
    public function setup_cli_routes($app = null)
    {
        $this->_app = $app;
        // Admin catch-all (load template if it exists)
        $this->app()->get('/:actions+?', function ($actions = ['home']) {
            try {
                $action_ident = implode('/', $actions);
                $action = ActionFactory::instance()->get('charcoal/admin/action/cli/'.$action_ident);

            } catch (Exception $e) {
                die('Error: '.$e->getMessage());
            }
            $action->run();
        });

        return $this;
    }

    /**
    * Bind all the availables admin templates to their auto-ident get routes.
    *
    * Get the TemplateView whose ident (PHP class + json metadata) matches the GET request.
    *
    *
    * @param string $tpl The template route (ident)
    * @param array $args
    * @return Module Chainable
    */
    public function add_template_route($tpl)
    {
        $admin_path = $this->config()->base_path();
        $this->app()->get('/'.$tpl, function(ServerRequestInterface $request, ResponseInterface $response) use ($tpl) {
            $view = new TemplateView();
            $content = $view->from_ident('charcoal/admin/template/'.$tpl)->render();
            $response->write($content);
            return $response;
        })->setName($admin_path.'/'.$tpl);
        return $this;
    }

    /**
    *  Bind all available admin actions to their auto-ident post routes.
    *
    * @param string $tpl
    * @return Module Chainable
    */
    public function add_action_route($tpl)
    {
        $admin_path = $this->config()->base_path();
        $this->_app->post('/action/json/'.$tpl, function($actions = null) use ($tpl) {
            try {
                //$action = new \Charcoal\Admin\Action\Login();
                $action = ActionFactory::instance()->get('charcoal/admin/action/'.$tpl);
                $action->set_mode('json');
                $action->run();
            } catch (Exception $e) {
                die($e->getMessage());
            }
        });

        $this->_app->post('/action/'.$tpl, function($actions = null) use ($tpl) {

            try {
                //$action = new \Charcoal\Admin\Action\Login();
                $action = ActionFactory::instance()->get('charcoal/admin/action/'.$tpl);
                $action->set_mode('json');
                $action->run();
            } catch (Exception $e) {
                die($e->getMessage());
            }
        });
        return $this;
    }

    /**
    * @param array $args
    * @return Module Chainable
    */
    public function add_cli_route($tpl)
    {
        unset($tpl);
        $admin_path = $this->config()->base_path();
        Charcoal::app()->get('/:actions+', function($actions = []) {
            try {
                $action_ident = implode('/', $actions);
                $action = ActionFactory::instance()->get('charcoal/admin/action/cli/'.$action_ident);
                $action->run();
            } catch (Exception $e) {
                die($e->getMessage()."\n");
            }
        });
        return $this;
    }

    public function app()
    {
        return $this->_app;
    }

    /**
    * @return Config
    */
    public function config()
    {
        return $this->_config;
    }

}
