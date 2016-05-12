<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\AdminModule as AdminModule;

class AdminModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Hello world
     */
    public function testConstructor()
    {
        $obj = new AdminModule([
            'logger'    => new \Psr\Log\NullLogger(),
            'config'    => null,
            'app'       => $GLOBALS['app']
        ]);
        $this->assertInstanceOf('\Charcoal\Admin\AdminModule', $obj);
    }
}
