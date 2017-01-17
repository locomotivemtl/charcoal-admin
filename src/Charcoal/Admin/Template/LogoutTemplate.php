<?php

namespace Charcoal\Admin\Template;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From 'charcoal-translation'
use Charcoal\Translation\TranslationString;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\User;
use Charcoal\Admin\User\AuthToken;

/**
 * Logout template
 */
class LogoutTemplate extends AdminTemplate
{
    /**
     * @param RequestInterface $request The request to initialize.
     * @return boolean
     */
    public function init(RequestInterface $request)
    {
        $user = User::getAuthenticated($this->modelFactory());

        if ($user) {
            $user->logout();
            $this->deleteUserAuthTokens($user);
        }

        return parent::init($request);
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
            $token->source()->dbQuery($q, [ 'username'=>$user->username() ]);
        }

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

    /**
     * Retrieve the title of the page.
     *
     * @return TranslationString|string|null
     */
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle([
                'en' => 'Logged Out',
                'fr' => 'Déconnecté(e)',
            ]);
        }

        return $this->title;
    }
}
