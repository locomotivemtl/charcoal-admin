<?php

namespace Charcoal\Admin\Tests\Ui;

use \Charcoal\Admin\Ui\TableProperty as TableProperty;

class TablePropertyTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new TableProperty();
		$this->assertInstanceOf('\Charcoal\Admin\Ui\TableProperty', $obj);
	}
}
