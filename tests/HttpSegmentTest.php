<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class HttpSegmentTest extends TestCase
{
    public function testSerialisesCorrectly(): void
    {
        $segment = new HttpSegment();
        $segment
            ->setUrl('http://example.com/')
            ->setMethod('GET')
            ->setResponseCode(200)
        ;

        $serialised = $segment->jsonSerialize();

        foreach ($serialised['http'] as $item){
            self::assertNotNull($item);
        }

        $this->assertEquals('remote', $serialised['namespace']);
        $this->assertEquals('http://example.com/', $serialised['http']['request']['url']);
        $this->assertEquals('GET', $serialised['http']['request']['method']);
        $this->assertEquals(200, $serialised['http']['response']['status']);
    }

    public function testCloseWithPsrResponse(): void
    {
        $segment = new HttpSegment();
        $segment
            ->setUrl('http://example.com/')
            ->setMethod('GET')
            ->closeWithPsrResponse(new Response(400));
        $serialised = $segment->jsonSerialize();

        $this->assertEquals(400, $serialised['http']['response']['status']);
        $this->assertTrue($serialised['error']);
        $this->assertSame("", $serialised['metadata']['content']);
        $this->assertSame("Bad Request", $serialised['metadata']['reason']);
        $this->assertCount(0, $serialised['metadata']['headers']);
    }
}
