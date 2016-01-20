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
}
