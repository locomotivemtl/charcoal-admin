<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\Object as Object;

class ObjectTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Object();
		$this->assertInstanceOf('\Charcoal\Admin\Template\Object', $obj);
	}
}
