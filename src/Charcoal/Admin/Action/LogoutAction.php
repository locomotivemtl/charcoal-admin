<?php

namespace Charcoal\Admin\Action;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// Intra-module (`charcoal-admin`) dependencies
use Charcoal\Admin\AdminAction;
use Charcoal\Admin\User;

/**
 * Logout Action
 */
class LogoutAction extends AdminAction
{
    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     * @todo   This should be done via an Authenticator object.
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $user = User::getAuthenticated($this->modelFactory());
        $res = $user->logout();
        $this->setSuccess($res);

        return $response;
    }

    /**
     * @return array
     */
    public function results()
    {
        return [
            'success' => $this->success()
        ];
    }
}
