<?php

namespace Charcoal\Admin\Template;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\User\AuthToken;

/**
 *
 */
class LoginTemplate extends AdminTemplate
{
    /**
     * Get the background image, from admin config.
     *
     * @return string
     */
    public function backgroundImage()
    {
        $backdrop = $this->adminConfig('login.background_image');
        if (empty($backdrop)) {
            return '';
        }

        return $this->baseUrl($backdrop);
    }

    /**
     * Get the background video, from admin config.
     *
     * @return string
     */
    public function backgroundVideo()
    {
        $backdrop = $this->adminConfig('login.background_video');
        if (empty($backdrop)) {
            return '';
        }

        return $this->baseUrl($backdrop);
    }

    /**
     * @return string
     */
    public function loginLogo()
    {
        $logo = $this->adminConfig('login.logo') ?:
                $this->adminConfig('login_logo', 'assets/admin/images/avatar.jpg');

        if (empty($logo)) {
            return '';
        }

        return $this->baseUrl($logo);
    }

    /**
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
     * @return boolean
     */
    public function showHeaderMenu()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function showFooterMenu()
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
     * @return string
     */
    public function urlLostPassword()
    {
        return $this->adminUrl('account/lost-password');
    }

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle($this->translator()->translation('Log In'));
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

        if ($this->recaptchaInvisible() === true) {
            $params['callback'] = 'CharcoalCaptchaLoginCallback';
            $params['tabindex'] = 4;
        }

        return $params;
    }
}
