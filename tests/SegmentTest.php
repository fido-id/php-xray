<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;

class SegmentTest extends TestCase
{
    use TestSubsegmentTrait;

    public function testSegmentWithoutErrorsSerialisesCorrectly(): void
    {
        $segment = new Segment(
            name: 'Test segment',
            parentId: '123'
        );
        $segment->end();

        $serialised = $segment->jsonSerialize();

        $this->assertEquals($segment->getId(), $serialised['id']);
        $this->assertEquals('Test segment', $serialised['name']);
        $this->assertNotNull($serialised['start_time']);
        $this->assertNotNull($serialised['end_time']);
        $this->assertArrayNotHasKey('fault', $serialised);
        $this->assertArrayNotHasKey('error', $serialised);
        $this->assertArrayNotHasKey('subsegments', $serialised);
    }

    public function testCannotSerializeOpenSegment(): void
    {
        $segment = $this->getNewSegment();
        $this->expectExceptionMessage('Segment must be closed before serialization.');
        $this->expectException(\RuntimeException::class);
        \json_encode($segment);
    }

    public function testSegmentWithErrorSerialisesCorrectly(): void
    {
        $segment = new Segment(
            name: 'Test segment',
            parentId: '123',
            error: true
        );
        $segment->end();

        $serialised = $segment->jsonSerialize();

        $this->assertEquals($segment->getId(), $serialised['id']);
        $this->assertEquals('Test segment', $serialised['name']);
        $this->assertTrue($serialised['error']);
        $this->assertNotNull($serialised['start_time']);
        $this->assertNotNull($serialised['end_time']);
        $this->assertArrayNotHasKey('fault', $serialised);
        $this->assertArrayNotHasKey('subsegments', $serialised);

        $segment = new Segment(
            name: 'Test segment',
            parentId: '123',
        );
        $segment->setError(true);
        $segment->end();

        $serialised = $segment->jsonSerialize();

        $this->assertEquals($segment->getId(), $serialised['id']);
        $this->assertEquals('Test segment', $serialised['name']);
        $this->assertTrue($serialised['error']);
        $this->assertNotNull($serialised['start_time']);
        $this->assertNotNull($serialised['end_time']);
        $this->assertArrayNotHasKey('fault', $serialised);
        $this->assertArrayNotHasKey('subsegments', $serialised);
    }

    public function testSegmentWithFaultSerialisesCorrectly(): void
    {
        $segment = new Segment(
            name: 'Test segment',
            parentId: '123',
            fault: true
        );
        $segment->end();

        $serialised = $segment->jsonSerialize();

        $this->assertEquals($segment->getId(), $serialised['id']);
        $this->assertEquals('Test segment', $serialised['name']);
        $this->assertTrue($serialised['fault']);
        $this->assertNotNull($serialised['start_time']);
        $this->assertNotNull($serialised['end_time']);
        $this->assertArrayNotHasKey('error', $serialised);
        $this->assertArrayNotHasKey('subsegments', $serialised);

        $segment = new Segment(
            name: 'Test segment',
            parentId: '123',
        );
        $segment->setFault(true);
        $segment->end();

        $serialised = $segment->jsonSerialize();

        $this->assertEquals($segment->getId(), $serialised['id']);
        $this->assertEquals('Test segment', $serialised['name']);
        $this->assertTrue($serialised['fault']);
        $this->assertNotNull($serialised['start_time']);
        $this->assertNotNull($serialised['end_time']);
        $this->assertArrayNotHasKey('error', $serialised);
        $this->assertArrayNotHasKey('subsegments', $serialised);
    }

    public function testSegmentWithSubsegmentSerialisesCorrectly(): void
    {
        $subsegment = new Segment(
            name: 'Test subsegment'
        );

        $segment = new Segment(
            name: 'Test segment',
            parentId: '123',
        );
        $segment->addSubsegment($subsegment);
        $segment->end();

        $serialised = \json_decode(\json_encode($segment), true);
        $this->assertIsNumeric($serialised['end_time']);
        unset($serialised['end_time']);

        $this->assertIsNumeric($serialised['subsegments'][0]['end_time']);
        unset($serialised['subsegments'][0]['end_time']);

        $this->assertSame([
            'id'          => $segment->getId(),
            'parent_id'   => '123',
            'name'        => 'Test segment',
            'start_time'  => $segment->getStartTime(),
            'subsegments' => [
                0 => [
                    'id'         => $subsegment->getId(),
                    'parent_id'  => $segment->getId(),
                    'name'       => 'Test subsegment',
                    'start_time' => $subsegment->getStartTime(),
                ],
            ],
        ], $serialised);
    }

    public function testIndependentSubsegmentSerialisesCorrectly(): void
    {
        $segment = new Segment(
            name: 'Test segment',
            parentId: '123',
            traceId: '456',
            independent: true
        );
        $segment->end();

        $serialised = $segment->jsonSerialize();

        $this->assertEquals('123', $serialised['parent_id']);
        $this->assertEquals('456', $serialised['trace_id']);
        $this->assertEquals('subsegment', $serialised['type']);
    }

    public function testGivenAnnotationsSerialisesCorrectly(): void
    {
        $segment = $this->getNewSegment();
        $segment->addAnnotation('key1', 'value1');
        $segment->addAnnotation('key2', 'value2');
        $segment->end();

        $serialised = $segment->jsonSerialize();

        $this->assertEquals(
            [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            $serialised['annotations']
        );
    }

    public function testGivenMetadataSerialisesCorrectly(): void
    {
        $segment = $this->getNewSegment();
        $segment->addMetadata('key1', 'value1');
        $segment->addMetadata('key2', ['value2', 'value3']);
        $segment->end();

        $serialised = $segment->jsonSerialize();

        $this->assertEquals(
            [
                'key1' => 'value1',
                'key2' => ['value2', 'value3'],
            ],
            $serialised['metadata']
        );
    }

    public function testIsNotOpenIfEndTimeSet(): void
    {
        $segment = new Segment('Test segment');
        $segment->end();

        $this->assertFalse($segment->isOpen());
    }

    public function testIsOpenIfEndTimeNotSet(): void
    {
        $segment = new Segment('Test segment');

        $this->assertTrue($segment->isOpen());
    }

    public function testGivenCauseSerializesCorrectly(): void
    {
        $pExceptionId = bin2hex(random_bytes(8));
        $line         = __LINE__;
        $cException   = new CauseException(
            "Test",
            "test",
            false,
            0,
            0,
            $pExceptionId,
            [new CauseStackFrame(__CLASS__, $line, __METHOD__)]
        );

        $segment = new Segment(
            name: 'Test Segment'
        );
        $segment->setCause(new Cause(__DIR__, [__CLASS__], [$cException]));
        $segment->end();

        $serialized = \json_decode(\json_encode($segment), true);
        // remove time based data
        $this->assertIsNumeric($serialized['end_time']);
        unset($serialized['end_time']);

        $this->assertSame(
            [
                'id'         => $segment->getId(),
                'name'       => 'Test Segment',
                'start_time' => $segment->getStartTime(),
                'cause'      => [
                    'working_directory' => __DIR__,
                    'paths'             => [
                        0 => 'Fido\PHPXray\SegmentTest',
                    ],
                    'exceptions'        => [
                        0 => [
                            'id'        => $cException->getId(),
                            'message'   => 'Test',
                            'type'      => 'test',
                            'remote'    => false,
                            'truncated' => 0,
                            'skipped'   => 0,
                            'cause'     => $pExceptionId,
                            'stack'     => [
                                [
                                    'path'  => 'Fido\PHPXray\SegmentTest',
                                    'line'  => $line,
                                    'label' => 'Fido\PHPXray\SegmentTest::testGivenCauseSerializesCorrectly',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $serialized
        );
    }

    public function testSegmentWithTraceHeader(): void
    {
        $traceId  = '1-ab3169f3-1b7f38ac63d9037ef1843ca4';
        $parentId = '1234567890';
        $segment  = $this->getNewSegment();
        $segment->setTraceHeader("Root=$traceId;Parent=$parentId");
        $segment->end();

        $serialized = \json_decode(\json_encode($segment), true);
        // remove time based data
        $this->assertIsNumeric($serialized['end_time']);
        unset($serialized['end_time']);

        $this->assertSame([
            'id'         => $segment->getId(),
            'parent_id'  => '1234567890',
            'trace_id'   => '1-ab3169f3-1b7f38ac63d9037ef1843ca4',
            'name'       => 'HTTP Segment',
            'start_time' => $segment->getStartTime(),
        ], $serialized);

        $segment  = $this->getNewSegment();
        $segment->setTraceHeader("Root=$traceId");
        $segment->end();

        $serialized = \json_decode(\json_encode($segment), true);
        // remove time based data
        $this->assertIsNumeric($serialized['end_time']);
        unset($serialized['end_time']);

        $this->assertSame([
            'id'         => $segment->getId(),
            'trace_id'   => '1-ab3169f3-1b7f38ac63d9037ef1843ca4',
            'name'       => 'HTTP Segment',
            'start_time' => $segment->getStartTime(),
        ], $serialized);

        $segment  = $this->getNewSegment();
        $segment->setTraceHeader("Parent=$parentId");
        $segment->end();

        $serialized = \json_decode(\json_encode($segment), true);
        // remove time based data
        $this->assertIsNumeric($serialized['end_time']);
        unset($serialized['end_time']);

        $this->assertSame([
            'id'         => $segment->getId(),
            'parent_id'  => '1234567890',
            'name'       => 'HTTP Segment',
            'start_time' => $segment->getStartTime(),
        ], $serialized);
    }

    private function getNewSegment(string $name = 'HTTP Segment'): Segment
    {
        return new Segment(
            name: $name,
        );
    }
}
