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
    *
    */
    protected function auth_required()
    {
        return false;
    }

    /**
    *
    */
    public function show_header_menu()
    {
        return false;
    }

    /**
    *
    */
    public function show_footer_menu()
    {
        return false;
    }

    /**
    *
    */
    public function url_login_action()
    {
        return Charcoal::app()->urlFor('admin/action/login');
    }
}
