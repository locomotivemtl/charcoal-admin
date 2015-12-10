<?php

namespace Charcoal\Admin\Template;

use \Charcoal\Admin\AdminTemplate as AdminTemplate;

class HelpTemplate extends AdminTemplate
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
}
