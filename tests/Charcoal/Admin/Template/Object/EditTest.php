<?php

namespace Charcoal\Admin\Tests\Template\Object;

use \Charcoal\Admin\Template\Object\Edit as Edit;

class EditTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Edit();
		$this->assertInstanceOf('\Charcoal\Admin\Template\Object\Edit', $obj);
	}
}
