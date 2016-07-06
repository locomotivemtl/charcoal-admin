<?php

namespace Charcoal\Admin\Tests;

use \Psr\Log\NullLogger;
use \Charcoal\Admin\AdminModule;

class AdminModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Hello world
     */
    public function testConstructor()
    {
        $obj = new AdminModule([
            'logger'    => new NullLogger(),
            'config'    => null,
            'app'       => $GLOBALS['app']
        ]);
        $this->assertInstanceOf('\Charcoal\Admin\AdminModule', $obj);
    }
}
