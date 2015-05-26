<?php

namespace Charcoal\Admin;

use \InvalidArgumentException as InvalidArgumentException;

// From `charcoal-base`
use \Charcoal\Object\Content as Content;

class User extends Content
{
    const SESSION_KEY = 'admin.user';
    /**
    * @var string
    */
    protected $_username = '';
    /**
    * @var string
    */
    protected $email;
    protected $password;
    protected $groups;
    protected $permissions;
    protected $active;

    protected $last_login_date;
    protected $last_login_ip;

    protected $last_password_date;
    protected $last_password_ip;

    protected $login_token;

    /**
    * @param array $data
    * @throws InvalidArgumentException
    * @return User Chainable
    */
    public function set_data($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }
        parent::set_data($data);

        if (isset($data['username']) && $data['username'] !== null) {
            $this->set_username($data['username']);
        }

        return $this;
    }

    /**
    * @param strint $username
    * @throws InvalidArgumentException
    */
    public function set_username($username)
    {
        if (!is_string($username)) {
            throw new InvalidArgumentException('Username must be a string');
        }
        $this->_username = mb_strtolower($username);
        return $this;
    }

    /**
    * @return string
    */
    public function username()
    {
        return mb_strtolower($this->_username);
    }

    public function authenticate($username, $password)
    {
        if (!is_string($username) || !is_string($password)) {
            throw new InvalidArgumentException('Username and password must be strings');
        }
        $pw_opts = ['cost'=>12];

        $username = mb_strtolower($username);
        
        $this->load($username);
        if ($this->username() != $username) {
            $this->set_username('');
            return false;
        }

        // Validate password
        if (password_verify($password, $this->password)) {
            if (password_needs_rehash($this->password, PASSWORD_DEFAULT, $pw_opts)) {
                $hash = password_hash($password, PASSWORD_DEFAULT, $pw_opts);
                // @todo Update user with new hash
            }

            // Save to session
            //session_regenerate_id(true);
            $_SESSION[self::SESSION_KEY] = $this;

            return true;
        }

        return false;
    }

    public function key()
    {
        return 'username';
    }

    /**
    *
    */
    static public function get_authenticated($reinit = true)
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return null;
        }
        $user = $_SESSION[self::SESSION_KEY];
        if (!($user instanceof User)) {
            unset($_SESSION[self::SESSION_KEY]);
            return null;
        }

        // Inactive users can not authenticate
        if (!$user->active()) {
            // @todo log error
            return null;
        }

        // Make sure the user is valid.
        if (!$user->id()) {
            return null;
        }

        // Optionally re-init the object from source (database)
        if ($reinit) {
            $user = Charcoal::obj(get_called_class())->load($user->id());
            // Save back to session
            $_SESSION[self::SESSION_KEY] = $user;
        }

        return $user;
    }

}
