<?php

namespace Charcoal\Admin\Tests\Widget;

use \Charcoal\Admin\Widget\Layout as Layout;

class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Layout();
        $this->assertInstanceOf('\Charcoal\Admin\Widget\Layout', $obj);
    }
}
