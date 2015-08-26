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
    * Default response stub
    *
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
