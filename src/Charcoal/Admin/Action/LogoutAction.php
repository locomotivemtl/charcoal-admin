<?php

namespace Charcoal\Admin\Action;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// Intra-module (`charcoal-admin`) dependencies
use Charcoal\Admin\AdminAction;
use Charcoal\Admin\User;
use Charcoal\Admin\User\AuthToken;

/**
 * Log Out Action
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
        if ($user) {
            $result = $user->logout();
            $this->deleteUserAuthTokens($user);
            $this->setSuccess($result);
        }

        return $response;
    }

    /**
     * @param User $user The user to clear auth tokens for.
     * @return LogoutTemplate Chainable
     */
    private function deleteUserAuthTokens(User $user)
    {
        $token = $this->modelFactory()->create(AuthToken::class);

        if ($token->source()->tableExists()) {
            $table = $token->source()->table();
            $q = 'DELETE FROM '.$table.' WHERE username = :username';
            $token->source()->dbQuery($q, [ 'username' => $user->username() ]);
        }

        return $this;
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
