<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\Action\LoginAction as LoginAction;

class LoginActionTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new LoginAction();
        $this->assertInstanceOf('\Charcoal\Admin\Action\Login', $obj);
    }
}
