<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Fido\PHPXray\Submission\SegmentSubmitter;
use Webmozart\Assert\InvalidArgumentException;

class SegmentTest extends TestCase
{
    public function testSegmentWithoutErrorsSerialisesCorrectly(): void
    {
        $segment = new Segment();

        $segment->setName('Test segment')
            ->setParentId('123')
            ->begin()
            ->end();

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
        $segment = new Segment();

        $segment->setName('Test segment')
            ->setParentId('123')
            ->begin()
            ->end()
            ->setError(true);

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
        $segment = new Segment();

        $segment->setName('Test segment')
            ->setParentId('123')
            ->begin()
            ->end()
            ->setFault(true);

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
        $segment = new Segment();
        $subsegment = new Segment();

        $subsegment->setName('Test subsegment')
            ->begin()
            ->end();

        $segment->setName('Test segment')
            ->setParentId('123')
            ->begin()
            ->addSubsegment($subsegment)
            ->end();

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
        $segment = new Segment();

        $segment->setName('Test segment')
            ->setParentId('123')
            ->setTraceId('456')
            ->setIndependent(true)
            ->begin()
            ->end();

        $serialised = $segment->jsonSerialize();

        $this->assertEquals('123', $serialised['parent_id']);
        $this->assertEquals('456', $serialised['trace_id']);
        $this->assertEquals('subsegment', $serialised['type']);
    }

    public function testGivenAnnotationsSerialisesCorrectly(): void
    {
        $segment = new Segment();
        $segment->addAnnotation('key1', 'value1')
            ->addAnnotation('key2', 'value2');

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
        $segment = new Segment();
        $segment->addMetadata('key1', 'value1')
            ->addMetadata('key2', ['value2', 'value3']);

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

        $segment = new Segment();
        $subsegment = new Segment();

        $subsegment->setName('Test subsegment')
            ->begin()
            ->end();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cant add a subsegment to a closed segment!');

        $segment->setName('Test segment')
            ->setParentId('123')
            ->begin()
            ->end()
            ->addSubsegment($subsegment);
    }

    public function testIsNotOpenIfEndTimeSet(): void
    {
        $segment = new Segment();
        $segment->begin()
            ->end();

        $this->assertFalse($segment->isOpen());
    }

    public function testIsOpenIfEndTimeNotSet(): void
    {
        $segment = new Segment();
        $segment->begin();

        $this->assertTrue($segment->isOpen());
    }

    public function testGivenNoSubsegmentsCurrentSegmentReturnsSegment(): void
    {
        $segment = new Segment();
        $segment->begin();

        $this->assertEquals($segment, $segment->getCurrentSegment());
    }

    public function testClosedSubsegmentCurrentSegmentReturnsSegment(): void
    {
        $subsegment = new Segment();
        $subsegment->begin()
            ->end();
        $segment = new Segment();
        $segment->begin()
            ->addSubsegment($subsegment);

        $this->assertEquals($segment, $segment->getCurrentSegment());
    }

    public function testOpenSubsegmentCurrentSegmentReturnsSubsegment(): void
    {
        $subsegment = new Segment();
        $subsegment->begin();
        $segment = new Segment();
        $segment->begin()
            ->addSubsegment($subsegment);

        $this->assertEquals($subsegment, $segment->getCurrentSegment());
    }

    public function testSubsequentCallsCurrentSegmentReturnsSubsegment(): void
    {
        $subsegment = new Segment();
        $subsegment->begin();
        $segment = new Segment();
        $segment->begin()
            ->addSubsegment($subsegment);

        $this->assertEquals($subsegment, $segment->getCurrentSegment());
        $this->assertEquals($subsegment, $segment->getCurrentSegment());
    }

    public function testChangingCurrentSegmentReturnsCorrectStatus(): void
    {
        $subsegment1 = new Segment();
        $subsegment1->begin();
        $subsegment2 = new Segment();
        $subsegment2->begin();
        $subsegment3 = new Segment();
        $subsegment3->begin();

        $segment = new Segment();
        $segment->begin()
            ->addSubsegment($subsegment1)
            ->addSubsegment($subsegment2)
            ->addSubsegment($subsegment3);

        $this->assertEquals($subsegment1, $segment->getCurrentSegment());

        $subsegment1->end();

        $this->assertEquals($subsegment2, $segment->getCurrentSegment());

        $subsegment2->end();

        $this->assertEquals($subsegment3, $segment->getCurrentSegment());
    }

    public function testGivenCauseSerialisesCorrectly(): void
    {
        $pExceptionId = bin2hex(random_bytes(8));
        $line = __LINE__;
        $cException = new CauseException(
            "Test",
            "test",
            false,
            0,
            0,
            $pExceptionId,
            [new CauseStackFrame(__CLASS__, $line, __METHOD__)]
        );

        $segment = new Segment();
        $segment->setCause(new Cause(__DIR__, [__CLASS__], [$cException]));

        $serialised = \json_decode(\json_encode($segment), true);

        $this->assertEquals(
            [
                'id' => $segment->getId(),
                'cause' => [
                    'working_directory' => __DIR__,
                    'paths' => [
                        0 => 'Fido\PHPXray\SegmentTest',
                    ],
                    'exceptions' => [
                        0 => [
                            'id' => $cException->getId(),
                            'message' => 'Test',
                            'type' => 'test',
                            'remote' => false,
                            'truncated' => 0,
                            'skipped' => 0,
                            'cause' => $pExceptionId,
                            'stack' => [
                                [
                                    'path' => 'Fido\PHPXray\SegmentTest',
                                    'line' => $line,
                                    'label' => 'Fido\PHPXray\SegmentTest::testGivenCauseSerialisesCorrectly',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $serialised
        );
    }
}
