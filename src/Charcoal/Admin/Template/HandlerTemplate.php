<?php

namespace Charcoal\Admin\Template;

// From 'charcoal-app'
use Charcoal\App\Handler\HandlerAwareTrait;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;

/**
 * Admin Error Handler Template
 */
class HandlerTemplate extends AdminTemplate
{
    use HandlerAwareTrait;

    /**
     * @return string
     */
    public function ident()
    {
        return 'error';
    }

    /**
     * Retrieve the title of the page.
     *
     * @return string|null
     */
    public function title()
    {
        return $this->appHandler()->getSummary();
    }

    /**
     * Error handler response is available to all users, no login required.
     *
     * @return boolean
     */
    protected function authRequired()
    {
        return false;
    }
}
