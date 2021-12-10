<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class SegmentTest extends TestCase
{
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

        $serialised = $segment->jsonSerialize();

        $this->assertEquals($segment->getId(), $serialised['id']);
        $this->assertEquals('Test segment', $serialised['name']);
        $this->assertNotNull($serialised['start_time']);
        $this->assertNotNull($serialised['end_time']);
        $this->assertArrayHasKey('subsegments', $serialised);

        $this->assertEquals($subsegment, $serialised['subsegments'][0]);
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
        $segment = new Segment(
            name: 'Test segment',
        );
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
        $segment = new Segment(
            name: 'Test segment',
        );
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

    public function testAddingSubsegmentToClosedSegmentFails(): void
    {
        $subsegment = new Segment(
            name: 'Test subsegment'
        );

        $segment = new Segment(
            name: 'Test segment',
            parentId: '123',
        );
        $segment->end();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cant add a subsegment to a closed segment!');
        $segment->addSubsegment($subsegment);
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

    public function testGivenNoSubsegmentsCurrentSegmentReturnsSegment(): void
    {
        $segment = new Segment('Test segment');

        $this->assertEquals($segment, $segment->getCurrentSegment());
    }

    public function testClosedSubsegmentCurrentSegmentReturnsSegment(): void
    {
        $subsegment = new Segment(
            name: 'Test subsegment'
        );

        $segment = new Segment(
            name: 'Test segment',
        );
        $subsegment->end();
        $segment->addSubsegment($subsegment);

        $this->assertEquals($segment, $segment->getCurrentSegment());
    }

    public function testOpenSubsegmentCurrentSegmentReturnsSubsegment(): void
    {
        $subsegment = new Segment(
            name: 'Test subsegment'
        );

        $segment = new Segment(
            name: 'Test segment',
        );
        $segment->addSubsegment($subsegment);

        $this->assertEquals($subsegment, $segment->getCurrentSegment());
        $this->assertEquals($subsegment, $segment->getCurrentSegment());
    }

    public function testChangingCurrentSegmentReturnsCorrectStatus(): void
    {
        $subsegment1 = new Segment('Test a');
        $subsegment2 = new Segment('Test b');
        $subsegment3 = new Segment('Test c');

        $segment = new Segment(
            name: 'Test segment',
        );
        $segment->addSubsegment($subsegment1);
        $segment->addSubsegment($subsegment2);
        $segment->addSubsegment($subsegment3);

        $this->assertEquals($subsegment1, $segment->getCurrentSegment());

        $subsegment1->end();

        $this->assertEquals($subsegment2, $segment->getCurrentSegment());

        $subsegment2->end();

        $this->assertEquals($subsegment3, $segment->getCurrentSegment());

        $subsegment3->end();

        $this->assertEquals($segment, $segment->getCurrentSegment());
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
        $this->assertArrayHasKey('start_time', $serialized);
        unset($serialized['start_time']);
        $this->assertArrayHasKey('end_time', $serialized);
        unset($serialized['end_time']);

        $this->assertEquals(
            [
                'name' => 'Test Segment',
                'id'    => $segment->getId(),
                'cause' => [
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
}
