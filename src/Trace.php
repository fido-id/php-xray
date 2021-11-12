<?php

namespace Fido\PHPXray;

use Exception;

class Trace extends Segment implements Typed, HttpInterface
{
    protected string $url;
    protected string $method;
    protected string $clientIpAddress;
    protected string $userAgent;
    protected int $responseCode;
    private static self $instance;
    private string $serviceVersion;
    private string $user;

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function setResponseCode(int $responseCode): self
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function setServiceVersion(string $serviceVersion): self
    {
        $this->serviceVersion = $serviceVersion;

        return $this;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setClientIpAddress(string $clientIpAddress): self
    {
        $this->clientIpAddress = $clientIpAddress;

        return $this;
    }

    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function begin(int $samplePercentage = 10): Segment
    {
        parent::begin();

        if (!isset($this->traceId)) {
            $this->generateTraceId();
        }

        if (!$this->isSampled()) {
            $this->sampled = (random_int(0, 99) < $samplePercentage);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data[self::SEGMENT_KEY_MAIN_HTTP]    = $this->serialiseHttpData();
        $data[self::SEGMENT_KEY_MAIN_SERVICE] = empty($this->serviceVersion) ? null : ['version' => $this->serviceVersion];
        $data[self::SEGMENT_KEY_SQL_USER]    = $this->user ?? null;

        return array_filter($data);
    }

    /**
     * @throws Exception if an appropriate source of randomness cannot be found.
     */
    private function generateTraceId(): void
    {
        $startHex = dechex((int)$this->startTime);
        $uuid     = bin2hex(random_bytes(12));
        $this->setTraceId("1-$startHex-$uuid");
    }

    public function serialiseHttpData(): array
    {
        return [
            self::SEGMENT_KEY_HTTP_REQUEST => \array_filter([
                self::SEGMENT_KEY_HTTP_REQUEST_URL => $this->url ?? null,
                self::SEGMENT_KEY_HTTP_REQUEST_METHOD => $this->method ?? null,
                self::SEGMENT_KEY_HTTP_REQUEST_CLIENT_IP => $this->clientIpAddress ?? null,
                self::SEGMENT_KEY_HTTP_REQUEST_USER_AGENT => $this->userAgent ?? null,
            ]),
            self::SEGMENT_KEY_HTTP_RESPONSE => \array_filter([
                self::SEGMENT_KEY_HTTP_RESPONSE_STATUS => $this->responseCode ?? null,
            ]),
        ];
    }
}
