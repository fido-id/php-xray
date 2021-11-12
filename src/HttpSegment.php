<?php

namespace Fido\PHPXray;

class HttpSegment extends RemoteSegment implements HttpInterface
{
    protected string $url;
    protected string $method;
    protected string $clientIpAddress;
    protected string $userAgent;
    protected int $responseCode;

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

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data[self::SEGMENT_KEY_MAIN_HTTP] = $this->serialiseHttpData();

        return array_filter($data);
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
