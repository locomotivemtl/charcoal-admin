<?php

namespace Charcoal\Admin\Tests\Widget\Graph;

use \Charcoal\Admin\Widget\Graph\Bar as Bar;

class BarTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Bar();
        $this->assertInstanceOf('\Charcoal\Admin\Widget\Graph\Bar', $obj);
    }
}
