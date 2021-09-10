<?php

namespace Fido\PHPXray\Submission;

use Fido\PHPXray\Segment;

interface SegmentSubmitter
{
    public function submitSegment(Segment $segment): void;
}
