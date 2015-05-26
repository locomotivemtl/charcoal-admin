<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\UserGroup as UserGroup;

class UserGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new UserGroup();
        $this->assertInstanceOf('\Charcoal\Admin\UserGroup', $obj);
    }
}
