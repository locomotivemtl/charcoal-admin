<?php

namespace Charcoal\Admin;

use RuntimeException;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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

    const GOOGLE_RECAPTCHA_SERVER_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * The name of the project.
     *
     * @var \Charcoal\Translator\Translation|string|null
     */
    private $siteName;

    /**
     * Store the user response token from the last validation by Google reCAPTCHA API.
     *
     * @var string|null
     */
    private $recaptchaLastToken;

    /**
     * Store the result from the last validation by Google reCAPTCHA API.
     *
     * @var array|null
     */
    private $recaptchaLastResult;

    /**
     * Store the model factory.
     *
     * @var FactoryInterface $modelFactory
     */
    private $modelFactory;

    /**
     * Action's init method is called automatically from `charcoal-app`'s Action Route.
     *
     * For admin actions, initializations is:
     *
     * - to start a session, if necessary
     * - to authenticate
     * - to initialize the action data with the PSR Request object
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
        unset($request);
        // Test if authentication is required.
        if ($this->authRequired() === false) {
            return;
        }

        // Test if user is authorized to access this controller
        if ($this->isAuthorized() === true) {
            return;
        }

        header('HTTP/1.0 403 Forbidden');
        exit;
    }

    /**
     * Sets the action data from a PSR Request object.
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
        ];
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
        $this->setDebug($container['debug']);
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
     * Determine if a CAPTCHA test is available.
     *
     * For example, the "Login", "Lost Password", and "Reset Password" templates
     * can render the CAPTCHA test.
     *
     * @see    AdminTemplate::recaptchaEnabled() Duplicate
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
     * Retrieve the Google reCAPTCHA secret key.
     *
     * @throws RuntimeException If Google reCAPTCHA is required but not configured.
     * @return string|null
     */
    public function recaptchaSecretKey()
    {
        $recaptcha = $this->apiConfig('google.recaptcha');

        if (!empty($recaptcha['private_key'])) {
            return $recaptcha['private_key'];
        } elseif (!empty($recaptcha['secret'])) {
            return $recaptcha['secret'];
        }

        return null;
    }

    /**
     * Retrieve the result from the last validation by Google reCAPTCHA API.
     *
     * @return array|null
     */
    protected function getLastCaptchaValidation()
    {
        return $this->recaptchaLastResult;
    }

    /**
     * Validate a Google reCAPTCHA user response.
     *
     * @todo   {@link https://github.com/mcaskill/charcoal-recaptcha Implement CAPTCHA validation as a service}.
     * @link   https://developers.google.com/recaptcha/docs/verify
     * @param  string $token A user response token provided by reCAPTCHA.
     * @throws RuntimeException If Google reCAPTCHA is not configured.
     * @return boolean Returns TRUE if the user response is valid, FALSE if it is invalid.
     */
    protected function validateCaptcha($token)
    {
        if (empty($token)) {
            throw new RuntimeException('Google reCAPTCHA response parameter is invalid or malformed.');
        }

        $secret = $this->recaptchaSecretKey();
        if (empty($secret)) {
            throw new RuntimeException('Google reCAPTCHA [apis.google.recaptcha.private_key] is not configured.');
        }

        $data = [
            'secret'   => $secret,
            'response' => $token,
        ];

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $data['remoteip'] = $_SERVER['REMOTE_ADDR'];
        }

        $query = http_build_query($data);
        $url   = static::GOOGLE_RECAPTCHA_SERVER_URL.'?'.$query;

        $this->logger->debug(sprintf('Verifying reCAPTCHA user response: %s', $url));

        /**
         * @todo Use Guzzle
         */
        $result = file_get_contents($url);
        $result = (array)json_decode($result, true);

        $this->recaptchaLastToken  = $token;
        $this->recaptchaLastResult = $result;

        return isset($result['success']) && (bool)$result['success'];
    }

    /**
     * Validate a Google reCAPTCHA user response from a PSR Request object.
     *
     * @param  RequestInterface       $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface|null $response A PSR-7 compatible Response instance.
     *     If $response is provided and challenge fails, then it is replaced
     *     with a new Response object that represents a client error.
     * @return boolean Returns TRUE if the user response is valid, FALSE if it is invalid.
     */
    protected function validateCaptchaFromRequest(RequestInterface $request, ResponseInterface &$response = null)
    {
        $token = $request->getParam('g-recaptcha-response', false);
        if (empty($token)) {
            if ($response !== null) {
                $this->addFeedback('error', $this->translator()->translate('Missing CAPTCHA response.'));
                $this->setSuccess(false);

                $response = $response->withStatus(400);
            }

            return false;
        }

        $result = $this->validateCaptcha($token);
        if ($result === false && $response !== null) {
            $this->addFeedback('error', $this->translator()->translate('Invalid or malformed CAPTCHA response.'));
            $this->setSuccess(false);

            $response = $response->withStatus(400);
        }

        return $result;
    }
}
