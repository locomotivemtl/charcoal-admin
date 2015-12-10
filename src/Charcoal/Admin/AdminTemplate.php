<?php

namespace Charcoal\Admin;

use \Exception;
use \InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Charcoal;
use \Charcoal\Translation\TranslationString;

// From `charcoal-app`
use \Charcoal\App\App as CharcoalApp;
use \Charcoal\App\Template\AbstractTemplate;

use \Charcoal\Admin\AdminModule;
use \Charcoal\Admin\User;

/**
* Base class for all `admin` Templates.
*
* An action extends [\
*
* # Available (mustache) methods
* - `title` (TranslationString) - The page title
* - `subtitle` (TranslationString) The page subtitle
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
    * @var string $ident
    */
    private $ident = '';
    /**
    * @var TranslationString $label
    */
    private $label = '';

    /**
    * @var TranslationString $title
    */
    private $title = '';
    /**
    * @var TranslationString $subtitle
    */
    private $subtitle = '';

    /**
    * @var boolean $show_header_menu
    */
    private $show_header_menu = true;
    /**
    * @var boolean $show_footer_menu
    */
    private $show_footer_menu = true;

    /**
    * @var array $feedbacks
    */
    private $feedbacks;

    /**
    * Constructor.
    * Ensure authentication before serving the template.
    * @todo Check permissions
    *
    * @param arrray $data
    */
    public function __construct(array $data = null)
    {
        if (!session_id()) {
            session_cache_limiter(false);
            session_start();
        }

        parent::set_data($data);

        if ($this->auth_required() !== false) {
            $this->auth();
        }

        // Initialize data with GET
        $this->set_data($_GET);

    }

    /**
    * @var array $data
    * @return AdminTemplate Chainable
    */
    public function set_data(array $data)
    {
        foreach ($data as $prop => $val) {
            $func = [$this, 'set_'.$prop];

            if ($val === null) {
                continue;
            }

            if (is_callable($func)) {
                call_user_func($func, $val);
                unset($data[$prop]);
            } else {
                $this->{$prop} = $val;
            }
        }
        return $this;
    }

    /**
    * @param mixed $ident
    * @return AdminTemplate Chainable
    */
    public function set_ident($ident)
    {
        $this->ident = $ident;
        return $this;
    }

    /**
    * @param string
    */
    public function ident()
    {
        return $this->ident;
    }

    /**
    * @param mixed $label
    * @return AdminTemplate Chainable
    */
    public function set_label($label)
    {
        $this->label = new TranslationString($label);
        return $this;
    }

    /**
    * @return TranslationString
    */
    public function label()
    {
        return $this->label;
    }

    /**
    * @param mixed $title
    * @return AdminTemplate Chainable
    */
    public function set_title($title)
    {
        $this->title = new TranslationString($title);
        return $this;
    }

    /**
    * @return TranslationString
    */
    public function title()
    {
        if ($this->title === null) {
            $this->title = 'Undefined title';
        }
        return $this->title;
    }

    /**
    * @param mixed $subtitle
    * @return AdminTemplate Chainable
    */
    public function set_subtitle($subtitle)
    {
        $this->subtitle = new TranslationString($subtitle);
        return $this;
    }

    /**
    * @return TranslationString
    */
    public function subtitle()
    {
        if ($this->subtitle === null) {
            $this->subtitle = 'Undefined title';
        }
        return $this->subtitle;
    }

    /**
    * @param boolean $show
    * @return AdminTemplate Chainable
    */
    public function set_show_header_menu($show)
    {
        $this->show_header_menu = !!$show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_header_menu()
    {
        return $this->show_header_menu;
    }

    /**
    * @return array
    */
    public function header_menu()
    {
        $obj_type = isset($_GET['obj_type']) ? $_GET['obj_type'] : '';

        $alert_selected = in_array($obj_type, ['alert/alert', 'alert/category', 'alert/']);
        $user_selected = in_array($obj_type, ['alert/user', 'alert/bulkuser']);
        $content_selected = in_array($obj_type, ['alert/faq', 'alert/text']);

        return [
            [
                'active'=>false,
                'label'=>'Accueil',
                'icon'=>'home',
                'url'=>$this->admin_url().'home',
                'has_children'=>false
            ],
            [
                'active'=>true,
                'selected'=>$alert_selected,
                'label'=>'Alertes',
                'icon'=>'alerts',
                'url'=>$this->admin_url().'object/collection?obj_type=alert/alert',
                'has_children'=>false
            ],
            [
                'active'=>true,
                'selected'=>$user_selected,
                'label'=>'Utilisateurs',
                'icon'=>'users',
                'url'=>$this->admin_url().'object/collection?obj_type=alert/user',
                'has_children'=>false
            ],
            [
                'active'=>true,
                'selected'=>$content_selected,
                'label'=>'Contenus',
                'icon'=>'contents',
                'url'=>$this->admin_url().'object/collection?obj_type=alert/faq',
                'has_children'=>false
            ],
            [
                'active'=>false,
                'label'=>'Statistiques',
                'icon'=>'stats',
                'url'=>'#',
                'has_children'=>false
            ],
            [
                'active'=>false,
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
        $this->show_footer_menu = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_footer_menu()
    {
        return $this->show_footer_menu;
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
        return $this->feedbacks;
    }

    public function add_feedback($level, $msg)
    {
        $this->feedbacks[] = [
            'msg'=>$msg,
            'level'=>$level
        ];
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
    private function auth()
    {
        //$cfg = AdminModule::config();
        $u = User::get_authenticated();
        if ($u === null) {
            header('Location: '.$this->admin_url().'login');
            exit;
        }
    }

    /**
    * @return string
    */
    public function admin_url()
    {
        return $this->base_url().'admin/';
    }

    /**
    * @return string
    */
    public function base_url()
    {
        return Charcoal::config()->get('URL');
    }

    public function for_loop()
    {
       $return = [];

       for ($i = 1; $i <= 10; $i++) {
           $return[$i] = new \ArrayIterator( array_combine( range(1, $i), range(1, $i) ) );
       }

       return $return;
   }

}
