<?php

namespace Fido\PHPXray\Submission;

use Error;
use Fido\PHPXray\Segment;
use Socket;

use function socket_create;
use function socket_last_error;

class DaemonSegmentSubmitter implements SegmentSubmitter
{
    protected const MAX_SEGMENT_SIZE = 64000;

    /** @var array<string, mixed> */
    public const HEADER = [ 'format'  => 'json', 'version' => 1];
    private string $host;
    private int    $port;
    private Socket $socket;

    public function __construct(string $host = '127.0.0.1', int $port = 2000)
    {
        if (isset($_SERVER['AWS_XRAY_DAEMON_ADDRESS'])) {
            [$host, $port] = explode(":", $_SERVER['AWS_XRAY_DAEMON_ADDRESS']);
        }

        $this->host = $_SERVER['_AWS_XRAY_DAEMON_ADDRESS'] ?? $host;
        $this->port = (int)($_SERVER['_AWS_XRAY_DAEMON_PORT'] ?? $port);

        if (!$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
            throw new Error('Can\'t create socket: ' . socket_last_error());
        }
        $this->socket = $socket;
    }

    public function __destruct()
    {
        socket_close($this->socket);
    }

    public function submitSegment(Segment $segment): void
    {
        $packet       = $this->buildPacket($segment);
        $packetLength = strlen($packet);

        if ($packetLength > self::MAX_SEGMENT_SIZE) {
            $this->submitFragmented($segment);
            return;
        }

        $this->sendPacket($packet);
    }

    /**
     * @param Segment|array<string, mixed> $segment
     */
    private function buildPacket(Segment|array $segment): string
    {
        return implode("\n", array_map('json_encode', [self::HEADER, $segment]));
    }

    private function sendPacket(string $packet): void
    {
        socket_sendto($this->socket, $packet, strlen($packet), 0, $this->host, $this->port);
    }

    private function submitFragmented(Segment $segment): void
    {
        $rawSegment = $segment->jsonSerialize();
        /** @var Segment[] $subsegments */
        $subsegments = $rawSegment['subsegments'] ?? [];
        unset($rawSegment['subsegments']);
        $this->submitOpenSegment($rawSegment);

        foreach ($subsegments as $subsegment) {
            $subsegment = clone $subsegment;
            $subsegment
                ->setParentId($segment->getId())
                ->setTraceId($segment->getTraceId())
                ->setIndependent(true)
            ;
            $this->submitSegment($subsegment);
        }

        $completePacket = $this->buildPacket($rawSegment);
        $this->sendPacket($completePacket);
    }

    /**
     * @param array<string, mixed> $openSegment
     */
    private function submitOpenSegment(array $openSegment): void
    {
        unset($openSegment['end_time']);
        $openSegment['in_progress'] = true;

        $this->sendPacket($this->buildPacket($openSegment));
    }
}
