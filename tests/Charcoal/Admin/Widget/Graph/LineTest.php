<?php

namespace Charcoal\Admin\Tests\Widget\Graph;

use \Charcoal\Admin\Widget\Graph\Line as Line;

class LineTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Line();
        $this->assertInstanceOf('\Charcoal\Admin\Widget\Graph\Line', $obj);
    }
}
