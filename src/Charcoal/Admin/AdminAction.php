<?php

namespace Charcoal\Admin;

use \Pimple\Container;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\FactoryInterface;

// Module `charcoal-app` dependencies
use Charcoal\App\Action\AbstractAction;

/**
 * The base class for the `admin` Actions.
 *
 */
abstract class AdminAction extends AbstractAction
{
    /**
     * @var array $feedbacks
     */
    private $feedbacks = [];

    /**
     * @var FactoryInterface $modelFactory
     */
    private $modelFactory;

    /**
    * @param array $data Optional.
    */
    final public function __construct(array $data = null)
    {
        parent::__construct($data);

        if ($data !== null) {
            $this->setData($data);
        }

        if ($this->authRequired() === true) {
            // @todo Authentication
            $this->auth();
        }
    }

    /**
     * Dependencies
     * @param Container $container DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setModelFactory($container['model/factory']);
    }

    /**
     * @param FactoryInterface $factory The factory used to create models.
     * @return AdminScript Chainable
     */
    protected function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
        return $this;
    }

    /**
     * @return FactoryInterface The model factory.
     */
    protected function modelFactory()
    {
        return $this->modelFactory;
    }


    /**
     * Authentication is required by default.
     *
     * Reimplement and change to false in templates that do not require authentication.
     *
     * @return boolean
     */
    public function authRequired()
    {
        return false;
    }

    /**
     * Determine if the current user is authenticated. If not it redirects them to the login page.
     *
     * @return void
     */
    private function auth()
    {
        $u = User::getAuthenticated();
        if ($u === null || !$u->id()) {
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

    /**
     * @param string $level The feedback level.
     * @param mixed  $msg   The actual feedback message.
     * @return AdminAction Chainable
     */
    public function addFeedback($level, $msg)
    {
        $this->feedbacks[] = [
            'msg'   => $msg,
            'level' => $level
        ];
        return $this;
    }

    /**
     * Default response stub.
     *
     * @return array
     */
    public function results()
    {
        $results = [
            'success'   => $this->success(),
            'next_url'  => $this->redirectUrl(),
            'feedbacks' => $this->feedbacks()
        ];
        return $results;
    }
}
