<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;

class CauseTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $cause  = new Cause(__DIR__, [__CLASS__], []);
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
}
