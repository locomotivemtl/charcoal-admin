<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\Module as Module;

class ModuleTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Module();
		$this->assertInstanceOf('\Charcoal\Admin\Module', $obj);
	}
}
