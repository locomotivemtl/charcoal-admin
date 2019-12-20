<?php

namespace Charcoal\Admin\Template\Account;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Template\AuthTemplateTrait;
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
    use AuthTemplateTrait;

    /**
     * @var string|null
     */
    private $lostPasswordToken;

    /**
     * Determine if the password token is valid.
     *
     * @param  RequestInterface $request The PSR-7 HTTP request.
     * @return boolean
     */
    public function init(RequestInterface $request)
    {
        // Undocumented Slim 3 feature: The route attributes are stored in routeInfo[2].
        $routeInfo = $request->getAttribute('routeInfo');

        if (isset($routeInfo[2]['token'])) {
            $this->lostPasswordToken = $routeInfo[2]['token'];
        } else {
            $this->lostPasswordToken = $request->getParam('token');
        }

        if ($this->lostPasswordToken && $this->validateToken($this->lostPasswordToken)) {
            return true;
        }

        header('HTTP/1.0 400 Bad Request');
        header('Location: '.$this->adminUrl('account/lost-password?notice=invalidtoken'));
        exit;
    }

    /**
     * @return string|null
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
        return $this->adminUrl('account/reset-password');
    }

    /**
     * Validate the given password reset token.
     *
     * To be valid, a token should:
     *
     * - exist in the database
     * - not be expired
     *
     * @see    \Charcoal\Admin\Action\Account\ResetPasswordAction::validateToken()
     * @param  string $token The token to validate.
     * @return boolean
     */
    private function validateToken($token)
    {
        $obj = $this->modelFactory()->create(LostPasswordToken::class);
        $sql = strtr('SELECT * FROM `%table` WHERE `token` = :token AND `expiry` > NOW()', [
            '%table' => $obj->source()->table()
        ]);
        $obj->loadFromQuery($sql, [
            'token' => $token
        ]);

        return !!$obj['token'];
    }

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle($this->translator()->translation('Password Reset'));
        }

        return $this->title;
    }

    /**
     * Retrieve the parameters for the Google reCAPTCHA widget.
     *
     * @return string[]
     */
    public function recaptchaParameters()
    {
        $params = parent::recaptchaParameters();
        $params['tabindex'] = 5;

        if ($this->recaptchaInvisible() === true) {
            $params['callback'] = 'CharcoalCaptchaChangePassCallback';
        }

        return $params;
    }



    // Templating
    // =========================================================================

    /**
     * Determine if main & secondary menu should appear as mobile in a desktop resolution.
     *
     * @return boolean
     */
    public function isFullscreenTemplate()
    {
        return true;
    }
}
