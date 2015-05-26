<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\Account\Info as Info;

class InfoTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Info();
        $this->assertInstanceOf('\Charcoal\Admin\Template\Account\Info', $obj);
    }
}
