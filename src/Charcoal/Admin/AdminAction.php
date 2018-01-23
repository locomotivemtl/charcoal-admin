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
use Charcoal\Translator\Translation;
use Charcoal\Translator\TranslatorAwareTrait;

// From 'charcoal-app'
use Charcoal\App\Action\AbstractAction;

// From 'charcoal-admin'
use Charcoal\Admin\Ui\FeedbackContainerTrait;
use Charcoal\Admin\Support\AdminTrait;
use Charcoal\Admin\Support\BaseUrlTrait;
use Charcoal\Admin\Support\SecurityTrait;

/**
 * The base class for the `admin` Actions.
 */
abstract class AdminAction extends AbstractAction implements
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
     * Retrieve the name of the project.
     *
     * @return Translation|string|null
     */
    public function siteName()
    {
        return $this->siteName;
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
     * Set common dependencies used in all admin actions.
     *
     * @param  Container $container DI Container.
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


        // Satisfies AdminAction dependencies
        $this->setSiteName($container['config']['project_name']);
        $this->setModelFactory($container['model/factory']);
    }

    /**
     * @param FactoryInterface $factory The factory used to create models.
     * @return void
     */
    protected function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
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
     * @todo   {@link https://github.com/mcaskill/charcoal-recaptcha Implement CAPTCHA validation as a service}.
     * @param  string $response The captcha value (response) to validate.
     * @throws RuntimeException If Google reCAPTCHA is not configured.
     * @return boolean
     */
    protected function validateCaptcha($response)
    {
        $validationUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha = $this->appConfig('apis.google.recaptcha');

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
}
