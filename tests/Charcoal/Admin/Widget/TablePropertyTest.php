<?php

namespace Charcoal\Admin\Tests\Widget;

use \Charcoal\Admin\Widget\TableProperty as TableProperty;

class TablePropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new TableProperty();
        $this->assertInstanceOf('\Charcoal\Admin\Widget\TableProperty', $obj);
    }
}
