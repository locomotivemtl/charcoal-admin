<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\Action as Action;

class ActionTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Action();
        $this->assertInstanceOf('\Charcoal\Admin\Action', $obj);
    }

}
