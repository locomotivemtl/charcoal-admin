<?php

namespace Charcoal\Admin\Template\System;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;

/**
 *
 */
class ClearCacheTemplate extends AdminTemplate
{
    /**
     * Retrieve the title of the page.
     *
     * @return Translation|string|null
     */
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle($this->translator()->translation('Clear cache'));
        }

        return $this->title;
    }

    /**
     * @return SidemenuWidgetInterface|null
     */
    public function sidemenu()
    {
        return $this->createSidemenu('system');
    }
}
