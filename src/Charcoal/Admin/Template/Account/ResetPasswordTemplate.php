<?php

namespace Charcoal\Admin\Template\Account;

use Psr\Http\Message\RequestInterface;

use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\User\LostPasswordToken;

/**
 * Reset Password Template
 *
 * This template, which does not require authentication, allows a user to reset its password
 * if they can provide a valid lost-password token, that should have been sent to their email address.
 *
 * Related: {@see \Charcoal\Admin\Template\Account\LostPasswordTemplate Lost Password Template}
 */
class ResetPasswordTemplate extends AdminTemplate
{
    /**
     * @var string
     */
    private $lostPasswordToken;

    /**
     * @param RequestInterface $request The PSR-7 HTTP request.
     * @return boolean
     */
    public function init(RequestInterface $request)
    {
        // Undocumented Slim3 feature: The route attributes are stored in routeInfo[2].
        $routeInfo = $request->getAttribute('routeInfo');
        if (isset($routeInfo[2]['token'])) {
            $this->lostPasswordToken = $routeInfo[2]['token'];
        } else {
            $this->lostPasswordToken = $request->getParam('token');
        }
        if ($this->lostPasswordToken) {
            if (!$this->validateToken($this->lostPasswordToken)) {
                $this->lostPasswordToken = false;
                $this->addFeedback('warning', 'Invalid or expired token.');
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function lostPasswordToken()
    {
        return $this->lostPasswordToken;
    }


    /**
     * @return boolean
     */
    public function authRequired()
    {
        return false;
    }

    /**
     * @return string
     */
    public function urlResetPasswordAction()
    {
        return 'action/account/reset-password';
    }

    /**
     * @return string
     */
    public function urlLostPassword()
    {
        return 'account/lost-password';
    }

    /**
     * To be valid, a token should:
     *
     * - exist in the database
     * - not be expired
     *
     * @param string $token The token to validate.
     * @return boolean
     */
    private function validateToken($token)
    {
        $tokenProto = $this->modelFactory()->create(LostPasswordToken::class);
        $q = '
        select
            *
        from
            `'.$tokenProto->source()->table().'`
        where
            `token`=:token
        and
            `expiry` > NOW()';
        $tokenProto->loadFromQuery($q, ['token'=>$token]);
        return !!$tokenProto->token();
    }

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation
     */
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle([
                'en' => 'Reset Password',
                'fr' => 'Réinitialisation du mot de passe',
            ]);
        }

        return $this->title;
    }
}
