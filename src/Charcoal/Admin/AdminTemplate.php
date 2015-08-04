<?php

namespace Charcoal\Admin;

use \InvalidArgumentException as InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Charcoal as Charcoal;

// From `charcoal-base`
use \Charcoal\Template\AbstractTemplate as AbstractTemplate;

use \Charcoal\Admin\AdminModule as AdminModule;
use \Charcoal\Admin\User as User;

/**
* Base class for all `admin` Templates.
*
* An action extends [\
*
* # Available (mustache) methods
* - `title` (string) - The page title
* - `subtitle` (string) The page subtitle
* - `show_header_menu` (bool) - Display the header menu or not
* - `header_menu` (iterator) - The header menu data
* - `show_footer_menu` (bool) - Display the footer menu or not
* - `footer_menu` (iterator) - The footer menu data
* - `has_feedback` (bool) - If there is feedback to display or not
* - `feedback` (iterator) - The feedback data
*/
class AdminTemplate extends AbstractTemplate
{
    /**
    * @var string $_ident
    */
    protected $_ident = '';
    protected $_label = '';

    /**
    * @var mixed $_title
    */
    protected $_title = '';
    /**
    * @var mixed $_subtitle
    */
    protected $_subtitle = '';

    /**
    * @var boolean $_show_header_menu
    */
    protected $_show_header_menu = true;
    /**
    * @var boolean $_show_footer_menu
    */
    protected $_show_footer_menu = true;

    /**
    * Constructor.
    * Ensure authentication before serving the template.
    * @todo Check permissions
    *
    * @param arrray $data
    */
    public function __construct($data = null)
    {
        if (!session_id()) {
            session_cache_limiter(false);
            session_start();
        }
        $this->metadata();
        if ($this->auth_required() !== false) {
            $this->auth();
        }

        if ($data === null) {
            $data = $_GET;
        } else {
            $data = array_merge_recursive($_GET, $data);
        }

        parent::__construct($data);

    }

    /**
    * @var array $data
    * @return AdminTemplate Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['ident']) && $data['ident'] !== null) {
            $this->set_ident($data['ident']);
        }
        if (isset($data['label']) && $data['label'] !== null) {
            $this->set_label($data['label']);
        }
        if (isset($data['title']) && $data['title'] !== null) {
            $this->set_title($data['title']);
        }
        if (isset($data['subtitle']) && $data['subtitle'] !== null) {
            $this->set_subtitle($data['subtitle']);
        }

        if (isset($data['show_header_menu']) && $data['show_header_menu'] !== null) {
            $this->set_show_header_menu($data['show_header_menu']);
        }
        if (isset($data['show_footer_menu']) && $data['show_footer_menu'] !== null) {
            $this->set_show_footer_menu($data['show_footer_menu']);
        }

        return $this;
    }

    /**
    * @param mixed $ident
    * @return AdminTemplate Chainable
    */
    public function set_ident($ident)
    {
        $this->_ident = $ident;
        return $this;
    }

    public function ident()
    {
        return $this->_ident;
    }

    /**
    * @param mixed $label
    * @return AdminTemplate Chainable
    */
    public function set_label($label)
    {
        $this->_label = $label;
        return $this;
    }

    public function label()
    {
        return $this->_label;
    }

    /**
    * @param mixed $title
    * @return AdminTemplate Chainable
    */
    public function set_title($title)
    {
        $this->_title = $title;
        return $this;
    }

    public function title()
    {
        if ($this->_title === null) {
            $this->_title = 'Undefined title';
        }
        return $this->_title;
    }

    /**
    * @param mixed $subtitle
    * @return AdminTemplate Chainable
    */
    public function set_subtitle($subtitle)
    {
        $this->_subtitle = $subtitle;
        return $this;
    }

    public function subtitle()
    {
        if ($this->_subtitle === null) {
            $this->_subtitle = 'Undefined title';
        }
        return $this->_subtitle;
    }

    /**
    * @param boolean $show
    * @throws InvalidArgumentException
    * @return AdminTemplate Chainable
    */
    public function set_show_header_menu($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show menu must be a boolean');
        }
        $this->_show_header_menu = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_header_menu()
    {
        return $this->_show_header_menu;
    }

    /**
    * @return array
    */
    public function header_menu()
    {
        return [
            [
                'active'=>true,
                'label'=>'Accueil',
                'icon'=>'home',
                'url'=>'#',
                'has_children'=>false
            ],
            [
                'active'=>true,
                'selected'=>true,
                'label'=>'Alertes',
                'icon'=>'alerts',
                'url'=>'#',
                'has_children'=>false
            ],
            [
                'active'=>true,
                'label'=>'Utilisateurs',
                'icon'=>'users',
                'url'=>'#',
                'has_children'=>false
            ],
            [
                'active'=>true,
                'label'=>'Contenus',
                'icon'=>'contents',
                'url'=>'#',
                'has_children'=>false
            ],
            [
                'active'=>true,
                'label'=>'Statistiques',
                'icon'=>'stats',
                'url'=>'#',
                'has_children'=>false
            ],
            [
                'active'=>true,
                'label'=>'Configuration',
                'icon'=>'config',
                'url'=>'#',
                'has_children'=>false
            ]
        ];
    }

    /**
    * @param boolean $show
    * @throws InvalidArgumentException
    * @return AdminTemplate Chainable
    */
    public function set_show_footer_menu($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show menu must be a boolean');
        }
        $this->_show_footer_menu = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_footer_menu()
    {
        return $this->_show_footer_menu;
    }

    /**
    * @return array
    */
    public function footer_menu()
    {
        // @todo
        return [];
    }

    public function token()
    {
        throw new \Exception('Function not implemented.');
    }

    /**
    * @return boolean
    */
    public function has_feedbacks()
    {
        return (count($this->feedbacks()) > 0);
    }

    /**
    * @return array
    */
    public function feedbacks()
    {
        return [];
        /*return [[
            'level'=>'error',
            'msg'=>'Feedback test error'
        ]];*/
    }

    /**
    * Determine if user authentication is required.
    *
    * Authentication is required by default. If unnecessary,
    * replace this method in the inherited template class.
    *
    * For example, the "Login" / "Reset Password" templates
    * should return `FALSE`.
    *
    * @return boolean
    */
    protected function auth_required()
    {
        return true;
    }

    /**
    * Determine if the current user is authenticated. If not it redirects them to the login page.
    */
    protected function auth()
    {
        //$cfg = AdminModule::config();
        $u = User::get_authenticated();
        if ($u === null) {
            $path = Charcoal::config()->admin_path().'/login';
            try {
                // @todo Investigate why app()->redirect throws an exception
                Charcoal::app()->redirect(Charcoal::app()->urlFor($path), 403);
            } catch (\Exception $e) {
                if (!headers_sent()) {
                    header('Location:'.Charcoal::app()->urlFor($path));
                    exit;
                }
            }
        }
    }

    public function url()
    {
        return Charcoal::config()['URL'];
    }

    public function admin_url()
    {
        return $this->base_url().'admin/';
    }

    public function base_url()
    {
        return Charcoal::config()['URL'];
    }
}
