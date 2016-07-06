<?php

namespace Charcoal\Admin\Tests\Widget;

use \Psr\Log\NullLogger;

use \Charcoal\Admin\Widget\FormSidebarWidget;
use \Charcoal\Admin\Widget\FormWidget;

class FormWidgetTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $logger = new NullLogger();
        $this->obj = new FormWidget([
            'logger' => $logger
        ]);
    }

    private function sidebarWidget()
    {
        $logger = new NullLogger();
        return new FormSidebarWidget([
            'logger' => $logger
        ]);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(FormWidget::class, $this->obj);
    }

    public function testSidebars()
    {
        $sidebars = iterator_to_array($this->obj->sidebars());
        $this->assertEquals([], $sidebars);

        $sidebarWidget = $this->sidebarWidget();
        $this->obj->addSidebar('foo', $sidebarWidget);

        $sidebars = iterator_to_array($this->obj->sidebars());
        $this->assertEquals(['foo'=>$sidebarWidget], $sidebars);
    }
}
