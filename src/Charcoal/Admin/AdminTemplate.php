<?php

namespace Charcoal\Admin;

use Exception;
use InvalidArgumentException;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-user'
use Charcoal\User\AuthAwareInterface;
use Charcoal\User\AuthAwareTrait;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;
use Charcoal\Translator\TranslatorAwareTrait;

// From 'charcoal-app'
use Charcoal\App\Template\AbstractTemplate;

// From 'charcoal-ui'
use Charcoal\Ui\Menu\GenericMenu;
use Charcoal\Ui\MenuItem\GenericMenuItem;

// From 'charcoal-admin'
use Charcoal\Admin\Ui\FeedbackContainerTrait;
use Charcoal\Admin\Support\AdminTrait;
use Charcoal\Admin\Support\BaseUrlTrait;
use Charcoal\Admin\Support\SecurityTrait;

/**
 * Base class for all `admin` Templates.
 *
 * # Available (mustache) methods
 * - `title` (Translation) - The page title
 * - `subtitle` (Translation) The page subtitle
 * - `showHeaderMenu` (bool) - Display the header menu or not
 * - `headerMenu` (iterator) - The header menu data
 * - `showSystemMenu` (bool) - Display the footer menu or not
 * - `systemMenu` (iterator) - The footer menu data
 */
class AdminTemplate extends AbstractTemplate implements
    AuthAwareInterface
{
    use AdminTrait;
    use AuthAwareTrait;
    use BaseUrlTrait;
    use FeedbackContainerTrait;
    use SecurityTrait;
    use TranslatorAwareTrait;

    /**
     * The name of the project.
     *
     * @var Translation|string|null
     */
    private $siteName;

    /**
     * @var string $ident
     */
    private $ident;

    /**
     * @var Translation|string|null $label
     */
    protected $label;

    /**
     * @var Translation|string|null $title
     */
    protected $title;

    /**
     * @var Translation|string|null $subtitle
     */
    protected $subtitle;

    /**
     * @var boolean
     */
    private $showSidemenu = true;

    /**
     * @var boolean
     */
    private $showHeaderMenu = true;

    /**
     * @var boolean
     */
    private $showSystemMenu = true;

    /**
     * @var boolean
     */
    private $showTopHeaderMenu;

    /**
     * @var boolean
     */
    protected $headerMenu;

    /**
     * @var boolean
     */
    protected $systemMenu;

    /**
     * @var SideMenuWidgetInterface
     */
    protected $sidemenu;

    /**
     * @var FactoryInterface $modelFactory
     */
    private $modelFactory;

    /**
     * @var FactoryInterface $widgetFactory
     */
    private $widgetFactory;

    /**
     * Template's init method is called automatically from `charcoal-app`'s Template Route.
     *
     * For admin templates, initializations is:
     *
     * - to start a session, if necessary
     * - to authenticate
     * - to initialize the template data with the PSR Request object
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

        $this->setDataFromRequest($request);

        if ($this->authRequired() !== false) {
            // Test template vs. ACL roles
            if (!$this->isAuthorized()) {
                header('HTTP/1.0 403 Forbidden');
                header('Location: '.$this->adminUrl().'login');
                exit;
            }
        }

        return parent::init($request);
    }

    /**
     * Sets the template data from a PSR Request object.
     *
     * @param  RequestInterface $request A PSR-7 compatible Request instance.
     * @return self
     */
    protected function setDataFromRequest(RequestInterface $request)
    {
        $keys = $this->validDataFromRequest();
        if (!empty($keys)) {
            $this->setData($request->getParams($keys));
        }

        return $this;
    }

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    protected function validDataFromRequest()
    {
        return [
            // HTTP Handling
            'next_url',
            // Navigation Menusa
            'header_menu_item', 'side_menu_item', 'system_menu_item',
        ];
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
     * @return Translation|string|null
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
        $this->title = $this->translator()->translation($title);

        return $this;
    }

    /**
     * Retrieve the title of the page.
     *
     * @return Translation|string|null
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
     * @return Translation|string|null
     */
    public function subtitle()
    {
        return $this->subtitle;
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
        $showTopHeaderMenu = $this->adminConfig('show_top_header_menu');
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
        return ($this->isAuthorized() && $this->showHeaderMenu);
    }

    /**
     * Yield the header menu.
     *
     * @return array|Generator
     */
    public function headerMenu()
    {
        if ($this->headerMenu === null) {
            $this->headerMenu = $this->createHeaderMenu();
        }

        foreach ($this->headerMenu as $menuIdent => $menuItem) {
            yield $menuIdent => $menuItem;
        }
    }

    /**
     * @param boolean $show The show footer menu flag.
     * @return AdminTemplate Chainable
     */
    public function setShowSystemMenu($show)
    {
        $this->showSystemMenu = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showSystemMenu()
    {
        return ($this->isAuthorized() && $this->showSystemMenu && (count($this->systemMenu()) > 0));
    }

    /**
     * @return array
     */
    public function systemMenu()
    {
        if ($this->systemMenu === null) {
            $this->systemMenu = $this->createSystemMenu();
        }

        return new \ArrayIterator($this->systemMenu);
    }

    /**
     * @param  boolean $show The show sidemenu flag.
     * @return AdminTemplate Chainable
     */
    public function setShowSidemenu($show)
    {
        $this->showSidemenu = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showSidemenu()
    {
        return ($this->isAuthorized() && $this->showSidemenu);
    }

    /**
     * Retrieve the sidemenu.
     *
     * @return \Charcoal\Admin\Widget\SidemenuWidgetInterface|null
     */
    public function sidemenu()
    {
        return $this->sidemenu;
    }

    /**
     * @return string
     */
    public function headerMenuLogo()
    {
        $logo = $this->adminConfig('menu_logo');
        if (!empty($logo)) {
            return $logo;
        }

        return 'assets/admin/images/identicon.png';
    }

    /**
     * Retrieve the name of the project.
     *
     * @return Translation|string|null
     */
    public function siteName()
    {
        return $this->siteName;
    }

    /**
     * Retrieve the document title.
     *
     * @return Translation|string|null
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
     * Retrieve the current language.
     *
     * @return string
     */
    public function lang()
    {
        return $this->translator()->getLocale();
    }

    /**
     * Retrieve the current language.
     *
     * @return string
     */
    public function locale()
    {
        $lang    = $this->lang();
        $locales = $this->translator()->locales();

        if (isset($locales[$lang]['locale'])) {
            $locale = $locales[$lang]['locale'];
            if (is_array($locale)) {
                $locale = implode(' ', $locale);
            }
        } else {
            $locale = 'en-US';
        }

        return $locale;
    }

    /**
     * Determine if a CAPTCHA test is available.
     *
     * For example, the "Login", "Lost Password", and "Reset Password" templates
     * can render the CAPTCHA test.
     *
     * @see    AdminAction::recaptchaEnabled() Duplicate
     * @return boolean
     */
    public function recaptchaEnabled()
    {
        $recaptcha = $this->appConfig('apis.google.recaptcha');

        return (!empty($recaptcha['public_key'])  || !empty($recaptcha['key'])) &&
               (!empty($recaptcha['private_key']) || !empty($recaptcha['secret']));
    }

    /**
     * Determine if the CAPTCHA test is invisible.
     *
     * Note: Charcoal's implementation of Google reCAPTCHA defaults to "invisible".
     *
     * @return boolean
     */
    public function recaptchaInvisible()
    {
        $recaptcha = $this->appConfig('apis.google.recaptcha');

        $hasInvisible = isset($recaptcha['invisible']);
        if ($hasInvisible && $recaptcha['invisible'] === true) {
            return true;
        }

        $hasSize = isset($recaptcha['size']);
        if ($hasSize && $recaptcha['size'] === 'invisible') {
            return true;
        }

        if (!$hasInvisible && !$hasSize) {
            return true;
        }

        return false;
    }

    /**
     * Alias of {@see self::recaptchaSiteKey()}.
     *
     * @deprecated
     * @return string|null
     */
    public function recaptchaKey()
    {
        return $this->recaptchaSiteKey();
    }

    /**
     * Retrieve the Google reCAPTCHA public (site) key.
     *
     * @throws RuntimeException If Google reCAPTCHA is required but not configured.
     * @return string|null
     */
    public function recaptchaSiteKey()
    {
        $recaptcha = $this->appConfig('apis.google.recaptcha');

        if (!empty($recaptcha['public_key'])) {
            return $recaptcha['public_key'];
        } elseif (!empty($recaptcha['key'])) {
            return $recaptcha['key'];
        }

        return null;
    }

    /**
     * Retrieve the parameters for the Google reCAPTCHA widget.
     *
     * @return string[]
     */
    public function recaptchaParameters()
    {
        $appConfig = $this->appConfig('apis.google.recaptcha');
        $tplConfig = $this->get('recaptcha_options') ?: [];

        $params = [
            'sitekey'  => $this->recaptchaSiteKey(),
            'badge'    => null,
            'type'     => null,
            'size'     => 'invisible',
            'tabindex' => null,
            'callback' => null,
        ];

        if ($this->recaptchaInvisible() === false) {
            $params['size'] = null;
        }

        foreach ($params as $key => $val) {
            if ($val === null || $val === '') {
                if (isset($tplConfig[$key])) {
                    $val = $tplConfig[$key];
                } elseif (isset($appConfig[$key])) {
                    $val = $appConfig[$key];
                }

                $params[$key] = $val;
            }
        }

        return $params;
    }

    /**
     * Generate a string representation of HTML attributes for the Google reCAPTCHA tag.
     *
     * @return string
     */
    public function recaptchaHtmlAttr()
    {
        $params = $this->recaptchaParameters();

        $attributes = [];
        foreach ($params as $key => $val) {
            if ($val !== null) {
                $attributes[] = sprintf('data-%s="%s"', $key, htmlspecialchars($val, ENT_QUOTES));
            }
        }

        return implode(' ', $attributes);
    }

    /**
     * Set common dependencies (services) used in all admin templates.
     *
     * @param Container $container DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Satisfies TranslatorAwareTrait dependencies
        $this->setTranslator($container['translator']);

        // Satisfies AuthAwareInterface + SecurityTrait dependencies
        $this->setAuthenticator($container['admin/authenticator']);
        $this->setAuthorizer($container['admin/authorizer']);

        // Satisfies AdminTrait dependencies
        $this->setDebug($container['config']);
        $this->setAppConfig($container['config']);
        $this->setAdminConfig($container['admin/config']);

        // Satisfies BaseUrlTrait dependencies
        $this->setBaseUrl($container['base-url']);
        $this->setAdminUrl($container['admin/base-url']);

        // Satisfies AdminTemplate dependencies
        $this->setSiteName($container['config']['project_name']);

        $this->setModelFactory($container['model/factory']);
        $this->setWidgetFactory($container['widget/factory']);

        $this->menuBuilder = $container['menu/builder'];
        $this->menuItemBuilder = $container['menu/item/builder'];
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
     * @throws Exception If the widget factory dependency was not previously set / injected.
     * @return FactoryInterface
     */
    protected function widgetFactory()
    {
        if ($this->widgetFactory === null) {
            throw new Exception(
                'Widget factory was not set.'
            );
        }
        return $this->widgetFactory;
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
     * @param  mixed $options The sidemenu widget ID or config.
     * @throws InvalidArgumentException If the menu is missing, invalid, or malformed.
     * @return array|Generator
     */
    protected function createHeaderMenu($options = null)
    {
        $headerMenu = $this->adminConfig('header_menu');

        if (!isset($headerMenu['items'])) {
            throw new InvalidArgumentException(
                'Missing "admin.header_menu.items"'
            );
        }

        $mainMenu = null;
        if (isset($this['header_menu_item'])) {
            $mainMenu = $this['header_menu_item'];
        }

        if (!(empty($options) && !is_numeric($options))) {
            if (is_string($options)) {
                $mainMenu = $options;
            } elseif (is_array($options)) {
                if (isset($options['widget_options']['ident'])) {
                    $mainMenu = $options['widget_options']['ident'];
                }
            }
        }

        $mainMenuFromRequest = filter_input(INPUT_GET, 'main_menu', FILTER_SANITIZE_STRING);
        if ($mainMenuFromRequest) {
            $mainMenu = $mainMenuFromRequest;
        }

        $menu  = $this->menuBuilder->build([]);
        foreach ($headerMenu['items'] as $menuIdent => $menuItem) {
            $menuItem['menu'] = $menu;
            $test = $this->menuItemBuilder->build($menuItem);
            if ($test->isAuthorized() === false) {
                continue;
            }
            unset($menuItem['menu']);

            if (isset($menuItem['active']) && $menuItem['active'] === false) {
                continue;
            }

            $menuItem  = $this->parseHeaderMenuItem($menuItem, $menuIdent, $mainMenu);
            $menuIdent = $menuItem['ident'];

            yield $menuIdent => $menuItem;
        }
    }

    /**
     * @param  mixed $options The sidemenu widget ID or config.
     * @throws InvalidArgumentException If the sidemenu widget is invalid.
     * @return \Charcoal\Admin\Widget\SidemenuWidgetInterface|null
     */
    protected function createSidemenu($options = null)
    {
        if (!is_array($options)) {
            $options = [
                'widget_options' => [
                    'ident' => $options
                ]
            ];
        } elseif (!isset($options['widget_options']['ident'])) {
            $options['widget_options']['ident'] = null;
        }

        if (isset($this['side_menu_item'])) {
            $options['widget_options']['current_item'] = $this['side_menu_item'];
        }

        if (isset($this['header_menu_item'])) {
            $options['widget_options']['ident'] = $this['header_menu_item'];
        }

        $sidemenuFromRequest = filter_input(INPUT_GET, 'side_menu', FILTER_SANITIZE_STRING);
        $mainMenuFromRequest = filter_input(INPUT_GET, 'main_menu', FILTER_SANITIZE_STRING);
        if ($sidemenuFromRequest) {
            $options['widget_options']['ident'] = $sidemenuFromRequest;
        } elseif ($mainMenuFromRequest) {
            $options['widget_options']['ident'] = $mainMenuFromRequest;
        }

        if (!is_string($options['widget_options']['ident'])) {
            return null;
        }

        if (isset($options['widget_type'])) {
            $widgetType = $options['widget_type'];
        } else {
            $widgetType = 'charcoal/admin/widget/sidemenu';
        }

        $sidemenu = $this->widgetFactory()->create($widgetType);

        if (isset($options['widget_options'])) {
            $sidemenu->setData($options['widget_options']);
        }

        return $sidemenu;
    }

    /**
     * @param  mixed $options The sidemenu widget ID or config.
     * @throws InvalidArgumentException If the menu is missing, invalid, or malformed.
     * @return array|Generator
     */
    protected function createSystemMenu($options = null)
    {
        $menuConfig = $this->adminConfig('system_menu');

        if (!isset($menuConfig['items'])) {
            return [];
        }

        $currentIdent = null;
        if (isset($this['system_menu_item'])) {
            $currentIdent = $this['system_menu_item'];
        }

        if (!(empty($options) && !is_numeric($options))) {
            if (is_string($options)) {
                $currentIdent = $options;
            } elseif (is_array($options)) {
                $menuConfig = array_replace_recursive($menuConfig, $options);
            }
        }

        $systemMenu = $this->menuBuilder->build([]);
        $menuItems  = [];
        foreach ($menuConfig['items'] as $menuIdent => $menuItem) {
            $menuItem['menu'] = $systemMenu;
            $test = $this->menuItemBuilder->build($menuItem);
            if ($test->isAuthorized() === false) {
                continue;
            }
            unset($menuItem['menu']);

            if (isset($menuItem['active']) && $menuItem['active'] === false) {
                continue;
            }

            $menuItem  = $this->parseSystemMenuItem($menuItem, $menuIdent, $currentIdent);
            $menuIdent = $menuItem['ident'];

            $menuItems[$menuIdent] = $menuItem;
        }
        return $menuItems;
    }

    /**
     * As a convenience, all admin templates have a model factory to easily create objects.
     *
     * @param FactoryInterface $factory The factory used to create models.
     * @return void
     */
    private function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
    }

    /**
     * @param FactoryInterface $factory The widget factory, to create the dashboard and sidemenu widgets.
     * @return void
     */
    private function setWidgetFactory(FactoryInterface $factory)
    {
        $this->widgetFactory = $factory;
    }

    /**
     * @param  array       $menuItem     The menu structure.
     * @param  string|null $menuIdent    The menu identifier.
     * @param  string|null $currentIdent The current menu identifier.
     * @return array Finalized menu structure.
     */
    private function parseHeaderMenuItem(array $menuItem, $menuIdent = null, $currentIdent = null)
    {
        $svgUri = $this->baseUrl().'assets/admin/images/svgs.svg#icon-';

        if (isset($menuItem['ident'])) {
            $menuIdent = $menuItem['ident'];
        } else {
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

        $menuItem['selected'] = ($menuItem['ident'] === $currentIdent);

        return $menuItem;
    }

    /**
     * @param  array       $menuItem     The menu structure.
     * @param  string|null $menuIdent    The menu identifier.
     * @param  string|null $currentIdent The current menu identifier.
     * @return array Finalized menu structure.
     */
    private function parseSystemMenuItem(array $menuItem, $menuIdent = null, $currentIdent = null)
    {
        if (!isset($menuItem['ident'])) {
            $menuItem['ident'] = $menuIdent;
        }

        if (!empty($menuItem['url'])) {
            $url = $menuItem['url'];
            if ($url && strpos($url, ':') === false && !in_array($url[0], [ '/', '#', '?' ])) {
                $url = $this->adminUrl().$url;
            }
        } else {
            $url = '#';
        }

        $menuItem['url'] = $url;

        if ($menuItem['icon_css']) {
            $menuItem['iconCss'] = $menuItem['icon_css'];
        }

        if (isset($menuItem['label'])) {
            $menuItem['label'] = $this->translator()->translation($menuItem['label']);
        }

        $menuItem['selected'] = ($menuItem['ident'] === $currentIdent);

        return $menuItem;
    }
}
