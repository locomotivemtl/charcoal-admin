<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\Account as Account;

class AccountTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Account();
        $this->assertInstanceOf('\Charcoal\Admin\Template\Account', $obj);
    }
}
