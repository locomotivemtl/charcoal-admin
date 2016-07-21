<?php

namespace Charcoal\Admin\Template;



// Local parent namespace dependencies
use \Charcoal\Admin\AdminTemplate;

/**
 *
 */
class ElfinderTemplate extends AdminTemplate
{
    /**
     * @return string
     */
    public function elfinderUrl()
    {
        return $this->baseUrl().'elfinder/';
    }
}
