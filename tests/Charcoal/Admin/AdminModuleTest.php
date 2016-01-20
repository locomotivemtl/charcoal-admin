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
            'config'=>null,
            'app'=>null
        ]);
        $this->assertInstanceOf('\Charcoal\Admin\AdminModule', $obj);
    }
}
