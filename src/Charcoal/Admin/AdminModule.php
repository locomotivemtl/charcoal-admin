<?php

namespace Charcoal\Admin;

// Dependencies from `PHP`
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
use \Charcoal\App\Module\AbstractModule;
use \Charcoal\App\Module\ModuleInterface;
use \Charcoal\App\Template\TemplateFactory;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Config as AdminConfig;

/**
* The base class for the `admin` Module
*/
class AdminModule extends AbstractModule implements
    ModuleInterface
{

    /**
    * @var AdminConfig $config
    */
    private $config;


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
    public function setup()
    {
        // A session is necessary for the admin module
        if (session_id() === '') {
            session_start();
        }

        // Hack
        $metadata_path = realpath(__DIR__.'/../../../metadata/');
        $templates_path = realpath(__DIR__.'/../../../templates/');
        Charcoal::config()->add_metadata_path($metadata_path);
        Charcoal::config()->add_template_path($templates_path);

        $container = $this->app()->getContainer();
        $container['charcoal/admin/module'] = function($c) {
            return new AdminModule([
                'logger'=>$c['logger'],
                'config'=>$c['charcoal/admin/config'],
                'app'=>$this->app()
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
            $loader->set_paths(Charcoal::config()->templates_path());
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
        $this->app()->get('/admin', 'charcoal/admin/module:default_route');
        $this->app()->group('/admin', 'charcoal/admin/module:setup_routes');
    }



    /**
    * @return Module Chainable
    */
    public function setup_routes2()
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

    public function default_route2(ServerRequestInterface $request, ResponseInterface $response, $args = null)
    {
        // Unused vars
        unset($request);
        unset($args);

        $c = $this->app()->getContainer();
        $view = $c['charcoal/view'];

        $type = 'charcoal/admin/template/home';

        $factory = new TemplateFactory();
        $context = $factory->create($type, [
            'app'=>$this->app()
        ]);
        $content = $view->render_template($type, $context);
        $response->write($content);
        return $response;
    }

    /**
    * @return Module Chainable
    */
    public function setup_cli_routes2()
    {
        // Admin catch-all (load template if it exists)
        $this->app()->get('/{actions:.*}', function ($req, $res, $args) {
            $factory = new ActionFactory();
            $action = $factory->get('charcoal/admin/action/cli/'.$args['actions']);
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


            $factory = new TemplateFactory();
            $context = $factory->create($type, [
                'app'=>$this
            ]);
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
            $factory = new ActionFactory();
            $action = $factory->create('charcoal/admin/action/'.$action_ident);
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
            $factory = new ActionFactory();
            $action = $factory->create('charcoal/admin/action/cli/'.$action_ident);

            $action->run();
        });
        return $this;
    }


    public function create_config(array $data = null)
    {
        $config = new AdminConfig();
        if($data !== null) {
            $config->set_data($data);
        }
        return $config;
    }

}
