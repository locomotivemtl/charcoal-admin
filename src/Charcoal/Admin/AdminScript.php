<?php

namespace Charcoal\Admin;

use \Exception;

// Module `charcoal-app` dependencies
use Charcoal\App\Script\AbstractScript;

// Intract-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\User;

/**
*
*/
abstract class AdminScript extends AbstractScript
{
    public function init()
    {
        parent::init();

        // Authenticate terminal user as an admin user.
        if ($this->auth_required() !== false) {
            $this->auth();
        }
    }

    /**
    * Determine if user authentication is required.
    *
    * Authentication is required by default. If unnecessary,
    * replace this method in the inherited template class.
    *
    * @see \Charcoal\Admin\Template::auth_required()
    *
    * @return boolean
    */
    public function auth_required()
    {
        return false;
    }

    /**
    *
    */
    public function auth()
    {
        $climate = $this->climate();

        $u = User::get_authenticated();
        if ($u === null) {
            $climate->yellow()->out(
                'You need to be logged in into your "admin" account to continue...'
            );

            $input = $climate->input(
                'Please enter your username:'
            );
            $username = $input->prompt();

            $input = $climate->password(
                'Please enter your password (hidden):'
            );
            $password = $input->prompt();

            $climate->br();

            $this->logger()->debug(
                sprintf('Admin login attempt: "%s"', $username)
            );

            try {
                $u = new User([
                    'logger' => $this->logger()
                ]);
                $is_authenticated = $u->authenticate($username, $password);
            } catch (\Exception $e) {
                $climate->dump($e);
                $is_authenticated = false;
            }

            if (!$is_authenticated) {
                $this->log_failed_attempt($username);
                $climate->br()->error(
                    'Authentication failed.'
                );
                die();
            } else {
                $this->log_successful_login($username);
            }
        }
    }

    public function log_failed_attempt($username)
    {
        $this->logger()->warning(
            sprintf('Login attempt failure: "%s"', $username)
        );
    }

    public function log_successful_login($username)
    {
        $this->logger()->debug(
            sprintf('Login attempt successful: "%s"', $username)
        );
    }
}
