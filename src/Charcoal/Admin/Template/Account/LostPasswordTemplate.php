<?php

namespace Charcoal\Admin\Template\Account;

use \Charcoal\Admin\AdminTemplate;

/**
 * Lost password template
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
}
