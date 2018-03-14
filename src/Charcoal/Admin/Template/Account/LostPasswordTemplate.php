<?php

namespace Charcoal\Admin\Template\Account;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Template\AuthTemplateTrait;

/**
 * Lost Password Template
 *
 * Related: {@see \Charcoal\Admin\Template\Account\ResetPasswordTemplate Reset Password Template}
 */
class LostPasswordTemplate extends AdminTemplate
{
    use AuthTemplateTrait;

    /**
     * @return boolean
     */
    public function authRequired()
    {
        return false;
    }

    /**
     * @return string
     */
    public function urlLostPasswordAction()
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
            $this->setTitle($this->translator()->translation('Lost Password'));
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
            $params['callback'] = 'CharcoalCaptchaResetPassCallback';
            $params['tabindex'] = 2;
        }

        return $params;
    }
}
