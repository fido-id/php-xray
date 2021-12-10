<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class TraceSingletonAccessorTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        TraceSingletonAccessor::reset();
    }

    public function testGetInstanceReturnsSingleton(): void
    {
        $trace = new Trace('singleton');

        $this->assertFalse(TraceSingletonAccessor::hasInstance());
        TraceSingletonAccessor::setInstance($trace);

        $this->assertTrue(TraceSingletonAccessor::hasInstance());

        $instance1 = TraceSingletonAccessor::getInstance();
        $instance2 = TraceSingletonAccessor::getInstance();

        $this->assertSame(spl_object_hash($instance1), spl_object_hash($instance2));
    }

    public function testThrowIfInstanceNotSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Trace instance not found.');
        TraceSingletonAccessor::getInstance();
    }
}
