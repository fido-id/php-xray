<?php

namespace Fido\PHPXray\Submission;

use Fido\PHPXray\Segment;

class DaemonSegmentSubmitter implements SegmentSubmitter
{
    const MAX_SEGMENT_SIZE = 64000;

    const HEADER = [
        'format' => 'json',
        'version' => 1
    ];

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var resource
     */
    private $socket;

    public function __construct(string $host = '127.0.0.1', int $port = 2000)
    {
        if (isset($_SERVER['AWS_XRAY_DAEMON_ADDRESS'])) {
            list($host, $port) = explode(":", $_SERVER['AWS_XRAY_DAEMON_ADDRESS']);
        }

        $this->host = $_SERVER['_AWS_XRAY_DAEMON_ADDRESS'] ?? $host;
        $this->port = (int) ($_SERVER['_AWS_XRAY_DAEMON_PORT'] ?? $port);

        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    }

    public function __destruct()
    {
        socket_close($this->socket);
    }

    /**
     * @param Segment $segment
     * @return void
     */
    public function submitSegment(Segment $segment)
    {
        $packet = $this->buildPacket($segment);
        $packetLength = strlen($packet);

        if ($packetLength > self::MAX_SEGMENT_SIZE) {
            $this->submitFragmented($segment);
            return;
        }

        $this->sendPacket($packet);
    }

    /**
     * @param Segment|array $segment
     * @return string
     */
    private function buildPacket($segment): string
    {
        return implode("\n", array_map('json_encode', [self::HEADER, $segment]));
    }

    /**
     * @param string $packet
     * @return void
     */
    private function sendPacket(string $packet)
    {
        socket_sendto($this->socket, $packet, strlen($packet), 0, $this->host, $this->port);
    }

    /**
     * @param Segment $segment
     * @return void
     */
    private function submitFragmented(Segment $segment)
    {
        $rawSegment = $segment->jsonSerialize();
        /** @var Segment[] $subsegments */
        $subsegments = $rawSegment['subsegments'] ?? [];
        unset($rawSegment['subsegments']);
        $this->submitOpenSegment($rawSegment);

        foreach ($subsegments as $subsegment) {
            $subsegment = clone $subsegment;
            $subsegment->setParentId($segment->getId())
                       ->setTraceId($segment->getTraceId())
                       ->setIndependent(true);
            $this->submitSegment($subsegment);
        }

        $completePacket = $this->buildPacket($rawSegment);
        $this->sendPacket($completePacket);
    }

    /**
     * @param array $openSegment
     * @return void
     */
    private function submitOpenSegment(array $openSegment)
    {
        unset($openSegment['end_time']);
        $openSegment['in_progress'] = true;
        $initialPacket = $this->buildPacket($openSegment);
        $this->sendPacket($initialPacket);
    }
}
