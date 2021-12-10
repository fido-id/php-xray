<?php

namespace Fido\PHPXray;

use Fido\PHPXray\Submission\DaemonSegmentSubmitter;
use PHPUnit\Framework\TestCase;
use Socket;

class DaemonSegmentSubmitterTest extends TestCase
{
    /** @var Socket */
    private $socket;

    protected function setUp(): void
    {
        parent::setUp();
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_bind($this->socket, '127.0.0.1', 2000);
    }

    public function tearDown(): void
    {
        socket_close($this->socket);
        parent::tearDown();
    }

    public function testGetConfigFromGlobal(): void
    {
        $_SERVER[DictionaryInterface::DAEMON_ADDRESS_AND_PORT] = '127.0.0.1:2000';

        $segment = new Segment(
            name: 'Test segment'
        );
        $segment->end();
        (new DaemonSegmentSubmitter())->submitSegment($segment);

        $packets = $this->receivePackets(1);
        $this->assertPacketsReceived([$segment], $packets);
    }

    public function testSubmitsToDaemon(): void
    {
        $segment = new Segment(
            name: 'Test segment'
        );
        $segment->end();
        (new DaemonSegmentSubmitter())->submitSegment($segment);

        $packets = $this->receivePackets(1);
        $this->assertPacketsReceived([$segment], $packets);
    }

    public function testSubmitsLongTraceAsFragmented(): void
    {
        $subsegment1 = new SqlSegment('Query a', str_repeat('a', 30000));
        $subsegment2 = new SqlSegment('Query b', str_repeat('b', 30000));
        $subsegment3 = new SqlSegment('Query c', str_repeat('c', 30000));

        $segment = new Trace(
            name: 'Test segment',
        );
        $segment->addSubsegment($subsegment1);
        $segment->addSubsegment($subsegment2);
        $segment->addSubsegment($subsegment3);
        $segment->end();
        (new DaemonSegmentSubmitter())->submitSegment($segment);

        $buffer = $this->receivePackets(5);

        $rawSegment = $segment->jsonSerialize();
        unset($rawSegment['subsegments']);
        $openingSegment = $rawSegment;
        unset($openingSegment['end_time']);
        $openingSegment['in_progress'] = true;

        $subsegment1->setIndependent(true);
        $subsegment1->setTraceId($segment->getTraceId());
        $subsegment1->setParentId($segment->getId());

        $subsegment2->setIndependent(true);
        $subsegment2->setTraceId($segment->getTraceId());
        $subsegment2->setParentId($segment->getId());

        $subsegment3->setIndependent(true);
        $subsegment3->setTraceId($segment->getTraceId());
        $subsegment3->setParentId($segment->getId());

        $expectedPackets = [$openingSegment, $subsegment1, $subsegment2, $subsegment3, $rawSegment];

        $this->assertPacketsReceived($expectedPackets, $buffer);
    }

    /**
     * @param array <int,mixed> $expectedPackets
     * @param array <int,mixed> $buffer
     */
    private function assertPacketsReceived(array $expectedPackets, array $buffer): void
    {
        for ($i = 0; $i < count($expectedPackets); $i++) {
            $this->assertEquals(
                json_encode(DaemonSegmentSubmitter::HEADER) . "\n" . json_encode($expectedPackets[$i]),
                $buffer[$i]
            );
        }
    }

    /**
     * @return  array<int, string>
     */
    private function receivePackets(int $number): array
    {
        $from   = '';
        $port   = 0;
        $buffer = array_fill(0, $number, '');

        for ($i = 0; $i < $number; $i++) {
            socket_recvfrom($this->socket, $buffer[$i], 65535, 0, $from, $port);
        }

        return $buffer;
    }
}
