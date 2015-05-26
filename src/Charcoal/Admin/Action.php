<?php

namespace Charcoal\Admin;

use Charcoal\Charcoal as Charcoal;

// From `charcoal-base`
use Charcoal\Action\AbstractAction as AbstractAction;

/**
*
*/
class Action extends AbstractAction
{
    /**
    * @param integer $http_code
    * @throws \Exception if mode is invalid
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
            throw new \Exception('Invalid mode');
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
