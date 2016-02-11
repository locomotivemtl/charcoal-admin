<?php

namespace Charcoal\Admin;

use \Exception;

// Module `charcoal-app` dependencies
use \Charcoal\App\Script\AbstractScript;

// Module `charcoal-property` dependencies
use \Charcoal\Property\PropertyInterface;

// Intract-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\User;

/**
 *
 */
abstract class AdminScript extends AbstractScript
{
    public function init()
    {
        parent::init();

        // Authenticate terminal user as an admin user.
        if ($this->authRequired() !== false) {
            $this->auth();
        }
    }

    /**
     * Determine if user authentication is required.
     *
     * Authentication is required by default. If unnecessary,
     * replace this method in the inherited template class.
     *
     * @see \Charcoal\Admin\Template::authRequired()
     *
     * @return boolean
     */
    public function authRequired()
    {
        return false;
    }

    /**
     *
     */
    protected function auth()
    {
        $climate = $this->climate();

        $u = User::getAuthenticated();
        if ($u === null) {
            $climate->yellow()->out(
                'You need to be logged in into your "admin" account to continue...'
            );

            $input = $climate->input(
                'Please enter your username:'
            );
            $username = $input->prompt();

            $input = $climate->password(
                'Please enter your password (hidden):'
            );
            $password = $input->prompt();

            $climate->br();

            $this->logger->debug(
                sprintf('Admin login attempt: "%s"', $username)
            );

            try {
                $u = new User([
                    'logger' => $this->logger
                ]);
                $is_authenticated = $u->authenticate($username, $password);
            } catch (Exception $e) {
                $climate->dump($e);
                $is_authenticated = false;
            }

            if (!$is_authenticated) {
                $this->logFailedAttempt($username);
                $climate->br()->error(
                    'Authentication failed.'
                );
                die();
            } else {
                $this->logSuccessfulLogin($username);
            }
        }
    }

    protected function logFailedAttempt($username)
    {
        $this->logger->warning(
            sprintf('Login attempt failure: "%s"', $username)
        );
    }

    protected function logSuccessfulLogin($username)
    {
        $this->logger->debug(
            sprintf('Login attempt successful: "%s"', $username)
        );
    }

    /**
     * @param PropertyInterface $prop The property to retrieve input from.
     * @return
     */
    protected function propertyToInput(PropertyInterface $prop)
    {
        $climate = $this->climate();

        if ($prop->type() == 'password') {
            return $this->passwordInput($prop);
        } else if ($prop->type() == 'boolean') {
            return $this->booleanInput($prop);
        } else {
            $input = $climate->input(
                sprintf('Enter value for "%s":', $prop->label())
            );
            if ($prop->type() == 'text' || $prop->type == 'html') {
                $input->multiLine();
            }
        }
        return $input;
    }

    /**
     * Get a CLI input from a boolean property.
     *
     * @param PropertyInterface $prop The property to retrieve input from.
     * @return \League\CLImate\TerminalObject\Dynamic\Input
     */
    private function booleanInput(PropertyInterface $prop)
    {
        $climate = $this->climate();

        $opts = [
            1 => $prop->trueLabel(),
            0 => $prop->falseLabel()
        ];
        $input = $climate->radio(
            sprintf('Enter value for "%s":', $prop->label()),
            $opts
        );
        return $input;
    }

    /**
     * Get a CLI password input (hidden) from a password property.
     *
     * @param PropertyInterface $prop The property to retrieve input from.
     * @return \League\CLImate\TerminalObject\Dynamic\Input
     */
    private function passwordInput(PropertyInterface $prop)
    {
        $climate = $this->climate();

        $input = $climate->password(
            sprintf('Enter value for "%s":', $prop->label())
        );
        return $input;
    }
}
