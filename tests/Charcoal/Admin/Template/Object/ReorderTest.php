<?php

namespace Charcoal\Admin\Tests\Template\Object;

use \Charcoal\Admin\Template\Object\Reorder as Colllection;

class ReorderTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Reorder();
		$this->assertInstanceOf('\Charcoal\Admin\Template\Objet\Reorder', $obj);
	}
}
