<?php

namespace Charcoal\Admin\Template;

// Local parent namespace dependencies
use \Charcoal\Admin\AdminTemplate as AdminTemplate;

/**
*
*/
class LoginTemplate extends AdminTemplate
{
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
