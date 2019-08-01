<?php

namespace Charcoal\Tests\Admin\Widget;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\FormSidebarWidget;
use Charcoal\Admin\Widget\FormWidget;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class FormWidgetTest extends AbstractTestCase
{
    /**
     * Object under test
     * @var FormWidget
     */
    private $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $logger = new NullLogger();
        $this->obj = new FormWidget([
            'logger' => $logger,
        ]);
    }

    /**
     * @return FormSidebarWidget
     */
    private function sidebarWidget()
    {
        $logger = new NullLogger();
        return new FormSidebarWidget([
            'logger' => $logger,
        ]);
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(FormWidget::class, $this->obj);
    }
}
