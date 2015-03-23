<?php

namespace Charcoal\Admin\Tests\Ui;

use \Charcoal\Admin\Ui\Dashboard as Dashboard;

class DashboardTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Dashboard();
		$this->assertInstanceOf('\Charcoal\Admin\Ui\Dashboard', $obj);
	}
}
