<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;

class CauseStackFrameTest extends TestCase
{
    /**
     * @test
     */
    public function smokeTest(): void
    {
        $causeStackFrame = new CauseStackFrame(
            path: 'test',
            line: 0,
            label: 'test'
        );

        $this->assertSame('test', $causeStackFrame->getPath());
        $this->assertSame(0, $causeStackFrame->getLine());
        $this->assertSame('test', $causeStackFrame->getLabel());
    }

    public function testJsonSerialize(): void
    {
        $causeStackFrame = new CauseStackFrame(__CLASS__, 0, __METHOD__);
        $result          = \json_decode(\json_encode($causeStackFrame), true);

        $this->assertSame(__CLASS__, $result['path']);
        $this->assertSame(0, $result['line']);
        $this->assertSame(__METHOD__, $result['label']);
        $this->assertSame([
            'path',
            'line',
            'label',
        ], array_keys($result));
    }
}
