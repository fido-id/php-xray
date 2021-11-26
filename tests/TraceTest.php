<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;

class TraceTest extends TestCase
{
    public function testGetInstanceReturnsSingleton(): void
    {
        $instance1 = Trace::getInstance();
        $instance2 = Trace::getInstance();

        $this->assertEquals(spl_object_hash($instance1), spl_object_hash($instance2));
    }

    public function testSerialisesCorrectly(): void
    {
        $trace = (new Trace())
            ->setServiceVersion('1.2.3')
            ->setUser('TestUser')
            ->setUrl('http://example.com')
            ->setMethod('GET')
            ->setClientIpAddress('127.0.0.1')
            ->setUserAgent('TestAgent')
            ->setResponseCode(200)
            ->setName('Test trace')
            ->begin()
            ->end();

        $serialised = $trace->jsonSerialize();

        $this->assertEquals('Test trace', $serialised['name']);
        $this->assertEquals('1.2.3', $serialised['service']['version']);
        $this->assertEquals('TestUser', $serialised['user']);
        $this->assertEquals('http://example.com', $serialised['http']['request']['url']);
        $this->assertEquals('GET', $serialised['http']['request']['method']);
        $this->assertEquals('127.0.0.1', $serialised['http']['request']['client_ip']);
        $this->assertEquals('TestAgent', $serialised['http']['request']['user_agent']);
        $this->assertEquals(200, $serialised['http']['response']['status']);
        $this->assertEquals($trace->getTraceId(), $serialised['trace_id']);
    }

    public function testGeneratesCorrectFormatTraceId(): void
    {
        $trace = new Trace();
        $trace->begin();

        $this->assertMatchesRegularExpression('@^1\-[a-f0-9]{8}\-[a-f0-9]{24}$@', $trace->getTraceId());
    }

    /**
     * The logic of this test is that if you don't set a proper header the TraceId won't be set as well
     * but the returned error is too generic imho
     */
    public function testGivenNullHeaderDoesNotSetId(): void
    {
        $this->expectException(\Error::class);

        $trace = new Trace();
        $trace->setTraceHeader();

        $trace->getTraceId();
    }

    public function testGivenIdHeaderSetsId(): void
    {
        $traceId = '1-ab3169f3-1b7f38ac63d9037ef1843ca4';

        $trace = new Trace();
        $trace->setTraceHeader("Root=$traceId");

        $this->assertEquals($traceId, $trace->getTraceId());
        $this->assertArrayNotHasKey('parent_id', $trace->jsonSerialize());
    }

    public function testGivenParentHeaderSetsParentId(): void
    {
        $traceId  = '1-ab3169f3-1b7f38ac63d9037ef1843ca4';
        $parentId = '1234567890';

        $trace = new Trace();
        $trace->setTraceHeader("Root=$traceId;Parent=$parentId");

        $this->assertEquals($traceId, $trace->getTraceId());
        $this->assertEquals($parentId, $trace->jsonSerialize()['parent_id']);
    }
}
