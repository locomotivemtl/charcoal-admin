<?php

namespace Charcoal\Admin\Tests\Action\Account;

use \PHPUnit_Framework_TestCase;

use \Pimple\Container;

use \Slim\Http\Environment;
use \Slim\Http\Request;
use \Slim\Http\Response;

use \Charcoal\Admin\Action\Account\ResetPasswordAction;

use \Charcoal\Admin\Tests\ContainerProvider;

use \Charcoal\Admin\User;

/**
 *
 */
class ResetPasswordActionTest extends PHPUnit_Framework_TestCase
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
        $this->obj = new ResetPasswordAction([
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

    public function testRunWithoutTokenReturns404()
    {
         $request = Request::createFromEnvironment(Environment::mock());
        $response = new Response();

        $res = $this->obj->run($request, $response);
        $this->assertEquals(404, $res->getStatusCode());

        $res = $this->obj->results();
        $this->assertFalse($res['success']);
    }

    public function testRunWithoutUsernameReturns404()
    {
         $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'token=foobar'
        ]));
        $response = new Response();

        $res = $this->obj->run($request, $response);
        $this->assertEquals(404, $res->getStatusCode());

        $res = $this->obj->results();
        $this->assertFalse($res['success']);
    }

    public function testRunWithoutPasswordReturns404()
    {
         $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'token=foobar&username=foobar'
        ]));
        $response = new Response();

        $res = $this->obj->run($request, $response);
        $this->assertEquals(404, $res->getStatusCode());

        $res = $this->obj->results();
        $this->assertFalse($res['success']);
    }

    public function testRunWithoutMatchingPasswordsReturns404()
    {
         $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'token=foobar&username=foobar&password=foo&password_confirm=bar'
        ]));
        $response = new Response();

        $res = $this->obj->run($request, $response);
        $this->assertEquals(404, $res->getStatusCode());

        $res = $this->obj->results();
        $this->assertFalse($res['success']);
    }

    public function testRunWithoutRecaptchaReturns404()
    {
         $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'token=foobar&username=foobar&password=foo&password_confirm=foo'
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
            'QUERY_STRING' => 'token=foobar&username=foobar&password=foo&password_confirm=foo&g-recaptcha-response=foobar'
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

            $this->container = $container;
        }
        return $this->container;
    }
}
