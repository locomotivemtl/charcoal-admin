<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\System\Clearcache as Clearcache;

class ClearcacheTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Clearcache();
        $this->assertInstanceOf('\Charcoal\Admin\Template\System\Clearcache', $obj);
    }
}
