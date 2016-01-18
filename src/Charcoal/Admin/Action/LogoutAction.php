<?php

namespace Charcoal\Admin\Action;

// Dependencies from `PHP`
use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

use \Charcoal\Admin\AdminAction as AdminAction;
use \Charcoal\Admin\User as User;

/**
*
*/
class LogoutAction extends AdminAction
{
    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $user = User::getAuthenticated();
        $res = $user->logout();
        $this->setSuccess($res);

        return $response;
    }

    /**
    * @return array
    */
    public function results()
    {
        $results = [
            'success'   => $this->success()
        ];
        return $results;
    }
}
