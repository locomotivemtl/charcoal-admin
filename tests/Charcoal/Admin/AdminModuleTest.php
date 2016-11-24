<?php

namespace Charcoal\Admin\Tests;

use \PHPUnit_Framework_TestCase;

use \Psr\Log\NullLogger;

use \Charcoal\App\App;

use \Charcoal\Admin\AdminModule;

/**
 *
 */
class AdminModuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * Hello world
     */
    public function testConstructor()
    {
        $obj = new AdminModule([
            'logger'    => new NullLogger(),
            'config'    => null,
            'app'       => App::instance($GLOBALS['container'])
        ]);
        $this->assertInstanceOf('\Charcoal\Admin\AdminModule', $obj);
    }
}
