<?php

namespace Charcoal\Admin;

use RuntimeException;

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
use Charcoal\Translator\Translator;
use Charcoal\Translator\TranslatorAwareTrait;

// From 'charcoal-app'
use Charcoal\App\Action\AbstractAction;

// From 'charcoal-admin'
use Charcoal\Admin\Ui\FeedbackContainerTrait;

/**
 * The base class for the `admin` Actions.
 */
abstract class AdminAction extends AbstractAction implements
    AuthAwareInterface
{
    use AuthAwareTrait;
    use FeedbackContainerTrait;
    use TranslatorAwareTrait;

    /**
     * Store a reference to the admin configuration.
     *
     * @var \Charcoal\Admin\Config
     */
    protected $adminConfig;

    /**
     * Store a reference to the application configuration.
     *
     * @var \Charcoal\App\Config
     */
    protected $appConfig;

    /**
     * The name of the project.
     *
     * @var \Charcoal\Translator\Translation|string|null
     */
    private $siteName;

    /**
     * Store the model factory.
     *
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

        // Initialize data with GET / POST parameters.
        $this->setData($request->getParams());

        if ($this->authRequired() !== false) {
            // Test action vs. ACL roles
            if (!$this->isAuthorized()) {
                header('HTTP/1.0 403 Forbidden');
                exit;
            }
        }

        return parent::init($request);
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
        $this->siteName = $this->translator()->translation($name);

        return $this;
    }

    /**
     * Retrieve the name of the project.
     *
     * @return \Charcoal\Translator\Translation|string|null
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
     * @todo   {@link https://github.com/mcaskill/charcoal-recaptcha Implement CAPTCHA validation as a service}.
     * @param  string $response The captcha value (response) to validate.
     * @throws RuntimeException If Google reCAPTCHA is not configured.
     * @return boolean
     */
    protected function validateCaptcha($response)
    {
        $validationUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha = $this->appConfig['apis.google.recaptcha'];

        if (isset($recaptcha['private_key'])) {
            $secret = $recaptcha['private_key'];
        } else {
            $secret = $recaptcha['secret'];
        }

        if (!$secret) {
            throw new RuntimeException('Google reCAPTCHA [apis.google.recaptcha.private_key] not configured.');
        }

        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $response = file_get_contents($validationUrl.'?secret='.$secret.'&response='.$response.'&remoteip='.$ip);
        $response = json_decode($response, true);

        return !!$response['success'];
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
     * @return UriInterface|string
     */
    public function adminUrl()
    {
        $adminPath = $this->adminConfig['base_path'];
        return rtrim($this->baseUrl(), '/').'/'.rtrim($adminPath, '/').'/';
    }

    /**
     * Set the base URI of the application.
     *
     * @param  UriInterface|string $uri The base URI.
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
     * @return UriInterface|string
     */
    public function baseUrl()
    {
        return rtrim($this->baseUrl, '/').'/';
    }
}
