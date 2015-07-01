<?php

namespace Charcoal\Admin;

interface UserInterface
{
    /**
    * @param array $data
    * @return UserInterface Chainable
    */
    public function set_data(array $data);

    /**
    * Force a lowercase username
    *
    * @param string $username
    * @return UserInterface Chainable
    */
    public function set_username($username);

    /**
    * @return string
    */
    public function username();

    /**
    * @param string $username
    * @return UserInterface Chainable
    */
    public function set_email($email);

    /**
    * @return string
    */
    public function email();

    /**
    * @param string $password
    * @return UserInterface Chainable
    */
    public function set_password($password);

    /**
    * @return string
    */
    public function password();

    /**
    * @param array $groups
    * @return UserInterface Chainable
    */
    public function set_groups($groups);
    /**
    * @param array|UserGroup $group
    * @return UserInterface Chainable
    */
    public function add_group($group);
    /**
    * @return array The UserGroup list attached to this user
    */
    public function groups();

    /**
    * @param array $permissions
    * @return UserInterface Chainable
    */
    public function set_permissions($permissions);
    /**
    * @param array|UserPermission $permission
    * @return UserInterface Chainable
    */
    public function add_permission($permission);
    /**
    * @return array The UserPersmission list attached to this user
    */
    public function permissions();

    /**
    * @param bool $active
    * @return UserInterface Chainable
    */
    public function set_active($active);
    /**
    * @return bool
    */
    public function active();

    /**
    * @param string|DateTime $ts
    * @return UserInterface Chainable
    */
    public function set_last_login_date($ts);
    /**
    * @return DateTime
    */
    public function last_login_date();

    /**
    * @param string|int $ip
    * @return UserInterface Chainable
    */
    public function set_last_login_ip($ip);
    /**
    * Get the last login IP in x.x.x.x format
    * @return string
    */
    public function last_login_ip();

    /**
    * @param string|DateTime $ts
    * @return UserInterface Chainable
    */
    public function set_last_password_date($ts);
    /**
    * @return DateTime
    */
    public function last_password_date();

    /**
    * @param string|int $ip
    * @return UserInterface Chainable
    */
    public function set_last_password_ip($ip);
    /**
    * Get the last password change IP in x.x.x.x format
    * @return string
    */
    public function last_password_ip();

    /**
    * @param string $token
    * @return UserInterface Chainable
    */
    public function set_login_token($token);
    /**
    * @return string
    */
    public function login_token();

    /**
    * @param string $username
    * @param string $password
    * @return boolean Login success / failure
    */
    public function authenticate($username, $password);

    //public function has_permission($permission);

    /**
    * Ensure the Model key is "username"
    * @return string
    */
    public function key();

    /**
    * Get the currently authenticated user.
    *
    * @param bool $reinit Whether to reload user data from source
    * @return UserInterface|null
    */
    static public function get_authenticated($reinit = true);

}
