<?php

namespace Charcoal\Admin\Template;

use \Charcoal\Admin\AdminTemplate as AdminTemplate;

/**
 * Admin Help template
 */
class HelpTemplate extends AdminTemplate
{
    /**
     * Help is available to all users, no login required.
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
