<?php

namespace Charcoal\Admin\Tests;

use \Charcoal\Admin\Template as Template;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Template();
		$this->assertInstanceOf('\Charcoal\Admin\Template', $obj);
	}
}
