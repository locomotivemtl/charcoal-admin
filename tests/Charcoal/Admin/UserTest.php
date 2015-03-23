<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\User as User;

class UserTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new User();
		$this->assertInstanceOf('\Charcoal\Admin\User', $obj);
	}
}
