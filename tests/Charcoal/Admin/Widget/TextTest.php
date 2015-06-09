<?php

namespace Charcoal\Admin\Tests\Widget;

use \Charcoal\Admin\Widget\Text as Text;

class TextTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Text();
        $this->assertInstanceOf('\Charcoal\Admin\Widget\Text', $obj);
    }
}
