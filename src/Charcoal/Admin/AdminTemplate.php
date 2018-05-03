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
 * - `showMainMenu` (bool) - Display the main menu or not
 * - `mainMenu` (iterator) - The main menu data
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

    const GOOGLE_RECAPTCHA_CLIENT_URL = 'https://www.google.com/recaptcha/api.js';

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
    private $showSecondaryMenu = true;

    /**
     * @var boolean
     */
    private $showMainMenu = true;

    /**
     * @var boolean
     */
    private $showSystemMenu = true;

    /**
     * @var boolean
     */
    protected $mainMenu;

    /**
     * @var boolean
     */
    protected $systemMenu;

    /**
     * @var SecondaryMenuWidgetInterface
     */
    protected $secondaryMenu;

    /**
     * @var FactoryInterface $modelFactory
     */
    private $modelFactory;

    /**
     * @var FactoryInterface $widgetFactory
     */
    private $widgetFactory;

    /**
     * The cache of parsed template names.
     *
     * @var array
     */
    protected static $templateNameCache = [];

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
        $this->authRedirect($request);

        return parent::init($request);
    }

    /**
     * Determine if the current user is authenticated, if not redirect them to the login page.
     *
     * @todo   Move auth-check and redirection to a middleware or dedicated admin route.
     * @param  RequestInterface $request The request to initialize.
     * @return void
     */
    protected function authRedirect(RequestInterface $request)
    {
        // Test if authentication is required.
        if ($this->authRequired() === false) {
            return;
        }

        // Test if user is authorized to access this controller
        if ($this->isAuthorized() === true) {
            return;
        }

        $redirectTo = urlencode($request->getRequestTarget());

        header('HTTP/1.0 403 Forbidden');
        header('Location: '.$this->adminUrl('login?redirect_to='.$redirectTo));
        exit;
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
            'main_menu_item', 'secondary_menu_item', 'system_menu_item',
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
     * @param boolean $show The show main menu flag.
     * @return AdminTemplate Chainable
     */
    public function setShowMainMenu($show)
    {
        $this->showMainMenu = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showMainMenu()
    {
        return ($this->isAuthorized() && $this->showMainMenu);
    }

    /**
     * Yield the main menu.
     *
     * @return array|Generator
     */
    public function mainMenu()
    {
        if ($this->mainMenu === null) {
            $this->mainMenu = $this->createMainMenu();
        }

        foreach ($this->mainMenu as $menuIdent => $menuItem) {
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
     * @param  boolean $show The show secondary menu flag.
     * @return AdminTemplate Chainable
     */
    public function setShowSecondaryMenu($show)
    {
        $this->showSecondaryMenu = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showSecondaryMenu()
    {
        return ($this->isAuthorized() && $this->showSecondaryMenu);
    }

    /**
     * Retrieve the secondary menu.
     *
     * @return \Charcoal\Admin\Widget\SecondaryMenuWidgetInterface|null
     */
    public function secondaryMenu()
    {
        if ($this->secondaryMenu === null) {
            $this->secondaryMenu = $this->createSecondaryMenu();
        }

        return $this->secondaryMenu;
    }

    /**
     * @return string
     */
    public function mainMenuLogo()
    {
        $logo = $this->adminConfig('menu_logo');
        if (!empty($logo)) {
            return $logo;
        }

        return 'assets/admin/images/identicon.png';
    }

    /**
     * Get the "Visit website" label.
     *
     * @return string|boolean The button's label,
     *     TRUE to use the default label,
     *     or FALSE to disable the link.
     */
    public function visitSiteLabel()
    {
        $label = $this->adminConfig('main_menu.visit_site');
        if ($label === false) {
            return false;
        }

        if (empty($label) || $label === true) {
            $label = $this->translator()->translate('Visit Site');
        } else {
            $label = $this->translator()->translate($label);
        }

        return $label;
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
        $recaptcha = $this->apiConfig('google.recaptcha');

        if (empty($recaptcha) || (isset($recaptcha['active']) && $recaptcha['active'] === false)) {
            return false;
        }

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
        $recaptcha = $this->apiConfig('google.recaptcha');

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
        $recaptcha = $this->apiConfig('google.recaptcha');

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
        $apiConfig = $this->apiConfig('google.recaptcha');
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
                } elseif (isset($apiConfig[$key])) {
                    $val = $apiConfig[$key];
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
     * @param  mixed $options The secondary menu widget ID or config.
     * @throws InvalidArgumentException If the menu is missing, invalid, or malformed.
     * @return array|Generator
     */
    protected function createMainMenu($options = null)
    {
        $mainMenuConfig = $this->adminConfig('main_menu');

        if (!isset($mainMenuConfig['items'])) {
            throw new InvalidArgumentException(
                'Missing "admin.main_menu.items"'
            );
        }

        $mainMenuIdent = null;
        if (isset($this['main_menu_item'])) {
            $mainMenuIdent = $this['main_menu_item'];
        }

        if (!(empty($options) && !is_numeric($options))) {
            if (is_string($options)) {
                $mainMenuIdent = $options;
            } elseif (is_array($options)) {
                if (isset($options['widget_options']['ident'])) {
                    $mainMenuIdent = $options['widget_options']['ident'];
                }
            }
        }

        $mainMenuFromRequest = filter_input(INPUT_GET, 'main_menu', FILTER_SANITIZE_STRING);
        if ($mainMenuFromRequest) {
            $mainMenuIdent = $mainMenuFromRequest;
        }

        $menu = $this->menuBuilder->build([]);
        foreach ($mainMenuConfig['items'] as $menuIdent => $menuItem) {
            $menuItem['menu'] = $menu;
            $test = $this->menuItemBuilder->build($menuItem);
            if ($test->isAuthorized() === false) {
                continue;
            }
            unset($menuItem['menu']);

            if (isset($menuItem['active']) && $menuItem['active'] === false) {
                continue;
            }

            $menuItem  = $this->parseMainMenuItem($menuItem, $menuIdent, $mainMenuIdent);
            $menuIdent = $menuItem['ident'];

            yield $menuIdent => $menuItem;
        }
    }

    /**
     * @throws InvalidArgumentException If the secondary menu widget is invalid.
     * @return \Charcoal\Admin\Widget\SecondaryMenuWidgetInterface|null
     */
    protected function createSecondaryMenu()
    {
        $secondaryMenu = [];
        $secondaryMenuItems = $this->adminConfig('secondary_menu');

        foreach ($secondaryMenuItems as $ident => $options) {
            $options['ident'] = $ident;

            if (isset($this['secondary_menu_item'])) {
                $options['current_item'] = $this['secondary_menu_item'];
            }

            if (isset($this['main_menu_item'])) {
                $options['ident'] = $this['main_menu_item'];
            }

            $mainMenuFromRequest = filter_input(INPUT_GET, 'main_menu', FILTER_SANITIZE_STRING);
            if ($mainMenuFromRequest) {
                $options['ident'] = $mainMenuFromRequest;
            }

            if (!is_string($options['ident'])) {
                return null;
            }

            $widget = $this->widgetFactory()
                            ->create('charcoal/admin/widget/secondary-menu')
                            ->setData($options);

            $secondaryMenu[] = $widget;
        }

        return $secondaryMenu;
    }

    /**
     * @param  mixed $options The secondary menu widget ID or config.
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
     * @param FactoryInterface $factory The widget factory, to create the dashboard and secondary menu widgets.
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
    private function parseMainMenuItem(array $menuItem, $menuIdent = null, $currentIdent = null)
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

        $secondaryMenu = $this->adminConfig('secondary_menu');
        if (!empty($menuIdent) && isset($secondaryMenu[$menuIdent])) {
            $menuItem['hasSecondaryMenu'] = true;
            $menuItem['secondaryMenu']    = $secondaryMenu[$menuIdent];
        } else {
            $menuItem['hasSecondaryMenu'] = false;
        }

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



    // Front-end helpers
    // ============================================================

    /**
     * Retrieve the template's identifier.
     *
     * @return string
     */
    public function templateName()
    {
        $key = substr(strrchr('\\'.get_class($this), '\\'), 1);

        if (!isset(static::$templateNameCache[$key])) {
            $value = $key;

            if (!ctype_lower($value)) {
                $value = preg_replace('/\s+/u', '', $value);
                $value = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1-', $value), 'UTF-8');
            }

            $value = str_replace(
                [ 'abstract', 'trait', 'interface', 'template', '\\' ],
                '',
                $value
            );

            static::$templateNameCache[$key] = trim($value, '-');
        }

        return static::$templateNameCache[$key];
    }
}
