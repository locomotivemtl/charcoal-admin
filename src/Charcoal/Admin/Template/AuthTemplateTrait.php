<?php

namespace Charcoal\Admin\Template;

/**
 *
 */
trait AuthTemplateTrait
{
    /**
     * Retrieve the base URI of the application.
     *
     * @param  mixed $targetPath Optional target path.
     * @throws RuntimeException If the base URI is missing.
     * @return string|null
     */
    abstract public function baseUrl($targetPath = null);

    /**
     * Retrieve the URI of the administration-area.
     *
     * @param  mixed $targetPath Optional target path.
     * @throws RuntimeException If the admin URI is missing.
     * @return UriInterface|null
     */
    abstract public function adminUrl($targetPath = null);

    /**
     * Retrieve the admin's configset.
     *
     * @param  string|null $key     Optional data key to retrieve from the configset.
     * @param  mixed|null  $default The default value to return if data key does not exist.
     * @return mixed|AdminConfig
     */
    abstract protected function adminConfig($key = null, $default = null);

    /**
     * @return string
     */
    public function urlLogin()
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
     * @return string
     */
    public function urlResetPassword()
    {
        return $this->adminUrl('account/reset-password');
    }

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
    public function avatarImage()
    {
        $logo = $this->adminConfig('login.logo') ?:
                $this->adminConfig('login_logo', 'assets/admin/images/avatar.jpg');

        if (empty($logo)) {
            return '';
        }

        return $this->baseUrl($logo);
    }
}
