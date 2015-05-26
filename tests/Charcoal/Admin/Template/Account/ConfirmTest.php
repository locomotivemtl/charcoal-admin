<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\Account\Confirm as Confirm;

class ConfirmTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Confirm();
        $this->assertInstanceOf('\Charcoal\Admin\Template\Account\Confirm', $obj);
    }
}
