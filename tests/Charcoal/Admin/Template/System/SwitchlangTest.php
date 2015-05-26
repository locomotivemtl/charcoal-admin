<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\System\Switchlang as Switchlang;

class SwitchlangTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Switchlang();
        $this->assertInstanceOf('\Charcoal\Admin\Template\System\Switchlang', $obj);
    }
}
