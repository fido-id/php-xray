<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;

class CauseExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function smokeTest(): void
    {
        $causeException = new CauseException(
            message: 'test',
            type: 'test',
            remote: true,
            truncated: 0,
            skipped: 0,
            cause: 'test'
        );

        $this->assertSame('test', $causeException->getMessage());
        $this->assertSame('test', $causeException->getType());
        $this->assertTrue($causeException->isRemote());
        $this->assertSame(0, $causeException->getTruncated());
        $this->assertSame(0, $causeException->getSkipped());
        $this->assertSame('test', $causeException->getCause());
    }

    public function testJsonSerialize(): void
    {
        $randomBytes    = \bin2hex(\random_bytes(8));
        $causeException = new CauseException('message', 'type', true, 0, 0, $randomBytes, []);
        $result         = \json_decode(\json_encode($causeException), true);

        $this->assertSame(16, strlen($result['id']));
        $this->assertTrue(ctype_xdigit($result['id']));
        $this->assertSame('message', $result['message']);
        $this->assertSame('type', $result['type']);
        $this->assertTrue($result['remote']);
        $this->assertSame(0, $result['truncated']);
        $this->assertSame(0, $result['skipped']);
        $this->assertSame($randomBytes, $result['cause']);
        $this->assertSame([], $result['stack']);
        $this->assertSame([
            'id',
            'message',
            'type',
            'remote',
            'truncated',
            'skipped',
            'cause',
            'stack',
        ], array_keys($result));
    }
}
