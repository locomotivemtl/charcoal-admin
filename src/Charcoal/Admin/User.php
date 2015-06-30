<?php

namespace Charcoal\Admin;

use \DateTime as DateTime;
use \InvalidArgumentException as InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Config\ConfigurableInterface as ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait as ConfigurableTrait;

// From `charcoal-base`
use \Charcoal\Object\Content as Content;
use \Charcoal\Property\PropertyFactory as PropertyFactory;

// From `charcoal-admin`
use \Charcoal\Admin\UserConfig as UserConfig;
use \Charcoal\Admin\UserInterface as UserInterface;

/**
* Admin User class
*/
class User extends Content implements
    UserInterface,
    ConfigurableInterface
{
    use ConfigurableTrait;

    const SESSION_KEY = 'admin.user';
    /**
    * @var string
    */
    private $_username = '';
    /**
    * @var string
    */
    private $_email;
    private $_password;
    private $_groups;
    private $_permissions;
    private $_active = true;

    private $_last_login_date;
    private $_last_login_ip;

    private $_last_password_date;
    private $_last_password_ip;

    /**
    * If the login token is set, then the user should be prompted to
    * reset his password after login / enter the token to continue
    * @var string $login_token
    */
    private $_login_token;

    /**
    * @param array $data
    * @return User Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);

        if (isset($data['username']) && $data['username'] !== null) {
            $this->set_username($data['username']);
        }
        if (isset($data['email']) && $data['email'] !== null) {
            $this->set_email($data['email']);
        }

        return $this;
    }

    /**
    * Force a lowercase username
    *
    * @param string $username
    * @throws InvalidArgumentException
    * @return User Chainable
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
        return $this->_username;
    }

    /**
    * @param string $username
    * @throws InvalidArgumentException
    * @return User Chainable
    */
    public function set_email($email)
    {
        if (!is_string($email)) {
            throw new InvalidArgumentException('Email must be a string');
        }
        $email_property = $this->p('email');
        //$email_property = PropertyFactory::instance()->get('email');
        $email_property->set_val($email);
        if ($email_property->validate() === false) {
            throw new InvalidArgumentException('Email must be a valid email');
        }
        $this->_email = $email;
        return $this;
    }

    /**
    * @return string
    */
    public function email()
    {
        return $this->_email;
    }

    /**
    * @param string $password
    * @return UserInterface Chainable
    */
    public function set_password($password)
    {
        if (!is_string($password)) {
            throw new InvalidArgumentException('Password must be a string');
        }
        $this->_password = $password;
        return $this;
    }

    /**
    * @return string
    */
    public function password()
    {
        return $this->_password;
    }

    /**
    * @param array $groups
    * @return UserInterface Chainable
    */
    public function set_groups($groups)
    {
        if (!is_array($groups)) {
            //throw new InvalidArgumentException('Groups must be an array');
            return $this;
        }
        $this->_groups = [];
        foreach ($groups as $g) {
            $this->add_group($g);
        }
        return $this;
    }
    /**
    * @param array|UserGroup $group
    * @return UserInterface Chainable
    */
    public function add_group($group)
    {
        $this->_groups[] = $group;
        return $this;
    }
    /**
    * @return array The UserGroup list attached to this user
    */
    public function groups()
    {
        return $this->_groups;
    }

    /**
    * @param array $permissions
    * @throws InvalidArgumentException
    * @return UserInterface Chainable
    */
    public function set_permissions($permissions)
    {
        if (!is_array($permissions)) {
            //throw new InvalidArgumentException('Permissions must be an array');
            return $this;
        }
        $this->_permissions = [];
        foreach ($permissions as $p) {
            $this->add_permission($p);
        }
        return $this;
    }
    /**
    * @param array|UserPermission $permission
    * @return UserInterface Chainable
    */
    public function add_permission($permission)
    {
        $this->_permissions[] = $permission;
        return $this;
    }

    /**
    * @return array The UserPersmission list attached to this user
    */
    public function permissions()
    {
        return $this->_permissions;
    }

    /**
    * @param bool $active
    * @throws InvalidArgumentException
    * @return UserInterface Chainable
    */
    public function set_active($active)
    {
        if (!is_bool($active)) {
            throw new InvalidArgumentException('Active must be a boolean');
        }
        $this->_active = $active;
        return $this;
    }
    /**
    * @return bool
    */
    public function active()
    {
        return $this->_active;
    }

    /**
    * @param string|DateTime $ts
    * @return UserInterface Chainable
    */
    public function set_last_login_date($ts)
    {
        if (is_string($ts)) {
            $last_login_date = new DateTime($ts);
        } else if ($ts instanceof DateTime) {
            $last_login_date = $ts;
        } else {
            throw new InvalidArgumentException('Login date must be a valid timestamp (string or DateTime object)');
        }
        $this->_last_login_date = $last_login_date;
        return $this;
    }
    /**
    * @return DateTime
    */
    public function last_login_date()
    {
        return $this->_last_login_date;
    }

    /**
    * @param string|int $ip
    * @return UserInterface Chainable
    */
    public function set_last_login_ip($ip)
    {
        if (is_int($ip)) {
            $ip = long2ip($ip);
        }
        if (!is_string($ip)) {
            throw new InvalidArgumentException('Invalid IP address');
        }
        $this->_last_login_ip = $ip;
        return $this;
    }
    /**
    * Get the last login IP in x.x.x.x format
    * @return string
    */
    public function last_login_ip()
    {
        return $this->_last_login_ip;
    }

    /**
    * @param string|DateTime $ts
    * @return UserInterface Chainable
    */
    public function set_last_password_date($ts)
    {
        if (is_string($ts)) {
            $last_password_date = new DateTime($ts);
        } else if ($ts instanceof DateTime) {
            $last_password_date = $ts;
        } else {
            throw new InvalidArgumentException('Login date must be a valid timestamp (string or DateTime object)');
        }
        $this->_last_password_date = $last_password_date;
        return $this;
    }

    /**
    * @return DateTime
    */
    public function last_password_date()
    {
        return $this->_last_password_date;
    }

    /**
    * @param string|int $ip
    * @return UserInterface Chainable
    */
    public function set_last_password_ip($ip)
    {
        if (is_int($ip)) {
            $ip = long2ip($ip);
        }
        if (!is_string($ip)) {
            throw new InvalidArgumentException('Invalid IP address');
        }
        $this->_last_password_ip = $ip;
        return $this;
    }
    /**
    * Get the last password change IP in x.x.x.x format
    * @return string
    */
    public function last_password_ip()
    {
        return $this->_last_password_ip;
    }

    /**
    * @param string $token
    * @throws InvalidArgumentException
    * @return UserInterface Chainable
    */
    public function set_login_token($token)
    {
        if (!is_string($token)) {
            throw new InvalidArgumentException('Token must be a string');
        }
        $this->_login_token = $token;
        return $this;
    }

    /**
    * @return string
    */
    public function login_token()
    {
        return $this->_login_token;
    }

    /**
    * @param string $username
    * @param string $password
    * @throws InvalidArgumentException
    * @return boolean Login success / failure
    */
    public function authenticate($username, $password)
    {
        if (!is_string($username) || !is_string($password)) {
            throw new InvalidArgumentException('Username and password must be strings');
        }
        $pw_opts = ['cost'=>12];

        $username = mb_strtolower($username);

        $this->load($username);
        if ($this->username() != $username) {
            $this->login_failed($username);
            return false;
        }
        if ($this->active() === false) {
            $this->login_failed($username);
            return false;
        }

        // Validate password
        if (password_verify($password, $this->password())) {
            if (password_needs_rehash($this->password(), PASSWORD_DEFAULT, $pw_opts)) {
                $hash = password_hash($password, PASSWORD_DEFAULT, $pw_opts);
                // @todo Update user with new hash
                $this->update(['password']);
            }

            $this->login();
            return true;
        }

        $this->login_failed($username);
        return false;
    }

    public function reset_password($plain_password)
    {
        $pw_opts = ['cost'=>12];
        $hash = password_hash($plain_password, PASSWORD_DEFAULT, $pw_opts);
        $this->set_password($hash);

        $this->set_last_password_date('now');
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        if ($ip) {
            $this->set_last_password_ip($ip);
        }

        if($this->id()) {
            $this->update(['password', 'last_password_date', 'last_password_ip']);
        }
    }

    public function login()
    {
        if (!$this->id()) {
            return false;
        }

        $this->set_last_login_date('now');
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        if ($ip) {
            $this->set_last_login_ip($ip);
        }
        $this->update(['last_login_ip', 'last_login_date']);

        // Save to session
        //session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = $this;

        return true;
    }

    public function log_login()
    {
        // @todo
        return true;
    }

    public function login_failed($username)
    {
        $this->set_username('');
        $this->set_permissions([]);
        $this->set_groups([]);

        $this->log_login_failed($username);
    }

    public function log_login_failed($username)
    {
        // @todo
        return true;
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
        if (!$user->id() || !$user->username()) {
            return null;
        }

        // Optionally re-init the object from source (database)
        if ($reinit) {
            $user_id = $user->id();

            $user = new User();
            $user->load($user_id);
            // Save back to session
            $_SESSION[self::SESSION_KEY] = $user;
        }

        return $user;
    }

    /**
    * ConfigurableInterface > create_config()
    *
    * @param array $data Optional
    * @return EmailConfig
    */
    public function create_config(array $data = null)
    {
        $config = new UserConfig();
        if (is_array($data)) {
            $config->set_data($data);
        }
        return $config;
    }

    public function pre_save()
    {
        //var_dump('PRE SAVE');
        parent::pre_save();
    }

    public function post_save()
    {
       // var_dump('POST SAVE');
        parent::post_save();

        $cfg = $this->config();
        //var_dump($cfg);
    }
}
