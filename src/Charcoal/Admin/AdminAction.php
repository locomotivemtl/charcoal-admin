<?php

namespace Charcoal\Admin;

// Dependencies from `PHP`
use \Exception;

// Module `charcoal-core` dependencies
use Charcoal\Charcoal;

// Module `charcoal-app` dependencies
use Charcoal\App\Action\AbstractAction;

/**
* The base class for the `admin` Actions.
*
* @see \Charcoal\Charcoal::app() The `Slim` application inside the core Charcoal object, used to set response.
*/
abstract class AdminAction extends AbstractAction
{
    private $feedbacks = [];

    /**
    * @param array $data Optional
    */
    final public function __construct(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }

        if ($this->auth_required() === true) {
            // @todo Authentication
            $this->auth();
        }
    }

    /**
    * Authentication is required by default.
    *
    * Change to false in
    *
    * @return boolean
    */
    public function auth_required()
    {
        return false;
    }

    /**
    * Determine if the current user is authenticated. If not it redirects them to the login page.
    */
    private function auth()
    {
        //$cfg = AdminModule::config();
        $u = User::get_authenticated();
        if ($u === null) {
            die('Auth required');
       }
    }

    /**
    * @param integer $http_code
    * @throws Exception if mode is invalid
    */
    public function output($response)
    {
        $mode = $this->mode();

        if ($mode == self::MODE_JSON) {
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode($this->response()));
        } else if ($mode == self::MODE_REDIRECT) {
            return $response
                ->withHeader('Location', $this->redirect_url());
        } else {
            throw new Exception(
                sprintf('Invalid mode "%s"', $mode)
            );
        }
    }

    /**
    * @return boolean
    */
    public function has_feedbacks()
    {
        return (count($this->feedbacks()) > 0);
    }

    /**
    * @return integer
    */
    public function num_feedbacks()
    {
        return count($this->feedbacks());
    }

    /**
    * @return array
    */
    public function feedbacks()
    {
        return $this->feedbacks;
    }

    public function add_feedback($level, $msg)
    {
        $this->feedbacks[] = [
            'msg'=>$msg,
            'level'=>$level
        ];
    }

    /**
    * Default response stub
    *
    * @return array
    */
    public function results()
    {
        $success = $this->success();

        $results = [
            'success'=>$this->success(),
            'next_url'=>$this->redirect_url(),
            'feedbacks'=>$this->feedbacks()
        ];
        return $results;
    }
}
