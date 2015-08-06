<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\AdminAction as AdminAction;

class AdminActionTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new AdminAction();
        $this->assertInstanceOf('\Charcoal\Admin\Action', $obj);
    }

}
