<?php

namespace Charcoal\Admin;

use \Exception;
use \InvalidArgumentException;

// From PSR-7
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// From `charcoal-core`
use \Charcoal\Charcoal;
use \Charcoal\Model\ModelFactory;

// From `charcoal-app`
use \Charcoal\App\Action\ActionFactory;
use \Charcoal\App\Template\TemplateFactory;

use \Charcoal\Admin\Config as AdminConfig;

/**
* The base class for the `admin` Module
*/
class AdminModule
{
    /**
    * @var AdminConfig $config
    */
    private $config;

    /**
    * @var \Slim\App $app
    */
    private $app;

    /**
    * Charcoal admin setup.
    *
    * This module is bound to the `/admin` URL.
    *
    * ## Provides
    * - `charcoal/admin/module` An instance of this module
    *   - Exact type: `\Charcoal\Admin\AdminModule`
    *   - which implements `\Charcoal\Module\ModuleInterface`
    * - `charcoal/admin/config`
    * - `
    *
    * ## Dependencies
    * - `charcoal/config` Provided by \Charcoal\CharcoalModule
    *
    * @param \Slim\App $app
    * @return void
    */
    static public function setup(\Slim\App $app)
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
            $config->set_data($c['charcoal/config']->get('admin'));
            return $config;
        };

        $container['charcoal/view/config'] = function($c) {
            return new \Charcoal\View\ViewConfig($c['charcoal/config']->get('view'));
        };

        $container['charcoal/view/loader'] = function($c) {
            $loader = new \Charcoal\View\Mustache\MustacheLoader([
                'logger' => $c['logger']
            ]);
            $loader->set_search_path(Charcoal::config()->template_path());
            return $loader;
        };

        $container['charcoal/view/engine'] = function($c) {
            $engine = new \Charcoal\View\Mustache\MustacheEngine([
                'logger' => $c['logger'],
                'loader' =>$c['charcoal/view/loader']
            ]);
            return $engine;
        };

        $container['charcoal/view'] = function($c) {
            $view = new \Charcoal\View\GenericView([
                'config' => $c['charcoal/view/config'],
                'logger' => $c['logger']
            ]);
            $view->set_engine($c['charcoal/view/engine']);
            return $view;
        };

        // Admin module
        $app->get('/admin', 'charcoal/admin/module:default_route');
        $app->group('/admin', 'charcoal/admin/module:setup_routes');
    }

    /**
    * @param array $data Dependencies map
    */
    public function __construct($data)
    {
        $this->config = $data['config'];
        $this->app = $data['app'];

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

        $container = $this->app()->getContainer();
        $config = $this->config();

        $router_views = $config->get('routes/templates');
        foreach ($router_views as $view_ident => $view_options) {
            $this->add_template_route($view_ident);
        }

        $router_actions = $config->get('routes/actions');
        foreach ($router_actions as $action_ident => $action_options) {
            $this->add_action_route($action_ident);
        }

        return $this;
    }

    public function default_route(ServerRequestInterface $request, ResponseInterface $response, $args = null)
    {
        // Unused vars
        unset($request);
        unset($args);

        $c = $this->app->getContainer();
        $view = $c['charcoal/view'];

        $type = 'charcoal/admin/template/home';

        $context = TemplateFactory::instance()->create($type);
        $content = $view->render_template($type, $context);
        $response->write($content);
        return $response;
    }

    /**
    * @return Module Chainable
    */
    public function setup_cli_routes($app = null)
    {
        $this->app = $app;
        // Admin catch-all (load template if it exists)
        $app->get('/{actions:.*}', function ($req, $res, $args) {
            $action = ActionFactory::instance()->get('charcoal/admin/action/cli/'.$args['actions']);
            $action($req, $res);
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
    public function add_template_route($view_ident)
    {
        $admin_path = $this->config()->base_path();
        $this->app()->get('/'.$view_ident, function(ServerRequestInterface $request, ResponseInterface $response) use ($view_ident) {

            $c = $this->getContainer();
            $view = $c['charcoal/view'];

            $type = 'charcoal/admin/template/'.$view_ident;

            $context = TemplateFactory::instance()->create($type);
            $content = $view->render_template($type, $context);
            $response->write($content);
            return $response;
        })->setName($admin_path.'/'.$view_ident);

    }

    /**
    *  Bind all available admin actions to their auto-ident post routes.
    *
    * @param string $tpl
    * @return Module Chainable
    */
    public function add_action_route($action_ident)
    {
        $admin_path = $this->config()->base_path();
        $this->app()->post('/action/json/'.$action_ident, function(ServerRequestInterface $request, ResponseInterface $response, $args = null) use ($action_ident) {
            //$action = new \Charcoal\Admin\Action\Login();
            $action = ActionFactory::instance()->get('charcoal/admin/action/'.$action_ident);
            $action->set_mode('json');
            return $action($request, $response);
        });

        $this->app()->post('/action/'.$action_ident, function(ServerRequestInterface $request, ResponseInterface $response, $args = null) use ($action_ident) {

            //$action = new \Charcoal\Admin\Action\Login();
            $action = ActionFactory::instance()->get('charcoal/admin/action/'.$action_ident);
            $action->set_mode('json');
            return $action($request, $response);

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
            $action_ident = implode('/', $actions);
            $action = ActionFactory::instance()->get('charcoal/admin/action/cli/'.$action_ident);
            $action->run();
        });
        return $this;
    }

    public function app()
    {
        return $this->app;
    }

    /**
    * @return Config
    */
    public function config()
    {
        return $this->config;
    }

}
