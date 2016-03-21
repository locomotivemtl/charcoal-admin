<?php

namespace Charcoal\Admin\Template;

// Local parent namespace dependencies
use \Charcoal\Admin\AdminTemplate;
use \Charcoal\Admin\User;

use \Charcoal\Admin\Object\AuthToken;

/**
 * Logout template
 */
class LogoutTemplate extends AdminTemplate
{

    /**
     * @param array|\ArayAccess $data
     */
    public function __construct($data)
    {
        $user = User::getAuthenticated();
        if($user) {
            $user->logout();
            $this->deleteUserAuthTokens($user);
        }

        parent::__construct($data);
    }

    /**
     * @param UserInterface $user
     * @return LogoutTemplate Chainable
     */
    private function deleteUserAuthTokens($user)
    {
        $token = new AuthToken([
            'logger' => $this->logger
        ]);

        $table = $token->source()->table();
        $q = 'delete from '.$table.' where username = :username';
        $token->source()->dbQuery($q, ['username'=>$user->username()]);
        return $this;
    }

    /**
     * Authentication is obviously never required for the login page.
     *
     * @return boolean
     */
    protected function authRequired()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function showHeaderMenu()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function showFooterMenu()
    {
        return false;
    }
}
