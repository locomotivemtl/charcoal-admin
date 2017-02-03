<?php

namespace Charcoal\Admin;

use Pimple\Container;

// From PSR-7 (HTTP Messaging)
use Psr\Http\Message\RequestInterface;

// Module `charcoal-factory` dependencies
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-base
use Charcoal\User\Authenticator;
use Charcoal\User\Authorizer;

// From 'charcoal-translation'
use Charcoal\Translation\TranslationString;

// From 'charcoal-app'
use Charcoal\App\Action\AbstractAction;

// Local module (charcoal-admin) dependencies
use Charcoal\Admin\Ui\FeedbackContainerTrait;
use Charcoal\Admin\User\AuthAwareInterface;
use Charcoal\Admin\User\AuthAwareTrait;

/**
 * The base class for the `admin` Actions.
 *
 */
abstract class AdminAction extends AbstractAction implements AuthAwareInterface
{
    use AuthAwareTrait;
    use FeedbackContainerTrait;

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
     * @var FactoryInterface $modelFactory
     */
    private $modelFactory;

    /**
     * @param array $data Optional.
     */
    final public function __construct(array $data = null)
    {
        parent::__construct($data);

        if ($data !== null) {
            $this->setData($data);
        }
    }

    /**
     * Dependencies
     * @param Container $container DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->appConfig = $container['config'];
        $this->adminConfig = $container['admin/config'];
        $this->setBaseUrl($container['base-url']);
        $this->setSiteName($container['config']['project_name']);
        $this->setModelFactory($container['model/factory']);

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
                exit;
            }

            // Initialize data with GET / POST parameters.
            $this->setData($request->getParams());

            // Test template vs. ACL roles
            $authUser = $this->authenticator()->authenticate();
            if (!$this->authorizer()->userAllowed($authUser, $this->requiredAclPermissions())) {
                header('HTTP/1.0 403 Forbidden');
                exit;
            }
        } else {
            // Initialize data with GET / POST parameters.
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
            'action'
        ];
    }

    /**
     * @param FactoryInterface $factory The factory used to create models.
     * @return AdminScript Chainable
     */
    protected function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
        return $this;
    }

    /**
     * @return FactoryInterface The model factory.
     */
    protected function modelFactory()
    {
        return $this->modelFactory;
    }

    /**
     * Set the name of the project.
     *
     * @param  string $name Name of the project.
     * @return AdminAction Chainable
     */
    protected function setSiteName($name)
    {
        if (TranslationString::isTranslatable($name)) {
            $this->siteName = new TranslationString($name);
        } else {
            $this->siteName = null;
        }

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
     * Authentication is required by default.
     *
     * Reimplement and change to false in templates that do not require authentication.
     *
     * @return boolean
     */
    public function authRequired()
    {
        return true;
    }

    /**
     * Determine if the current user is authenticated. If not it redirects them to the login page.
     *
     * @return void
     */
    private function auth()
    {
        if (!session_id()) {
            session_cache_limiter(false);
            session_start();
        }

        $u = User::getAuthenticated();
        if ($u === null || !$u->id()) {
            die('Auth Required');
        }
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
     * Default response stub.
     *
     * @return array
     */
    public function results()
    {
        $results = [
            'success'   => $this->success(),
            'next_url'  => $this->redirectUrl(),
            'feedbacks' => $this->feedbacks()
        ];
        return $results;
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
}
