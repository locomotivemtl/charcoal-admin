<?php

namespace Charcoal\Admin;

use Exception;

// From PSR-7 (HTTP Messaging)
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-base
use Charcoal\User\Authenticator;
use Charcoal\User\Authorizer;

// From 'charcoal-translation'
use Charcoal\Translator\Translator;

// From 'charcoal-app'
use Charcoal\App\Template\AbstractTemplate;

// Local module (charcoal-admin) dependencies
use Charcoal\Admin\Ui\FeedbackContainerTrait;
use Charcoal\Admin\User\AuthAwareInterface;
use Charcoal\Admin\User\AuthAwareTrait;

/**
 * Base class for all `admin` Templates.
 *
 * An action extends
 *
 * # Available (mustache) methods
 * - `title` (Translation) - The page title
 * - `subtitle` (Translation) The page subtitle
 * - `showHeaderMenu` (bool) - Display the header menu or not
 * - `headerMenu` (iterator) - The header menu data
 * - `showFooterMenu` (bool) - Display the footer menu or not
 * - `footerMenu` (iterator) - The footer menu data
 */
class AdminTemplate extends AbstractTemplate implements AuthAwareInterface
{
    use AuthAwareTrait;
    use FeedbackContainerTrait;

    /**
     * The base URI.
     *
     * @var UriInterface
     */
    protected $baseUrl;

    /**
     * Store a reference to the admin configuration.
     *
     * @var \Charcoal\Admin\Config
     */
    protected $adminConfig;

    /**
     * @var \Charcoal\App\Config
     */
    protected $appConfig;

    /**
     * The name of the project.
     *
     * @var string|null
     */
    private $siteName;

    /**
     * @var string $ident
     */
    private $ident;

    /**
     * @var \Charcoal\Translator\Translation $label
     */
    protected $label;

    /**
     * @var \Charcoal\Translator\Translation $title
     */
    protected $title;

    /**
     * @var \Charcoal\Translator\Translation $subtitle
     */
    protected $subtitle;

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
     * @var FactoryInterface $modelFactory
     */
    private $modelFactory;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * Set common dependencies (services) used in all admin templates.
     *
     * @param Container $container DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setModelFactory($container['model/factory']);
        $this->setTranslator($container['translator']);

        $this->appConfig = $container['config'];
        $this->adminConfig = $container['admin/config'];
        $this->setBaseUrl($container['base-url']);
        $this->setSiteName($container['config']['project_name']);


        // AuthAware dependencies
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
            if (!$this->authenticator()->authenticate()) {
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
     * @param Translator $translator The translator service.
     * @return void
     */
    private function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return Translator
     */
    protected function translator()
    {
        return $this->translator;
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
        $this->label = $this->translator()->translation($label);
        return $this;
    }

    /**
     * @return \Charcoal\Translator\Translation
     */
    public function label()
    {
        return $this->label;
    }

    /**
     * Set the title of the page.
     *
     * @param  mixed $title Template title.
     * @return AdminTemplate Chainable
     */
    public function setTitle($title)
    {
        $this->title = $this->translator()->translation($label);

        return $this;
    }

    /**
     * Retrieve the title of the page.
     *
     * @return string|null
     */
    public function title()
    {
        if ($this->title === null) {
            return $this->siteName();
        }

        return $this->title;
    }

    /**
     * Set the page's sub-title.
     *
     * @param mixed $subtitle Template subtitle.
     * @return AdminTemplate Chainable
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $this->translator()->translation($subtitle);
        return $this;
    }

    /**
     * Retrieve the page's sub-title.
     *
     * @return \Charcoal\Translator\Translation
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

        $svgUri = $this->baseUrl().'assets/admin/images/svgs.svg#icon-';
        foreach ($headerMenu['items'] as $menuIdent => $menuItem) {
            if (isset($menuItem['active']) && $menuItem['active'] === false) {
                continue;
            }

            if (!isset($menuItem['ident'])) {
                $menuItem['ident'] = $menuIdent;
            }

            if (!empty($menuItem['url'])) {
                $url = $menuItem['url'];
                if ($url && strpos($url, ':') === false && !in_array($url[0], [ '/', '#', '?' ])) {
                    $url = $this->adminUrl().$url;
                }
            } else {
                $url = '';
            }
            $menuItem['url'] = $url;

            if (isset($menuItem['icon'])) {
                $icon = $menuItem['icon'];
                if ($icon && strpos($icon, ':') === false && !in_array($icon[0], [ '/', '#', '?' ])) {
                    $icon = $svgUri.$icon;
                }
            } else {
                $icon = $svgUri.'contents';
            }

            if (is_string($icon) && strpos($icon, '.svg') > 0) {
                unset($menuItem['icon']);
                $menuItem['svg'] = $icon;
            } else {
                unset($menuItem['svg']);
                $menuItem['icon'] = $icon;
            }

            if (isset($menuItem['label'])) {
                $menuItem['label'] = $this->translator()->translation($menuItem['label']);
            }

            $menuItem['show_label'] = (isset($menuItem['show_label']) ? !!$menuItem['show_label'] : true);

            $menuItem['selected'] = ($menuItem['ident'] === filter_input(INPUT_GET, 'main_menu'));

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
     * @param  mixed $sidemenuConfig The sidemenu widget ID or config.
     * @throws InvalidArgumentException If the sidemenu widget is invalid.
     * @return SidemenuWidgetInterface|null
     */
    protected function createSidemenu($sidemenuConfig = null)
    {
        if (empty($sidemenuConfig)) {
            $sidemenuFromRequest = filter_input(INPUT_GET, 'side_menu', FILTER_SANITIZE_STRING);
            $mainMenuFromRequest = filter_input(INPUT_GET, 'main_menu', FILTER_SANITIZE_STRING);

            if ($sidemenuFromRequest) {
                $sidemenuConfig = $sidemenuFromRequest;
            } elseif ($mainMenuFromRequest) {
                $sidemenuConfig = $mainMenuFromRequest;
            } else {
                return null;
            }
        }

        if (is_string($sidemenuConfig)) {
            $sidemenuConfig = [
                'widget_options' => [
                    'ident' => $sidemenuConfig
                ]
            ];
        } elseif (!is_array($sidemenuConfig)) {
            throw new InvalidArgumentException(
                'The sidemenu definition must be a sidemenu identifier or sidemenu structure.'
            );
        }

        $GLOBALS['widget_template'] = 'charcoal/admin/widget/sidemenu';

        if (isset($sidemenuConfig['widget_type'])) {
            $widgetType = $sidemenuConfig['widget_type'];
        } else {
            $widgetType = 'charcoal/admin/widget/sidemenu';
        }

        $sidemenu = $this->widgetFactory()->create($widgetType);

        if (isset($sidemenuConfig['widget_options'])) {
            $sidemenu->setData($sidemenuConfig['widget_options']);
        }

        return $sidemenu;
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
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return !!$this->authenticator()->authenticate();
    }

    /**
     * Retrieve the currently authenticated user.
     *
     * @return \Charcoal\User\UserInterface|null
     */
    public function getAuthenticatedUser()
    {
        return $this->authenticator()->authenticate();
    }

    /**
     * Retrieve the base URI of the administration area.
     *
     * @return string|UriInterface
     */
    public function adminUrl()
    {
        $adminPath = $this->adminConfig['base_path'];

        return rtrim($this->baseUrl(), '/').'/'.rtrim($adminPath, '/').'/';
    }

    /**
     * Set the base URI of the application.
     *
     * @param string|UriInterface $uri The base URI.
     * @return self
     */
    public function setBaseUrl($uri)
    {
        $this->baseUrl = $uri;

        return $this;
    }

    /**
     * Retrieve the base URI of the application.
     *
     * @return string|UriInterface
     */
    public function baseUrl()
    {
        return rtrim($this->baseUrl, '/').'/';
    }

    /**
     * @return string
     */
    public function headerMenuLogo()
    {
        if (isset($this->adminConfig['menu_logo'])) {
            if (is_string($this->adminConfig['menu_logo'])) {
                return $this->adminConfig['menu_logo'];
            }
        }

        return 'assets/admin/images/identicon.png';
    }

    /**
     * Set the name of the project.
     *
     * @param  string $name Name of the project.
     * @return AdminTemplate Chainable
     */
    protected function setSiteName($name)
    {
        $this->siteName = $this->translator()->translation($name);
        return $this;
    }

    /**
     * Retrieve the name of the project.
     *
     * @return string|null
     */
    public function siteName()
    {
        return $this->siteName;
    }

    /**
     * Retrieve the document title.
     *
     * @return \Charcoal\Translator\Translation
     */
    public function documentTitle()
    {
        $siteName  = $this->siteName();
        $pageTitle = strip_tags($this->title());

        if ($pageTitle) {
            if ($pageTitle === $siteName) {
                return sprintf('%1$s &#8212; Charcoal', $pageTitle);
            } else {
                return sprintf('%1$s &lsaquo; %2$s &#8212; Charcoal', $pageTitle, $siteName);
            }
        }

        return $siteName;
    }

    /**
     * Application Debug Mode.
     *
     * @return boolean
     */
    public function devMode()
    {
        if (!$this->appConfig) {
            return false;
        }

        $debug   = isset($this->appConfig['debug'])    ? $this->appConfig['debug']    : false;
        $devMode = isset($this->appConfig['dev_mode']) ? $this->appConfig['dev_mode'] : false;

        return $debug || $devMode;
    }

    /**
     * Retrieve the current language.
     *
     * @return string
     */
    public function lang()
    {
        return $this->translator()->getLocale();
    }

    /**
     * @return string
     */
    public function recaptchaKey()
    {
        $key = $this->appConfig['apis.google.recaptcha.public_key'] ?: $this->appConfig['apis.google.recaptcha.key'];

        return (string)$key;
    }
}
