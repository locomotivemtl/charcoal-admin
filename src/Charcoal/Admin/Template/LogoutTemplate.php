<?php

namespace Charcoal\Admin\Template;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Template\AuthTemplateTrait;

/**
 * Log Out template
 */
class LogoutTemplate extends AdminTemplate
{
    use AuthTemplateTrait {
        AuthTemplateTrait::avatarImage as loginLogo;
    }

    /**
     * @param RequestInterface $request The request to initialize.
     * @return boolean
     */
    public function init(RequestInterface $request)
    {
        $authenticator = $this->authenticator();

        if ($authenticator->check()) {
            $authenticator->logout();
        }

        return parent::init($request);
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
    public function avatarImage()
    {
        $logo = $this->adminConfig('logout.logo') ?:
                $this->adminConfig('logout_logo', 'assets/admin/images/avatar.jpg');

        if (empty($logo)) {
            return $this->loginLogo();
        }

        return $this->baseUrl($logo);
    }

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle($this->translator()->translation('auth.logout.title'));
        }

        return $this->title;
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
