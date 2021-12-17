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
        $causeException = new CauseException(
            message: 'message',
            type: 'type',
            remote: true,
            truncated: 0,
            skipped: 0,
            cause: $randomBytes,
            stack: []
        );
        $result         = $causeException->jsonSerialize();

        $this->assertSame(16, strlen($result['id']));
        $this->assertTrue(ctype_xdigit($result['id']));
        $this->assertSame([
            'id'        => $causeException->getId(),
            'message'   => 'message',
            'type'      => 'type',
            'remote'    => true,
            'truncated' => 0,
            'skipped'   => 0,
            'cause'     => $randomBytes,
            'stack'     => [],
        ], $result);

        $causeException = new CauseException(
            message: 'message',
            type: 'type',
            remote: true,
            truncated: 0,
            skipped: 0
        );
        $result         = $causeException->jsonSerialize();

        $this->assertSame(16, strlen($result['id']));
        $this->assertTrue(ctype_xdigit($result['id']));
        $this->assertSame([
            'id'        => $causeException->getId(),
            'message'   => 'message',
            'type'      => 'type',
            'remote'    => true,
            'truncated' => 0,
            'skipped'   => 0,
            'cause' => null,
            'stack' => null,
        ], $result);
    }
}
