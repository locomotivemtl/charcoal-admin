<?php

namespace Charcoal\Admin\Action;

use Exception;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;

/**
 * Action: Attempt to log a user out.
 *
 * ## Response
 *
 * - `success` (_boolean_) — TRUE if the user was properly logged out, FALSE in case of any error.
 *
 * ## HTTP Status Codes
 *
 * - `200` — Successful; User has been safely logged out
 * - `500` — Server error; User could not be logged out
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

        try {
            $translator = $this->translator();

            $doneMessage = $translator->translation('You are now logged out.');
            $failMessage = $translator->translation('An error occurred while logging out');
            $errorThrown = strtr($translator->translation('{{ errorMessage }}: {{ errorThrown }}'), [
                '{{ errorMessage }}' => $failMessage
            ]);

            $authenticator = $this->authenticator();

            if ($authenticator->check()) {
                $authenticator->logout();

                $this->addFeedback('success', $doneMessage);
                $this->setSuccess(true);

                return $response->withStatus(204);
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            $this->addFeedback('error', strtr($errorThrown, [
                '{{ errorThrown }}' => $e->getMessage()
            ]));
            $this->setSuccess(false);

            return $response->withStatus(500);
        }

        /** Fail silently — Never confirm or deny the existence of an account. */
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
        if ($ip) {
            $logMessage = sprintf('[Admin] Logout attempt for unauthenticated user from %s', $ip);
        } else {
            $logMessage = '[Admin] Logout attempt for unauthenticated user';
        }
        $this->logger->warning($logMessage);

        $this->addFeedback('error', $failMessage);
        $this->setSuccess(false);

        return $response->withStatus(401);
    }

    /**
     * @todo   Provide feedback and redirection?
     * @return array
     */
    public function results()
    {
        return [
            'success' => $this->success(),
        ];
    }
}
