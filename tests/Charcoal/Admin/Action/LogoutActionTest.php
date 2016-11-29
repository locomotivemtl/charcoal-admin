<?php

namespace Charcoal\Admin\Tests\Action;

use \PHPUnit_Framework_TestCase;

// PSR-3 (logger) dependencies
use \Psr\Log\NullLogger;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

use \Charcoal\Admin\Action\LogoutAction;

/**
 *
 */
class LogoutActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Instance of object under test
     * @var LoginAction
     */
    private $obj;

    public function setUp()
    {
        $this->obj = new LogoutAction([
            'logger' => new NullLogger()
        ]);
    }

    public function testAuthRequiredIsTrue()
    {
        $this->assertTrue($this->obj->authRequired());
    }
}
