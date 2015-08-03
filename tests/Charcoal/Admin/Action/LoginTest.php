<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\Action\Login as Login;

class LoginTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Login();
        $this->assertInstanceOf('\Charcoal\Admin\Action\Login', $obj);
    }
}
