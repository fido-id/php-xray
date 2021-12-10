<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;

class CauseTest extends TestCase
{
    /**
     * @test
     */
    public function smokeTest(): void
    {
        $cause = new Cause(
            workingDirectory: 'test',
            paths: [],
            exceptions: []
        );

        $this->assertSame('test', $cause->getWorkingDirectory());
        $this->assertSame([], $cause->getPaths());
        $this->assertSame([], $cause->getExceptions());
    }

    public function testJsonSerialize(): void
    {
        $cause = new Cause(__DIR__, [__CLASS__], []);
        $result = \json_decode(\json_encode($cause), true);

        $this->assertSame(__DIR__, $result['working_directory']);
        $this->assertSame([__CLASS__], $result['paths']);
        $this->assertSame([], $result['exceptions']);
        $this->assertSame([
            'working_directory',
            'paths',
            'exceptions',
        ], array_keys($result));
    }

    public function testFromThrowable(): void
    {
        try {
            throw new \Exception('test');
        } catch (\Throwable $t) {
            $exception = $t;
        }
        $cause = Cause::fromThrowable($exception);
        $this->assertStringMatchesFormat('%s/CauseTest.php::%d', $cause->getWorkingDirectory());
        $this->assertCount(1, $cause->getPaths());
        $this->assertStringMatchesFormat('%s/CauseTest.php::%d', $cause->getPaths()[0]);
        $this->assertCount(1, $cause->getExceptions());
        $e = $cause->getExceptions()[0];
        $this->assertInstanceOf(CauseException::class, $e);
        $this->assertSame('test', $e->getMessage());
        $this->assertNotEmpty($e->getStack());
    }
}
