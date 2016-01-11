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
use \Charcoal\App\App as CharcoalApp;
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

    public function create_config(array $data = null)
    {
        $config = new AdminConfig();
        if ($data !== null) {
            $config->set_data($data);
        }
        return $config;
    }

}
