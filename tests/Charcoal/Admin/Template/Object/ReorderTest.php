<?php

namespace Charcoal\Admin\Tests\Template\Object;

use \Charcoal\Admin\Template\Object\Reorder as Reorder;

class ReorderTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Reorder();
		$this->assertInstanceOf('\Charcoal\Admin\Template\Object\Reorder', $obj);
	}
}
