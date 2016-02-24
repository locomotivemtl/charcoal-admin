<?php

namespace Charcoal\Admin\Template;

use \Pimple\Container;

// Local parent namespace dependencies
use \Charcoal\Admin\AdminTemplate;
use \Charcoal\Admin\Object\AuthToken;

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
        return $loginConfig['background_image'];
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
        return $loginConfig['background_video'];
    }

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

    public function rememberMeEnabled()
    {
        $token = new AuthToken([
            'logger' => $this->logger
        ]);
        if($token->metadata()->enabled() === false) {
            return false;
        }
        if($token->metadata()->httpsOnly() === true) {
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
}
