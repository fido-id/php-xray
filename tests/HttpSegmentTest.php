<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class HttpSegmentTest extends TestCase
{
    public function testSerialisesCorrectly(): void
    {
        $segment = new HttpSegment(
            name: 'HTTP Segment',
            url: 'http://example.com/',
            method: 'GET',
            responseCode: 200
        );
        $segment->end();

        $serialised = $segment->jsonSerialize();

        foreach ($serialised['http'] as $item){
            self::assertNotNull($item);
        }

        $this->assertEquals('remote', $serialised['namespace']);
        $this->assertEquals('http://example.com/', $serialised['http']['request']['url']);
        $this->assertEquals('GET', $serialised['http']['request']['method']);
        $this->assertEquals(200, $serialised['http']['response']['status']);
    }

    public function testCloseWithPsrResponse400(): void
    {
        $segment = new HttpSegment(
            name: 'HTTP Segment',
            url: 'http://example.com/',
            method: 'GET',
        );
        $segment->closeWithPsrResponse(new Response(400));
        $serialised = $segment->jsonSerialize();

        $this->assertEquals(400, $serialised['http']['response']['status']);
        $this->assertTrue($serialised['error']);
        $this->assertSame("", $serialised['metadata']['content']);
        $this->assertSame("Bad Request", $serialised['metadata']['reason']);
        $this->assertCount(0, $serialised['metadata']['headers']);
    }

    public function testCloseWithPsrResponse500(): void
    {
        $segment = new HttpSegment(
            name: 'HTTP Segment',
            url: 'http://example.com/',
            method: 'GET',
        );
        $segment->closeWithPsrResponse(new Response(500));
        $serialised = $segment->jsonSerialize();

        $this->assertEquals(500, $serialised['http']['response']['status']);
        $this->assertTrue($serialised['fault']);
        $this->assertSame("", $serialised['metadata']['content']);
        $this->assertSame("Internal Server Error", $serialised['metadata']['reason']);
        $this->assertCount(0, $serialised['metadata']['headers']);
    }
}
