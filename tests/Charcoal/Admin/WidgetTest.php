<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\Widget as Widget;

class WidgetTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Widget();
		$this->assertInstanceOf('\Charcoal\Admin\Widget', $obj);
	}
}
