<?php

namespace Charcoal\Admin\Tests\Action\Account;

// From PHPUnit
use \PHPUnit_Framework_TestCase;

// From Mockery
use \Mockery as m;

// From Pimple
use \Pimple\Container;

// From Slim
use \Slim\Http\Environment;
use \Slim\Http\Request;
use \Slim\Http\Response;

// From 'charcoal-admin'
use \Charcoal\Admin\Action\Account\ResetPasswordAction;
use \Charcoal\Admin\User;

use \Charcoal\Admin\Tests\ContainerProvider;

/**
 *
 */
class ResetPasswordActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var ResetPasswordAction
     */
    public $obj;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $container = $this->container();
        $this->obj = new ResetPasswordAction([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    public function testAuthRequiredIsFalse()
    {
        $this->assertFalse($this->obj->authRequired());
    }

    public function testRunWithoutTokenReturns400()
    {
        $request  = Request::createFromEnvironment(Environment::mock());
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    public function testRunWithoutUsernameReturns400()
    {
        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'token=foobar'
        ]));
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    public function testRunWithoutPasswordReturns400()
    {
        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'token=foobar&username=foobar'
        ]));
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    public function testRunWithoutMatchingPasswordsReturns400()
    {
        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'token=foobar&username=foobar&password1=foo&password2=bar'
        ]));
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    public function testRunWithoutRecaptchaReturns400()
    {
        $mock = m::mock($this->obj);
        $mock->shouldAllowMockingProtectedMethods()
             ->shouldReceive('validateCaptcha')
                ->with(null)
                    ->andReturn(false);

        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'token=foobar&username=foobar&password1=foo&password2=foo'
        ]));
        $response = new Response();

        $response = $mock->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $mock->results();
        $this->assertFalse($results['success']);
    }

    public function testRunWithInvalidRecaptchaReturns400()
    {
        $mock = m::mock($this->obj);
        $mock->shouldAllowMockingProtectedMethods()
             ->shouldReceive('validateCaptcha')
                ->with('foobar')
                    ->andReturn(false);

        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'token=foobar&username=foobar&password1=foo&password2=foo&g-recaptcha-response=foobar'
        ]));
        $response = new Response();

        $response = $mock->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $mock->results();
        $this->assertFalse($results['success']);
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerAdminServices($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
