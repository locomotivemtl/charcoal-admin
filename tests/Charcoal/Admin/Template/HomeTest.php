<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\Home as Home;

class HomeTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Home();
		$this->assertInstanceOf('\Charcoal\Admin\Template\Home', $obj);
	}
}
