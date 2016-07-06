<?php

namespace Charcoal\Admin\Tests\Widget;

use \Psr\Log\NullLogger;

use \Charcoal\Admin\Widget\SearchWidget;

class SearchWidgetTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $logger = new NullLogger();
        $this->obj = new SearchWidget([
            'logger' => $logger
        ]);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(SearchWidget::class, $this->obj);
    }
}
