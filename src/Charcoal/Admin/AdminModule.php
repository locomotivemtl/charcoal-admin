<?php

namespace Charcoal\Admin;

use \InvalidArgumentException as InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Module\AbstractModule as AbstractModule;

// From `charcoal-base`
use \Charcoal\Template\TemplateView as TemplateView;
use \Charcoal\Action\ActionFactory as ActionFactory;

use \Charcoal\Admin\Config as Config;

class AdminModule extends AbstractModule
{
    /**
    * @var Config $_config
    */
    private $_config;

    /**
    * @param array $data
    * @return Module Chainable
    */
    public function init($data = null)
    {
        // A session is necessary for the admin module
        if (session_id() === '') {
            session_start();
        }

        $this->_config = new Config();

        if (isset($data['config'])) {
            $this->set_config($data['config']);
        }

        $metadata_path = realpath(__DIR__.'/../../../metadata/');
        $templates_path = realpath(__DIR__.'/../../../templates/');
        Charcoal::config()->add_metadata_path($metadata_path);
        Charcoal::config()->add_template_path($templates_path);

        return $this;
    }

    /**
    * @var mixed $config
    * @throws InvalidArgumentException if config is not a string, array or Config object
    * @return Module Chainable
    */
    public function set_config($config)
    {
        if ($this->_config === null) {
            $this->config = new Config();
        }
        if (is_string($config)) {
            $this->_config->add_file($config);
        } else if (is_array($config)) {
            $this->_config->set_data($config);
        } else if (($config instanceof Config)) {
            $this->_config = $config;
        } else {
            throw new InvalidArgumentException('Config must be a string (filename), array (config data) or Config object');
        }
        return $this;
    }

    /**
    * @return Module Chainable
    */
    public function setup_routes()
    {
        $this->add_template_route('home');
        $this->add_template_route('login');

        // Admin catch-all (load template if it exists)
        Charcoal::app()->get('/:actions+?', function ($actions = ['home']) {
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

        return $this;
    }

    /**
    * @return Module Chainable
    */
    public function setup_cli_routes()
    {
        // Admin catch-all (load template if it exists)
        Charcoal::app()->get('/:actions+?', function ($actions = ['home']) {
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
    public function add_template_route($tpl, $args = null)
    {
        $admin_path = $this->config()->base_path();
        Charcoal::app()->get('/'.$tpl, function($args = null) use ($tpl) {
            $view = new TemplateView();
            $content = $view->from_ident('charcoal/admin/template/'.$tpl)->render();
            echo $content;
        })->name($admin_path.'/'.$tpl);
        return $this;
    }

    /**
    *  Bind all available admin actions to their auto-ident post routes.
    *
    * @param array $args
    * @return Module Chainable
    */
    public function add_action_route($tpl, $args = null)
    {
        $admin_path = $this->config()->base_path();
        Charcoal::app()->post('/action/json/'.$tpl, function($args = null) use ($tpl) {
            $action = new \Charcoal\Admin\Action\Login();
            $action->set_mode('json');
            $action->run();
        })->name($admin_path.'/action/json/'.$tpl);

        Charcoal::app()->post('/action/'.$tpl, function($args = null) use ($tpl) {
            $action = new \Charcoal\Admin\Action\Login();
            $action->run();
        })->name($admin_path.'/action/'.$tpl);
        return $this;
    }

    /**
    * @param array $args
    * @return Module Chainable
    */
    public function add_cli_route($tpl, $args = null)
    {
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

    /**
    * @return Config
    */
    public function config()
    {
        return $this->_config;
    }

}
