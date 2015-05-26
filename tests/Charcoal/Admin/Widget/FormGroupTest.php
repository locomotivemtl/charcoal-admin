<?php

namespace Charcoal\Admin\Tests\Widget;

use \Charcoal\Admin\Widget\FormGroup as FormGroup;

class FormGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new FormGroup();
        $this->assertInstanceOf('\Charcoal\Admin\Widget\FormGroup', $obj);
    }
}
