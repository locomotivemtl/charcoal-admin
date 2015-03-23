<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\Login as Login;

class LoginTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Login();
		$this->assertInstanceOf('\Charcoal\Admin\Template\Login', $obj);
	}
}
