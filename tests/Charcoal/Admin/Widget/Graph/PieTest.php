<?php

namespace Charcoal\Admin\Tests\Widget\Graph;

use \Charcoal\Admin\Widget\Graph\Pie as Pie;

class PieTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Pie();
        $this->assertInstanceOf('\Charcoal\Admin\Widget\Graph\Pie', $obj);
    }
}
