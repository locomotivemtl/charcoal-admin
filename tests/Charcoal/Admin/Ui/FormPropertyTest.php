<?php

namespace Charcoal\Admin\Tests\Ui;

use \Charcoal\Admin\Ui\FormProperty as FormProperty;

class FormPropertyTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new FormProperty();
		$this->assertInstanceOf('\Charcoal\Admin\Ui\FormProperty', $obj);
	}
}
