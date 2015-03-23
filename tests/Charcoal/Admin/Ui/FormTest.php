<?php

namespace Charcoal\Admin\Tests\Ui;

use \Charcoal\Admin\Ui\Form as Form;

class FormTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Form();
		$this->assertInstanceOf('\Charcoal\Admin\Ui\Form', $obj);
	}
}
