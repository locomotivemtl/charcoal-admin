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
    protected function auth_required()
    {
        return false;
    }

    /**
    * @return boolean
    */
    public function show_header_menu()
    {
        return false;
    }

    /**
    * @return boolean
    */
    public function show_footer_menu()
    {
        return false;
    }

    /**
    * @return string
    */
    public function url_login_action()
    {
        return 'action/login';
    }
}
