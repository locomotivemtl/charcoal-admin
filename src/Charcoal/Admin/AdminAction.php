<?php

namespace Charcoal\Admin;

// Dependencies from `PHP`
use \Exception as Exception;

// Module `charcoal-core` dependencies
use Charcoal\Charcoal as Charcoal;

// Module `charcoal-base` dependencies
use Charcoal\Action\AbstractAction as AbstractAction;

/**
* The base class for the `admin` Actions.
*
* @see \Charcoal\Charcoal::app() The `Slim` application inside the core Charcoal object, used to set response.
*/
abstract class AdminAction extends AbstractAction
{
    /**
    * @param array $data Optional
    */
    final public function __construct(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }
    }

    /**
    * @param array $data
    * @return AdminAction Chainable
    */
    abstract public function set_data(array $data);

    /**
    * @param integer $http_code
    * @throws Exception if mode is invalid
    */
    public function output($http_code = 200)
    {
        $response = $this->response();
        $mode = $this->mode();

        if ($mode == self::MODE_JSON) {
            Charcoal::app()->response->setStatus($http_code);
            Charcoal::app()->response->headers->set('Content-Type', 'application/json');
            echo json_encode($response);
        } else if ($mode == self::MODE_REDIRECT) {
            Charcoal::app()->response->redirect($this->redirect_url(), $http_code);
        } else {
            throw new Exception('Invalid mode');
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

    /**
    * @return string
    */
    public function ip()
    {
        return Charcoal::app()->request->getIp();
    }

    /**
    * @return string
    */
    public function user_agent()
    {
        return Charcoal::app()->request->getUserAgent();
    }
}
