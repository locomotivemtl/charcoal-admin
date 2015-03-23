<?php

namespace Charcoal\Admin\Tests\Ui;

use \Charcoal\Admin\Ui\FormGroup as FormGroup;

class FormGroupTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new FormGroup();
		$this->assertInstanceOf('\Charcoal\Admin\Ui\FormGroup', $obj);
	}
}
