<?php

namespace Charcoal\Admin\Action;

// Dependencies from `PHP`
use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Charcoal as Charcoal;

use \Charcoal\Admin\AdminAction as AdminAction;
use \Charcoal\Admin\User as User;

/**
* Admin Login Action: Attempt to log a user in.
*
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
*
* @see \Charcoal\Charcoal::app() The `Slim` application inside the core Charcoal object, used to read request and set response.
*/
class LoginAction extends AdminAction
{
    /**
    * @var string $_next_url
    */
    protected $_next_url;

    /**
    * @param array $data
    * @return Login Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);
        if (isset($data['next_url']) && $data['next_url'] !== null) {
            $this->set_next_url($data['next_url']);
        }
        return $this;
    }

    /**
    * Assign the next URL.
    *
    * Note that any string is accepted. It should be validated before using this method.
    *
    * @param string $next_url
    * @throws InvalidArgumentException If the $next_url parameter is not a string.
    */
    public function set_next_url($next_url)
    {
        if (!is_string($next_url)) {
            throw new InvalidArgumentException('Next URL needs to be a string');
        }
        $this->_next_url = $next_url;
        return $this;
    }

    /**
    * Get the next URL.
    *
    * If none was assigned, generate the default URL (frrom `admin/home`)
    */
    public function next_url()
    {
        if (!$this->_next_url) {
            return Charcoal::app()->urlFor('admin/home');
        }
        return $this->_next_url;
    }

    /**
    * @return string
    */
    public function success_url()
    {
        return $this->next_url();
    }

    /**
    * @return string
    */
    public function failure_url()
    {
        return Charcoal::app()->urlFor('admin/login');
    }

    /**
    * @return void
    */
    public function run()
    {
        $username = Charcoal::app()->request->post('username');
        $password = Charcoal::app()->request->post('password');

        if (!$username || !$password) {
            $this->set_success(false);
            $this->output(404);
            return;
        }
        $u = new User();
        try {
            $is_authenticated = $u->authenticate($username, $password);
        } catch (Exception $e) {
            $is_authenticated = false;
        }

        if (!$is_authenticated) {
            $this->set_success(false);
            $this->output(403);
        } else {
            $this->set_success(true);
            $this->output(200);
        }
    }

    /**
    * @return array
    */
    public function response()
    {
        $success = $this->success();

        $response = [
            'success'=>$this->success(),
            'next_url'=>$this->redirect_url()
        ];
        return $response;
    }

}
