<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\Account\Logout as Logout;

class LogoutTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Logout();
        $this->assertInstanceOf('\Charcoal\Admin\Template\Account\Logout', $obj);
    }
}
