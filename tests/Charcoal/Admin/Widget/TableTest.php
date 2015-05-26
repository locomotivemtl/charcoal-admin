<?php

namespace Charcoal\Admin\Tests\Widget;

use \Charcoal\Admin\Widget\Table as Table;

class TableTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Table();
        $this->assertInstanceOf('\Charcoal\Admin\Widget\Table', $obj);
    }
}
