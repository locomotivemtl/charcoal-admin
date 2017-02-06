<?php

namespace Charcoal\Admin\Tests\Action\Account;

use \PHPUnit_Framework_TestCase;

use \Pimple\Container;

use \Slim\Http\Environment;
use \Slim\Http\Request;
use \Slim\Http\Response;

use \Charcoal\Admin\Action\Account\LostPasswordAction;

use \Charcoal\Admin\Tests\ContainerProvider;

use \Charcoal\Admin\User;

/**
 *
 */
class LostPasswordActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Instance of object under test
     * @var LoginAction
     */
    private $obj;

    private $container;

    /**
     *
     */
    public function setUp()
    {
        $container = $this->container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerActionDependencies($container);

        $this->obj = new LostPasswordAction([
            'logger' => $container['logger'],
            'container' => $container
        ]);
    }

    /**
     *
     */
    public function testAuthRequiredIsFalse()
    {
        $this->assertFalse($this->obj->authRequired());
    }

    public function testRunWithoutUsernameReturns404()
    {
         $request = Request::createFromEnvironment(Environment::mock());
        $response = new Response();

        $res = $this->obj->run($request, $response);
        $this->assertEquals(404, $res->getStatusCode());

        $res = $this->obj->results();
        $this->assertFalse($res['success']);
    }

    public function testRunWithoutRecaptchaReturns404()
    {
         $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'username=foobar'
         ]));
        $response = new Response();

        $res = $this->obj->run($request, $response);
        $this->assertEquals(404, $res->getStatusCode());

        $res = $this->obj->results();
        $this->assertFalse($res['success']);
    }

    public function testRunWithInvalidRecaptchaReturns404()
    {
         $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'username=foobar&g-recaptcha-response=foobar'
         ]));
        $response = new Response();

        $res = $this->obj->run($request, $response);
        $this->assertEquals(404, $res->getStatusCode());

        $res = $this->obj->results();
        $this->assertFalse($res['success']);
    }

    /**
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseUrl($container);
            $containerProvider->registerAdminConfig($container);
            $containerProvider->registerAuthenticator($container);
            $containerProvider->registerAuthorizer($container);
            $containerProvider->registerEmailFactory($container);

            $this->container = $container;
        }
        return $this->container;
    }
}
