<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;

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
}
