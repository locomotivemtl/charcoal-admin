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
 * - `showHeaderMenu` (bool) - Display the header menu or not
 * - `headerMenu` (iterator) - The header menu data
 * - `showFooterMenu` (bool) - Display the footer menu or not
 * - `footerMenu` (iterator) - The footer menu data
 * - `hasFeedback` (bool) - If there is feedback to display or not
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
     * @var boolean $showHeaderMenu
     */
    private $showHeaderMenu = true;
    /**
     * @var boolean $showFooterMenu
     */
    private $showFooterMenu = true;

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

        parent::setData($data);

        if ($this->authRequired() !== false) {
            $this->auth();
        }

        // Initialize data with GET
        $this->setData($_GET);
    }

    /**
     * @param mixed $ident
     * @return AdminTemplate Chainable
     */
    public function setIdent($ident)
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
    public function setLabel($label)
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
    public function setTitle($title)
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
    public function setSubtitle($subtitle)
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
    public function setShowHeaderMenu($show)
    {
        $this->showHeaderMenu = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showHeaderMenu()
    {
        return $this->showHeaderMenu;
    }

    /**
     * @return array
     */
    public function headerMenu()
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
                'url'=>$this->adminUrl().'home',
                'has_children'=>false
            ],
            [
                'active'=>true,
                'selected'=>$alert_selected,
                'label'=>'Alertes',
                'icon'=>'alerts',
                'url'=>$this->adminUrl().'object/collection?obj_type=alert/alert',
                'has_children'=>false
            ],
            [
                'active'=>true,
                'selected'=>$user_selected,
                'label'=>'Utilisateurs',
                'icon'=>'users',
                'url'=>$this->adminUrl().'object/collection?obj_type=alert/user',
                'has_children'=>false
            ],
            [
                'active'=>true,
                'selected'=>$content_selected,
                'label'=>'Contenus',
                'icon'=>'contents',
                'url'=>$this->adminUrl().'object/collection?obj_type=alert/faq',
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
    public function setShowFooterMenu($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show menu must be a boolean');
        }
        $this->showFooterMenu = $show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showFooterMenu()
    {
        return $this->showFooterMenu;
    }

    /**
     * @return array
     */
    public function footerMenu()
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
    public function hasFeedbacks()
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

    public function addFeedback($level, $msg)
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
    protected function authRequired()
    {
        return false;
    }

    /**
     * Determine if the current user is authenticated. If not it redirects them to the login page.
     */
    private function auth()
    {
        //$cfg = AdminModule::config();
        $u = User::get_authenticated();
        if ($u === null) {
            header('Location: '.$this->adminUrl().'login');
            exit;
        }
    }

    /**
     * @return string
     */
    public function adminUrl()
    {
        $adminPath = $this->app()->getContainer()->get('charcoal/admin/config')->basePath();

        return rtrim($this->baseUrl(), '/').'/'.rtrim($adminPath, '/').'/';
    }

    /**
     * @return string
     */
    public function baseUrl()
    {
        $appConfig = $this->app()->config();

        if ($appConfig->has('URL')) {
            return $appConfig->get('URL');
        } else {
            $uri = $this->app()->getContainer()->get('request')->getUri();

            return rtrim($uri->getBaseUrl(), '/').'/';
        }
    }

    public function forLoop()
    {
        $return = [];

        for ($i = 1; $i <= 10; $i++) {
            $return[$i] = new \ArrayIterator(array_combine(range(1, $i), range(1, $i)));
        }

        return $return;
    }
}
