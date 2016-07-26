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
        return $this->baseUrl().'assets/admin/elfinder/';
    }

    public function elfinderCallback()
    {
        return isset($_GET['callback']) ? $_GET['callback'] : '';
    }
}
