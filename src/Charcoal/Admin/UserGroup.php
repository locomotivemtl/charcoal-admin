<?php

namespace Charcoal\Admin;

use \Charcoal\Model\AbstractModel as AbstractModel;

class UserGroup extends AbstractModel
{
    /**
    * IndexableTrait > key()
    *
    * @return string
    */
    public function key()
    {
        return 'id';
    }
}
