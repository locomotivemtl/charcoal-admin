<?php

namespace Charcoal\Tests\Admin\Template;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\Template\LogoutTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;

/**
 *
 */
class LogoutTemplateTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * @var LogoutTemplate
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->obj = new LogoutTemplate([
            'logger' => new NullLogger()
        ]);
    }

    /**
     * @return void
     */
    public function testAuthRequiredIsFalse()
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertNotTrue($res);
    }
}
