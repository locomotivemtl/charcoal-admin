<?php

namespace Charcoal\Tests\Admin\Template\Account;

use ReflectionClass;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\Template\Account\ResetPasswordTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;

/**
 *
 */
class ResetPasswordTemplateTest extends AbstractTestCase
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
    public function setUp(): void
    {
        $this->obj = new ResetPasswordTemplate([
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
