<?php

namespace Charcoal\Tests\Admin\Action\Account;

use ReflectionClass;

// From Mockery
use Mockery as m;

// From Pimple
use Pimple\Container;

// From Slim
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

// From 'charcoal-admin'
use Charcoal\Admin\Action\Account\ResetPasswordAction;
use Charcoal\Admin\User;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class ResetPasswordActionTest extends AbstractTestCase
{
    use ReflectionsTrait;

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
     *
     * @return void
     */
    public function setUp(): void
    {
        $container = $this->container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerActionDependencies($container);

        $this->obj = new ResetPasswordAction([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    /**
     * @return void
     */
    public function testAuthRequiredIsFalse()
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertFalse($res);
    }

    /**
     * @return void
     */
    public function testRunWithoutTokenReturns400()
    {
        $request  = Request::createFromEnvironment(Environment::mock());
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    /**
     * @return void
     */
    public function testRunWithoutEmailReturns400()
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

    /**
     * @return void
     */
    public function testRunWithoutPasswordReturns400()
    {
        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'token=foobar&email=foobar@foo.bar'
        ]));
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    /**
     * @return void
     */
    public function testRunWithoutMatchingPasswordsReturns400()
    {
        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'token=foobar&email=foobar@foo.bar&password1=foo&password2=bar'
        ]));
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    /**
     * @return void
     */
    public function testRunWithoutRecaptchaReturns400()
    {
        $mock = m::mock($this->obj);
        $mock->shouldAllowMockingProtectedMethods()
             ->shouldReceive('validateCaptcha')
                ->with(null)
                    ->andReturn(false);

        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'token=foobar&email=foobar@foo.bar&password1=foo&password2=foo'
        ]));
        $response = new Response();

        $response = $mock->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $mock->results();
        $this->assertFalse($results['success']);
    }

    /**
     * @return void
     */
    public function testRunWithInvalidRecaptchaReturns400()
    {
        $mock = m::mock($this->obj);
        $mock->shouldAllowMockingProtectedMethods()
             ->shouldReceive('validateCaptcha')
                ->with('foobar')
                    ->andReturn(false);

        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'token=foobar&email=foobar@foo.bar&password1=foo&password2=foo&g-recaptcha-response=foobar'
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
    protected function container()
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
