<?php

namespace Charcoal\Admin\Tests\Template;

use \Charcoal\Admin\Template\Help as Help;

class HelpTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Help();
		$this->assertInstanceOf('\Charcoal\Admin\Template\Help', $obj);
	}
}
