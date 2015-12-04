<?php

namespace Charcoal\Admin;

use \Exception;
use \InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Charcoal;
use \Charcoal\Translation\TranslationString;

// From `charcoal-app`
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
        //$this->metadata();
        if ($this->auth_required() !== false) {
            $this->auth();
        }

        if ($data === null) {
            $data = $_GET;
        } else {
            $data = array_merge_recursive($_GET, $data);
        }

        $this->set_data($data);

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
    * @throws InvalidArgumentException
    * @return AdminTemplate Chainable
    */
    public function set_show_header_menu($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show menu must be a boolean');
        }
        $this->show_header_menu = $show;
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
        return [
            [
                'active'=>true,
                'label'=>'Accueil',
                'icon'=>'home',
                'url'=>$this->admin_url().'home',
                'has_children'=>false
            ],
            [
                'active'=>true,
                'selected'=>true,
                'label'=>'Alertes',
                'icon'=>'alerts',
                'url'=>$this->admin_url().'object/collection?obj_type=alert/alert',
                'has_children'=>false
            ],
            [
                'active'=>true,
                'label'=>'Utilisateurs',
                'icon'=>'users',
                'url'=>$this->admin_url().'object/collection?obj_type=alert/user',
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
    private function auth_required()
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
            $path = Charcoal::config()->get('admin_path').'/login';
            try {
                // @todo Investigate why app()->redirect throws an exception
                //Charcoal::app()->response->withRedirect($path, 403);

            } catch (\Exception $e) {
                if (!headers_sent()) {
                    header('Location:'.Charcoal::app()->urlFor($path));
                    exit;
                }
            }
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
