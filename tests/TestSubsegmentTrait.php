<?php

namespace Fido\PHPXray;

use Webmozart\Assert\InvalidArgumentException;

trait TestSubsegmentTrait
{
    public function testAddingSubsegmentToClosedSegmentFails(): void
    {
        $subsegment = new Segment(
            name: 'Test subsegment'
        );

        $segment = $this->getNewSegment(
            name: 'Test segment',
        );
        $segment->end();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cant add a subsegment to a closed segment!');
        $segment->addSubsegment($subsegment);
    }

    public function testGivenNoSubsegmentsCurrentSegmentReturnsSegment(): void
    {
        $segment = $this->getNewSegment();

        $this->assertEquals($segment, $segment->getCurrentSegment());
    }

    public function testClosedSubsegmentCurrentSegmentReturnsSegment(): void
    {
        $subsegment = new Segment(
            name: 'Test subsegment'
        );

        $segment = $this->getNewSegment(
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

        $segment = $this->getNewSegment(
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

        $segment = $this->getNewSegment(
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
}
