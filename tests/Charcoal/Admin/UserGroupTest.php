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
        $container = $GLOBALS['container'];
        $logger = new \Psr\Log\NullLogger();
        $obj = new UserGroup([
            'logger'=>$logger,
            'metadata_loader' => $container['metadata/loader']
        ]);
        $this->assertInstanceOf('\Charcoal\Admin\UserGroup', $obj);
    }
}
