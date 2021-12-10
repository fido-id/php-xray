<?php

namespace Fido\PHPXray\Submission;

use Error;
use Fido\PHPXray\DictionaryInterface;
use Fido\PHPXray\Segment;
use Socket;
use Webmozart\Assert\Assert;

use function socket_create;
use function socket_last_error;

class DaemonSegmentSubmitter implements SegmentSubmitter
{
    protected const MAX_SEGMENT_SIZE = 64000;

    /**
     * @var array<string, mixed>
     */
    public const HEADER = ['format' => 'json', 'version' => 1];
    /** @var Socket */
    private $socket;

    /** @infection-ignore-all */
    public function __construct(
        private string $host = '127.0.0.1',
        private int $port = 2000,
        int $socketDomain = AF_INET,
        int $socketType = SOCK_DGRAM,
        int $socketProtocol = SOL_UDP
    )
    {
        if (isset($_SERVER[DictionaryInterface::DAEMON_ADDRESS_AND_PORT])) {
            [$host, $port] = explode(":", $_SERVER[DictionaryInterface::DAEMON_ADDRESS_AND_PORT]);
        }

        $this->host = $_SERVER[DictionaryInterface::DAEMON_ADDRESS] ?? $host;
        $this->port = (int)($_SERVER[DictionaryInterface::DAEMON_PORT] ?? $port);

        $socket = socket_create($socketDomain, $socketType, $socketProtocol);
        Assert::notFalse($socket);
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
        $traceId = $segment->getTraceId();
        Assert::string($traceId, "Trace ID is mandatory when subsegment are sent independently.");
        /** @var Segment[] $subsegments */
        $subsegments = $rawSegment[DictionaryInterface::SEGMENT_KEY_MAIN_SUBSEGMENTS] ?? [];
        unset($rawSegment[DictionaryInterface::SEGMENT_KEY_MAIN_SUBSEGMENTS]);
        $this->submitOpenSegment($rawSegment);

        foreach ($subsegments as $subsegment) {
            $subsegment = clone $subsegment;
            $subsegment->setParentId($segment->getId());
            $subsegment->setTraceId($traceId);
            $subsegment->setIndependent(true);
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
        unset($openSegment[DictionaryInterface::SEGMENT_KEY_MAIN_END_TIME]);
        $openSegment[DictionaryInterface::SEGMENT_KEY_MAIN_IN_PROGRESS] = true;

        $this->sendPacket($this->buildPacket($openSegment));
    }
}
