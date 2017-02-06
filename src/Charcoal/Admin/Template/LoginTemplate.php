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
        if (!isset($this->adminConfig['login'])) {
            return '';
        }
        $loginConfig = $this->adminConfig['login'];
        if (!isset($loginConfig['background_image']) || !is_string($loginConfig['background_image'])) {
            return '';
        }
        $bg = $loginConfig['background_image'];
        if (strstr($bg, 'http')) {
            return $bg;
        } else {
            return $this->baseUrl().$bg;
        }
    }

    /**
     * Get the background video, from admin config.
     *
     * @return string
     */
    public function backgroundVideo()
    {
        if (!isset($this->adminConfig['login'])) {
            return '';
        }
        $loginConfig = $this->adminConfig['login'];
        if (!isset($loginConfig['background_video']) || !is_string($loginConfig['background_video'])) {
            return '';
        }
        $bg = $loginConfig['background_video'];
        if (strstr($bg, 'http')) {
            return $bg;
        } else {
            return $this->baseUrl().$bg;
        }
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
        return 'action/login';
    }

    /**
     * @return string
     */
    public function urlLostPassword()
    {
        return 'account/lost-password';
    }

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation
     */
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle([
                'en' => 'Log In',
                'fr' => 'Se connecter',
            ]);
        }

        return $this->title;
    }
}
