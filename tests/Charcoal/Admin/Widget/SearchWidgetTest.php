<?php

namespace Charcoal\Tests\Admin\Widget;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\SearchWidget;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class SearchWidgetTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        $logger = new NullLogger();
        $this->obj = new SearchWidget([
            'logger' => $logger
        ]);
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(SearchWidget::class, $this->obj);
    }
}
