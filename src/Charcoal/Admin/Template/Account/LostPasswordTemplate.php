<?php

namespace Charcoal\Admin\Template\Account;

use Charcoal\Admin\AdminTemplate;

/**
 * Lost Password Template
 *
 * Related: {@see \Charcoal\Admin\Template\Account\ResetPasswordTemplate Reset Password Template}
 */
class LostPasswordTemplate extends AdminTemplate
{
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
        return $this->adminUrl().'account/lost-password';
    }

    /**
     * @return string
     */
    public function urlResetPassword()
    {
        return $this->adminUrl().'account/reset-password';
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
                'en' => 'Lost Password',
                'fr' => 'Mot de passe oublié',
            ]);
        }

        return $this->title;
    }
}
