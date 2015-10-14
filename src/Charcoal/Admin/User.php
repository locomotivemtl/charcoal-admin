<?php

namespace Charcoal\Admin;

// Module `charcoal-base` dependencies
use \Charcoal\User\AbstractUser;

// Local namespace dependencies
use \Charcoal\Admin\UserConfig;
use \Charcoal\Admin\UserGroup;

/**
* Admin User class
*/
class User extends AbstractUser
{
    /**
    * @return string
    */
    static public function session_key()
    {
        return 'admin.user';
    }

    /**
    * ConfigurableInterface > create_config()
    *
    * @param array|null $data Optional
    * @return UserConfig
    */
    public function create_config(array $data = null)
    {
        $config = new UserConfig();
        if ($data !== null) {
            $config->set_data($data);
        }
        return $config;
    }

    /**
    * @param array|null $data Optional
    * @return UserGroup
    */
    public function create_group(array $data = null)
    {
        $group =  new UserGroup();
        if ($data !== null) {
            $group->set_data($data);
        }
        return $group;
    }

}
