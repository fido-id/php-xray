<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;

class RemoteSegmentTest extends TestCase
{
    public function testUntracedSegmentSerialisesCorrectly(): void
    {
        $segment = new RemoteSegment();

        $serialised = $segment->jsonSerialize();

        $this->assertEquals($segment->getId(), $serialised['id']);
        $this->assertEquals('remote', $serialised['namespace']);
        $this->assertArrayNotHasKey('traced', $serialised);
    }

    public function testTracedSegmentSerialisesCorrectly(): void
    {
        $segment = new RemoteSegment();
        $segment->setTraced(true);

        $serialised = $segment->jsonSerialize();

        $this->assertEquals($segment->getId(), $serialised['id']);
        $this->assertEquals('remote', $serialised['namespace']);
        $this->assertTrue($serialised['traced']);
    }
}
