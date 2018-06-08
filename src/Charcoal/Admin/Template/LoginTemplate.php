<?php

namespace Charcoal\Admin\Template;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Template\AuthTemplateTrait;
use Charcoal\Admin\User\AuthToken;

/**
 *
 */
class LoginTemplate extends AdminTemplate
{
    use AuthTemplateTrait;

    /**
     * Determine if the password token is valid.
     *
     * @param  RequestInterface $request The PSR-7 HTTP request.
     * @return boolean
     */
    public function init(RequestInterface $request)
    {
        $translator = $this->translator();

        $notice = $request->getParam('notice');
        switch ($notice) {
            case 'resetpass':
                $message = $translator->translate('Check your email for instructions to reset your password.');
                $this->addFeedback([
                    'level'       => 'notice',
                    'message'     => $message,
                    'dismissible' => false
                ]);
                break;

            case 'newpass':
                $message = $translator->translate('Log in with your new password.');
                $this->addFeedback([
                    'level'       => 'notice',
                    'message'     => $message,
                    'dismissible' => false
                ]);
                break;
        }

        return true;
    }

    /**
     * @todo   Implement using PSR Request object
     * @return boolean
     */
    private function isHttps()
    {
        if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on') {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        } elseif (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && $_SERVER['HTTP_FRONT_END_HTTPS'] === 'on') {
            return true;
        }
        return false;
    }

    /**
     * @return boolean
     */
    public function rememberMeEnabled()
    {
        $token = $this->modelFactory()->create(AuthToken::class);

        if ($token->metadata()->enabled() === false) {
            return false;
        }
        if ($token->metadata()->httpsOnly() === true) {
            return $this->isHttps();
        } else {
            return true;
        }
    }

    /**
     * Authentication is obviously never required for the login page.
     *
     * @return boolean
     */
    protected function authRequired()
    {
        return false;
    }

    /**
     * @return string
     */
    public function urlLoginAction()
    {
        return $this->adminUrl('login');
    }

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle($this->translator()->translation('auth.login.title'));
        }

        return $this->title;
    }

    /**
     * Retrieve the parameters for the Google reCAPTCHA widget.
     *
     * @return string[]
     */
    public function recaptchaParameters()
    {
        $params = parent::recaptchaParameters();
        $params['tabindex'] = 4;

        if ($this->recaptchaInvisible() === true) {
            $params['callback'] = 'CharcoalCaptchaLoginCallback';
        }

        return $params;
    }



    // Templating
    // =========================================================================

    /**
     * Determine if main & secondary menu should appear as mobile in a desktop resolution.
     *
     * @return boolean
     */
    public function isFullscreenTemplate()
    {
        return true;
    }
}
