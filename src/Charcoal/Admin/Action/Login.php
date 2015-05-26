<?php

namespace Charcoal\Admin\Action;

use \Charcoal\Charcoal as Charcoal;

use \Charcoal\Admin\Action as Action;
use \Charcoal\Admin\User as User;

/**
* ## Parameters
* **Required parameters**
* - `username`
* - `password`
* **Optional parameters**
* - `next_url`
*
* ## Response
* - `success` true if login was successful, false otherwise.
*   - Failure should also send a different HTTP code: see below.
* - `feedbacks` (Optional) operation feedbacks, if any.
* - `next_url` Redirect URL, in case of successfull login.
*   - This is the `next_url` parameter if it was set, or the default admin URL if not
*
* ## HTTP Codes
* - `200` in case of a successful login
* - `403` in case of wrong credentials
* - `404` if a required parameter is missing
*/
class Login extends Action
{
    /**
    * @var string
    */
    private $_next_url;

    public function __construct($data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }
    }

    public function set_data($data)
    {
        parent::set_data($data);
        if (isset($data['next_url']) && $data['next_url'] !== null) {
            $this->set_next_url($data['next_url']);
        }
        return $this;
    }

    public function success_url()
    {
        return $this->next_url();
    }

    public function failure_url()
    {
        return Charcoal::app()->urlFor('admin/login');
    }



    public function set_next_url($next_url)
    {
        if (!is_string($next_url)) {
            throw new \InvalidArgumentException('Next URL needs to be a string');
        }
        $this->_next_url = $next_url;
        return $this;
    }

    public function next_url()
    {
        if (!$this->_next_url) {
            return Charcoal::app()->urlFor('admin/home');
        }
        return $this->_next_url;
    }

    public function run()
    {
        $username = Charcoal::app()->request->post('username');
        $password = Charcoal::app()->request->post('password');

        if (!$username || !$password) {
            $this->set_success(false);
            $this->output(404);
        }

        $u = new User();
        try {
            $is_authenticated = $u->authenticate($username, $password);
        } catch (\Exception $e) {
            $is_authenticated = false;
        }

        if (!$is_authenticated) {
            $this->log_failed_attempt();
            $this->set_success(false);
            $this->output(403);
        } else {
            $this->log_successful_login();
            $this->set_success(true);
            $this->output(200);
        }
    }

    public function response()
    {
        $success = $this->success();

        $response = [
            'success'=>$this->success(),
            'next_url'=>$this->redirect_url()
        ];
        return $response;
    }

    public function log_failed_attempt()
    {
        // @todo
    }

    public function log_successful_login()
    {
        // @todo
    }
}
