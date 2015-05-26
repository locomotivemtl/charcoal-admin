<?php

namespace Charcoal\Admin\Tests\Widget;

use \Charcoal\Admin\Widget\FormProperty as FormProperty;

class FormPropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new FormProperty();
        $this->assertInstanceOf('\Charcoal\Admin\Widget\FormProperty', $obj);
    }
}
