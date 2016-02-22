<?php

namespace Charcoal\Admin\Template;

use \Pimple\Container;

// Local parent namespace dependencies
use \Charcoal\Admin\AdminTemplate as AdminTemplate;

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
