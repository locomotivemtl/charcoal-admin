<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\System\Checkenv as Checkenv;

class CheckenvTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Checkenv();
        $this->assertInstanceOf('\Charcoal\Admin\Template\System\Checkenv', $obj);
    }
}
