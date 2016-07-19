<?php

namespace Charcoal\Admin;

// Module `charcoal-base` dependencies
use \Charcoal\User\AbstractUser;

// Local namespace dependencies
use \Charcoal\Admin\UserGroup;

/**
 * Admin User class.
 */
class User extends AbstractUser
{
    /**
     * @return string
     */
    public static function sessionKey()
    {
        return 'admin.user';
    }

    /**
     * @param array|null $data Optional. Default usergroup data.
     * @return UserGroup
     */
    public function createGroup(array $data = null)
    {
        $group =  new UserGroup();
        if ($data !== null) {
            $group->setData($data);
        }
        return $group;
    }
}
