<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\Account\Lostpassword as Lostpassword;

class LostpasswordTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Lostpassword();
        $this->assertInstanceOf('\Charcoal\Admin\Template\Account\Lostpassword', $obj);
    }
}
