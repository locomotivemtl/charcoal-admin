<?php

namespace Charcoal\Tests\Admin\Template;

use ReflectionClass;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\Template\LoginTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;

/**
 *
 */
class LoginTemplateTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * Instance of object under test
     * @var LoginTemplate
     */
    private $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->obj = new LoginTemplate([
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
