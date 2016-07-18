<?php

namespace Charcoal\Admin;

use \ArrayIterator;
use \Exception;
use \InvalidArgumentException;

// PSR-7 (HTTP Messaging) dependencies
use \Psr\Http\Message\RequestInterface;

// Dependency from `pimple/pimple` (Symfony DI Container)
use \Pimple\Container;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\FactoryInterface;

// Module `charcoal-base` dependencies
use \Charcoal\User\Authenticator;
use \Charcoal\User\Authorizer;

use \Charcoal\App\Template\AbstractTemplate;
use \Charcoal\Translation\TranslationString;

// Intra-module (`charcoal-admin`) dependency
use \Charcoal\Admin\User;

/**
 * Base class for all `admin` Templates.
 *
 * An action extends
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
     * Admin configuration
     * from main config['admin']
     * @var array
     */
    protected $adminConfig;

    /**
     * @var string $ident
     */
    private $ident;
    /**
     * @var TranslationString $label
     */
    private $label;

    /**
     * @var TranslationString $title
     */
    private $title;
    /**
     * @var TranslationString $subtitle
     */
    private $subtitle;

    /**
     * @var boolean $showHeaderMenu
     */
    private $showHeaderMenu = true;
    /**
     * @var boolean $showFooterMenu
     */
    private $showFooterMenu = true;
    /**
     * @var boolean $showTopHeaderMenu
     */
    private $showTopHeaderMenu;
    /**
     * @var boolean $headerMenu
     */
    private $headerMenu;

    /**
     * @var array $feedbacks
     */
    private $feedbacks;

    /**
     * @var \Charcoal\App\App $app
     */
    private $app;

    /**
     * @var FactoryInterface $modelFactory
     */
    private $modelFactory;

    /**
     * @var Authenticator $authenticator
     */
    private $authenticator;

    /**
     * @var Authorizer $authorizer
     */
    private $authorizer;

    /**
     * @return \Charcoal\App\App
     */
    private function app()
    {
        if ($this->app === null) {
            $this->logger->notice('App from singleton');
            $this->app = \Charcoal\App\App::instance();
        }
        return $this->app;
    }

    /**
     * Set common dependencies (services) used in all admin templates.
     *
     * @param Container $container DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->adminConfig = $container['charcoal/admin/config'];
        $this->setModelFactory($container['model/factory']);
        $this->setAuthenticator($container['admin/authenticator']);
        $this->setAuthorizer($container['admin/authorizer']);
    }

    /**
     * Template's init method is called automatically from `charcoal-app`'s Template Route.
     *
     * For admin templates, initializations is:
     *
     * - to start a session, if necessary
     * - to authenticate
     * - to initialize the template data with `$_GET`
     *
     * @param RequestInterface $request The request to initialize.
     * @return boolean
     * @see \Charcoal\App\Route\TemplateRoute::__invoke()
     */
    public function init(RequestInterface $request)
    {
        if (!session_id()) {
            session_cache_limiter(false);
            session_start();
        }

        if ($this->authRequired() !== false) {
            // This can reset headers / die if unauthorized.
            if (!$this->authenticator->authenticate()) {
                header('HTTP/1.0 403 Forbidden');
                header('Location: '.$this->adminUrl().'login');
                exit;
            }

            // Initialize data with GET
            $this->setData($request->getParams());

            // Test template vs. ACL roles
            $authUser = $this->authenticator()->authenticate();
            if (!$this->authorizer()->userAllowed($authUser, $this->requiredAclPermissions())) {
                header('HTTP/1.0 403 Forbidden');
                header('Location: '.$this->adminUrl().'login');
                exit;
            }
        } else {
            // Initialize data with GET
            $this->setData($request->getParams());
        }

        return parent::init($request);
    }

    /**
     * @return string[]
     */
    protected function requiredAclPermissions()
    {
        return [
            'template'
        ];
    }

    /**
     * As a convenience, all admin templates have a model factory to easily create objects.
     *
     * @param FactoryInterface $factory The factory used to create models.
     * @return void
     */
    protected function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
    }

    /**
     * @throws Exception If the factory is not set.
     * @return FactoryInterface The model factory.
     */
    protected function modelFactory()
    {
        if (!$this->modelFactory) {
            throw new Exception(
                sprintf('Model factory is not set for template "%s".', get_class($this))
            );
        }
        return $this->modelFactory;
    }

    /**
     * @param Authenticator $authenticator The authentication service.
     * @return void
     */
    protected function setAuthenticator(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * @return Authenticator
     */
    protected function authenticator()
    {
        return $this->authenticator;
    }

    /**
     * @param Authorizer $authorizer The authorization service.
     * @return void
     */
    protected function setAuthorizer(Authorizer $authorizer)
    {
        $this->authorizer = $authorizer;
    }

    /**
     * @return Authorizer
     */
    protected function authorizer()
    {
        return $this->authorizer;
    }

    /**
     * @param mixed $ident Template identifier.
     * @return AdminTemplate Chainable
     */
    public function setIdent($ident)
    {
        $this->ident = $ident;
        return $this;
    }

    /**
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * @param mixed $label Template label.
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
     * @param mixed $title Template title.
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
            $this->title = 'Undefined Title';
        }
        return $this->title;
    }

    /**
     * @param mixed $subtitle Template subtitle.
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
        return $this->subtitle;
    }

    /**
     * @param boolean $show The show header menu flag.
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
        return ($this->isAuthenticated() && $this->showHeaderMenu);
    }

    /**
     * Display or not the top right header menu.
     * @todo This is NOT used yet.
     * @param boolean $bool Display or not.
     * @return AdminTemplate Chainable.
     */
    public function setShowTopHeaderMenu($bool)
    {
        $this->showTopHeaderMenu = $bool;
        return $this;
    }

    /**
     * @todo Don't take the admin configuration that way...
     * @return boolean Show the top header menu or not.
     */
    public function showTopHeaderMenu()
    {
        $showTopHeaderMenu = $this->adminConfig['show_top_header_menu'];
        return $showTopHeaderMenu;
    }

    /**
     * Sets the top right header menu.
     * @param array $menu Menu as link and labels.
     * @return AdminTemplate Chainable.
     */
    public function setTopHeaderMenu(array $menu)
    {
        $this->topHeaderMenu = $menu;
        return $this;
    }

    /**
     * Header menu links and labels.
     * @todo To Do.
     * @return array The menu.
     */
    public function topHeaderMenu()
    {
        return [];
    }

    /**
     * @throws Exception If the menu was not properly configured.
     * @return array This method is a generator.
     */
    public function headerMenu()
    {
        $headerMenu = $this->adminConfig['header_menu'];

        if (!isset($headerMenu['items'])) {
            throw new Exception(
                'Header menu was not property configured.'
            );
        }

        foreach ($headerMenu['items'] as $menuItem) {
            if ($menuItem['url'] != '#') {
                $menuItem['url'] = $this->adminUrl().$menuItem['url'];
            }
            if (isset($menuItem['label'])) {
                $menuItem['label'] = new TranslationString($menuItem['label']);
            }
            yield $menuItem;
        }
    }

    /**
     * @param boolean $show The show footer menu flag.
     * @return AdminTemplate Chainable
     */
    public function setShowFooterMenu($show)
    {
        $this->showFooterMenu = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showFooterMenu()
    {
        return ($this->isAuthenticated() && $this->showFooterMenu);
    }

    /**
     * @return array
     */
    public function footerMenu()
    {
        return [];
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

    /**
     * @param string $level The feedback level.
     * @param mixed  $msg   The feedback message.
     * @return AdminTemplate Chainable
     */
    public function addFeedback($level, $msg)
    {
        $this->feedbacks[] = [
            'msg'=>$msg,
            'level'=>$level
        ];
        return $this;
    }

    /**
     * Determine if user authentication is required.
     *
     * Authentication is required by default. If unnecessary,
     * replace this method in the inherited template class.
     *
     * For example, the "Login" / "Reset Password" templates
     * should return `false`.
     *
     * @return boolean
     */
    protected function authRequired()
    {
        return true;
    }

    /**
     * Determine if the current user is authenticated.
     * Uses the Authenticator service.
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return !!$this->authenticator->authenticate();
    }

    /**
     * @return string
     */
    public function adminUrl()
    {
        $adminPath = $this->adminConfig['base_path'];

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

    /**
     * @return string
     */
    public function headerMenuLogo()
    {
        if (!isset($this->adminConfig['menu_logo'])) {
            return 'assets/admin/images/user_01.jpg';
        }

        if (!is_string($this->adminConfig['menu_logo'])) {
            return 'assets/admin/images/user_01.jpg';
        }

        return $this->adminConfig['menu_logo'];
    }
}
