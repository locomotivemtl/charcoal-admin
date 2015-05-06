<?php

namespace Charcoal\Admin\Template;

use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Admin\Template as Template;

class Login extends Template
{
    protected function auth_required()
    {
        return false;
    }

    public function show_header_menu()
    {
        return false;
    }

    public function show_footer_menu()
    {
        return false;
    }

    public function url_login_action()
    {
        return Charcoal::app()->urlFor('admin/action/login');
    }
}
