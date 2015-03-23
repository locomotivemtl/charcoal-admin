<?php

namespace Charcoal\Admin\Tests\Template\Object;

use \Charcoal\Admin\Template\Object\Collection as Colllection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Collection();
		$this->assertInstanceOf('\Charcoal\Admin\Template\Objet\Collection', $obj);
	}
}
