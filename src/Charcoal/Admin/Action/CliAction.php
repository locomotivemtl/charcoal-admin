<?php

namespace Charcoal\Admin\Action;

// From `charcoal-base`
use \Charcoal\Action\CliAction as CliActionBase;

use \Charcoal\Admin\User as User;

abstract class CliAction extends CliActionBase
{
    public function __construct()
    {
       //session_id(getenv('CHARCOAL_ADMIN_SESSID'));
        $arguments = $this->default_arguments();
        $this->set_arguments($arguments);

        // Auth.
        if ($this->auth_required() !== false) {
            $this->auth();
        }
    }

    public function auth_required()
    {
        return true;
    }

    /**
    *
    */
    public function auth()
    {
        $climate = $this->climate();

        $u = User::get_authenticated();
        if ($u === null) {
            $climate->yellow()->out('You need to be logged in into your "admin" account to continue...');
            
            $input = $climate->input('Please enter your username:');
            $username = $input->prompt();
            $input = $climate->password('Please enter your password (hidden):');
            $password = $input->prompt();
            $climate->br();

            $u = new User();
            try {
                $is_authenticated = $u->authenticate($username, $password);
            } catch (\Exception $e) {
                $climate->dump($e);
                $is_authenticated = false;
            }
            if (!$is_authenticated) {
                //$this->log_failed_attempt();

                $climate->br()->error('Authentication failed.');
                die();
            } else {
                //$this->log_successful_login();
            }
        }
    }
}
