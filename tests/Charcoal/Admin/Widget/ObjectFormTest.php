<?php

namespace Charcoal\Admin\Tests\Widget;

use \Charcoal\Admin\Widget\ObjectForm as ObjectForm;

class ObjectFormTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new ObjectForm();
        $this->assertInstanceOf('\Charcoal\Admin\Widget\ObjectForm', $obj);
    }
}
