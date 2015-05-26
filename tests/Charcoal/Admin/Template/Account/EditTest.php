<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\Account\Edit as Edit;

class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Edit();
        $this->assertInstanceOf('\Charcoal\Admin\Template\Account\Edit', $obj);
    }
}
