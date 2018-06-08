<?php

namespace Charcoal\Tests;

/**
 * Utilities for advanced assertions.
 */
trait AssertionsTrait
{
    /**
     * Asserts that the given haystack is as expected.
     *
     * @param  array  $expected The expected haystack.
     * @param  array  $haystack The actual haystack.
     * @param  string $message  The error to report.
     * @return void
     */
    public function assertArrayEquals(array $expected, array $haystack, $message = '')
    {
        $this->assertCount(count($expected), $haystack, $message);
        $this->assertEquals($expected, $haystack, $message);
    }

    /**
     * Asserts that the given haystack contains the expected values.
     *
     * @param  array  $expected The expected haystack.
     * @param  array  $haystack The actual haystack.
     * @param  string $message  The error to report.
     * @return void
     */
    public function assertArrayContains(array $expected, array $haystack, $message = '')
    {
        foreach ($expected as $item) {
            $this->assertContains($item, $haystack, $message);
        }
    }

    /**
     * Asserts that the given haystack contains the expected keys.
     *
     * @param  array  $expected The expected haystack.
     * @param  array  $haystack The actual haystack.
     * @param  string $message  The error to report.
     * @return void
     */
    public function assertArrayHasKeys(array $expected, array $haystack, $message = '')
    {
        foreach ($expected as $item) {
            $this->assertArrayHasKey($item, $haystack, $message);
        }
    }

    /**
     * Asserts that the given haystack contains the expected subsets.
     *
     * @param  array   $expected The expected haystack.
     * @param  array   $haystack The actual haystack.
     * @param  boolean $strict   Whether to check for object identity.
     * @param  string  $message  The error to report.
     * @return void
     */
    public function assertArraySubsets(
        array $expected,
        array $haystack,
        $strict = false,
        $message = ''
    ) {
        foreach ($expected as $key => $val) {
            $this->assertArraySubset([ $key => $val ], $haystack, $strict, $message);
        }
    }
}
