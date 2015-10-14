<?php

namespace Charcoal\Admin\Template;

// Module `charcoal-core` dependencies
use \Charcoal\Charcoal as Charcoal;

// Local parent namespace dependencies
use \Charcoal\Admin\AdminTemplate as AdminTemplate;

/**
*
*/
class LoginTemplate extends AdminTemplate
{
    /**
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
