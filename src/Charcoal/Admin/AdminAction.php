<?php

namespace Charcoal\Admin;

// Module `charcoal-core` dependencies
use Charcoal\Charcoal;

// Module `charcoal-app` dependencies
use Charcoal\App\Action\AbstractAction;

/**
 * The base class for the `admin` Actions.
 *
 * @see \Charcoal\App\App The `Slim` application inside the core Charcoal object, used to set response.
 */
abstract class AdminAction extends AbstractAction
{
    private $feedbacks = [];

    /**
    * @param array $data Optional
    */
    final public function __construct(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        if ($this->authRequired() === true) {
            // @todo Authentication
            $this->auth();
        }
    }

    /**
     * Authentication is required by default.
     *
     * Change to false in
     *
     * @return boolean
     */
    public function authRequired()
    {
        return false;
    }

    /**
     * Determine if the current user is authenticated. If not it redirects them to the login page.
     */
    private function auth()
    {
        //$cfg = AdminModule::config();
        $u = User::getAuthenticated();
        if ($u === null) {
            die('Auth required');
        }
    }

    /**
     * @return boolean
     */
    public function hasFeedbacks()
    {
        return (count($this->feedbacks()) > 0);
    }

    /**
     * @return integer
     */
    public function numFeedbacks()
    {
        return count($this->feedbacks());
    }

    /**
     * @return array
     */
    public function feedbacks()
    {
        return $this->feedbacks;
    }

    public function addFeedback($level, $msg)
    {
        $this->feedbacks[] = [
            'msg'=>$msg,
            'level'=>$level
        ];
    }

    /**
     * Default response stub
     *
     * @return array
     */
    public function results()
    {
        $success = $this->success();

        $results = [
            'success'=>$this->success(),
            'next_url'=>$this->redirectUrl(),
            'feedbacks'=>$this->feedbacks()
        ];
        return $results;
    }
}
