<?php

namespace Charcoal\Admin\Tests\Ui;

use \Charcoal\Admin\Ui\Table as Table;

class TableTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Table();
		$this->assertInstanceOf('\Charcoal\Admin\Ui\Table', $obj);
	}
}
