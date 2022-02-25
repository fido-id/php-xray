<?php

namespace Fido\PHPXray;

use Exception;

class Trace extends Segment implements HttpInterface
{
    public function __construct(
        string $name,
        protected ?string $url = null,
        protected ?string $method = null,
        protected ?int $responseCode = null,
        protected ?string $clientIpAddress = null,
        protected ?string $userAgent = null,
        private ?string $serviceVersion = null,
        private ?string $user = null,
        ?string $parentId = null,
        ?string $traceId = null,
        bool $error = false,
        bool $fault = false,
        ?Cause $cause = null,
        bool $independent = false,
        int $lastOpenSegment = 0
    ) {
        parent::__construct(
            name: $name,
            parentId: $parentId,
            traceId: $traceId,
            error: $error,
            fault: $fault,
            cause: $cause,
            independent: $independent,
            lastOpenSegment: $lastOpenSegment,
        );
        if (!isset($this->traceId)) {
            $this->generateTraceId();
        }
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data[DictionaryInterface::SEGMENT_KEY_MAIN_HTTP]    = $this->serializeHttpData();
        $data[DictionaryInterface::SEGMENT_KEY_MAIN_SERVICE] = empty($this->serviceVersion) ? null : ['version' => $this->serviceVersion];
        $data[DictionaryInterface::SEGMENT_KEY_SQL_USER]     = $this->user;

        return array_filter($data);
    }

    /**
     * @throws Exception if an appropriate source of randomness cannot be found.
     */
    private function generateTraceId(): void
    {
        $startHex = dechex((int)$this->getStartTime());
        $uuid     = bin2hex(random_bytes(12));
        $this->setTraceId("1-$startHex-$uuid");
    }

    public function serializeHttpData(): array
    {
        return [
            DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST  => \array_filter([
                DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST_URL        => $this->url,
                DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST_METHOD     => $this->method,
                DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST_CLIENT_IP  => $this->clientIpAddress,
                DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST_USER_AGENT => $this->userAgent,
            ]),
            DictionaryInterface::SEGMENT_KEY_HTTP_RESPONSE => \array_filter([
                DictionaryInterface::SEGMENT_KEY_HTTP_RESPONSE_STATUS => $this->responseCode ,
            ]),
        ];
    }
}
