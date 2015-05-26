<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\System as System;

class SystemTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new System();
        $this->assertInstanceOf('\Charcoal\Admin\Template\System', $obj);
    }
}
