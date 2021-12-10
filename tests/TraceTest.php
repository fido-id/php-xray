<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;

class TraceTest extends TestCase
{
    public function testSerialisesCorrectly(): void
    {
        $trace = new  Trace(
            name: 'Test trace',
            url: 'http://example.com',
            method: 'GET',
            responseCode: 200,
            clientIpAddress: '127.0.0.1',
            userAgent: 'TestAgent',
            serviceVersion: '1.2.3',
            user: 'TestUser',
        );
        $trace->end();

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
        $trace = new Trace(name: 'Test trace',);
        $trace->end();

        $this->assertMatchesRegularExpression('@^1\-[a-f0-9]{8}\-[a-f0-9]{24}$@', $trace->getTraceId());
    }

    public function testGivenIdHeaderSetsId(): void
    {
        $traceId = '1-ab3169f3-1b7f38ac63d9037ef1843ca4';

        $trace = new Trace(name: 'Test trace',);
        $trace->setTraceHeader("Root=$traceId");
        $trace->end();

        $this->assertEquals($traceId, $trace->getTraceId());
        $this->assertArrayNotHasKey('parent_id', $trace->jsonSerialize());
    }

    public function testGivenParentHeaderSetsParentId(): void
    {
        $traceId  = '1-ab3169f3-1b7f38ac63d9037ef1843ca4';
        $parentId = '1234567890';

        $trace = new Trace(name: 'Test trace',);
        $trace->setTraceHeader("Root=$traceId;Parent=$parentId");
        $trace->end();

        $this->assertEquals($traceId, $trace->getTraceId());
        $this->assertEquals($parentId, $trace->jsonSerialize()['parent_id']);
    }
}
