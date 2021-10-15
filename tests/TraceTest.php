<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;

class TraceTest extends TestCase
{
    public function testGetInstanceReturnsSingleton()
    {
        $instance1 = Trace::getInstance();
        $instance2 = Trace::getInstance();

        $this->assertEquals(spl_object_hash($instance1), spl_object_hash($instance2));
    }

    public function testSerialisesCorrectly()
    {
        $trace = new Trace();
        $trace->setName('Test trace')
            ->setServiceVersion('1.2.3')
            ->setUser('TestUser')
            ->setUrl('http://example.com')
            ->setMethod('GET')
            ->setClientIpAddress('127.0.0.1')
            ->setUserAgent('TestAgent')
            ->setResponseCode(200)
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

    public function testGeneratesCorrectFormatTraceId()
    {
        $trace = new Trace();
        $trace->begin();

        $this->assertMatchesRegularExpression('@^1\-[a-f0-9]{8}\-[a-f0-9]{24}$@', $trace->getTraceId());
    }

    public function testGivenNullHeaderDoesNotSetId()
    {
        $this->expectException(\TypeError::class);

        $trace = new Trace();
        $trace->setTraceHeader(null);

        $trace->getTraceId();
    }

    public function testGivenIdHeaderSetsId()
    {
        $traceId = '1-ab3169f3-1b7f38ac63d9037ef1843ca4';

        $trace = new Trace();
        $trace->setTraceHeader("Root=$traceId");

        $this->assertEquals($traceId, $trace->getTraceId());
        $this->assertFalse($trace->isSampled());
        $this->assertArrayNotHasKey('parent_id', $trace->jsonSerialize());
    }

    public function testGivenSampledHeaderSetsSampled()
    {
        $traceId = '1-ab3169f3-1b7f38ac63d9037ef1843ca4';

        $trace = new Trace();
        $trace->setTraceHeader("Root=$traceId;Sampled=1");

        $this->assertEquals($traceId, $trace->getTraceId());
        $this->assertTrue($trace->isSampled());
        $this->assertArrayNotHasKey('parent_id', $trace->jsonSerialize());
    }

    public function testGivenParentHeaderSetsParentId()
    {
        $traceId = '1-ab3169f3-1b7f38ac63d9037ef1843ca4';
        $parentId = '1234567890';

        $trace = new Trace();
        $trace->setTraceHeader("Root=$traceId;Sampled=1;Parent=$parentId");

        $this->assertEquals($traceId, $trace->getTraceId());
        $this->assertTrue($trace->isSampled());
        $this->assertEquals($parentId, $trace->jsonSerialize()['parent_id']);
    }
}
