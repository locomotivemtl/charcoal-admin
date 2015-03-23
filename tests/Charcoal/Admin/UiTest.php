<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\Ui as Ui;

class UiTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Ui();
		$this->assertInstanceOf('\Charcoal\Admin\Ui', $obj);
	}
}
