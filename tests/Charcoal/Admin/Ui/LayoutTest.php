<?php

namespace Charcoal\Admin\Tests\Ui;

use \Charcoal\Admin\Ui\Layout as Layout;

class LayoutTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Layout();
		$this->assertInstanceOf('\Charcoal\Admin\Ui\Layout', $obj);
	}
}
