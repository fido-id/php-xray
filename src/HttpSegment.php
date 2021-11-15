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

        $data[DictionaryInterface::SEGMENT_KEY_MAIN_HTTP] = $this->serialiseHttpData();

        return array_filter($data);
    }

    public function serialiseHttpData(): array
    {
        return [
            DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST => \array_filter([
                DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST_URL => $this->url ?? null,
                DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST_METHOD => $this->method ?? null,
                DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST_CLIENT_IP => $this->clientIpAddress ?? null,
                DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST_USER_AGENT => $this->userAgent ?? null,
            ]),
            DictionaryInterface::SEGMENT_KEY_HTTP_RESPONSE => \array_filter([
                DictionaryInterface::SEGMENT_KEY_HTTP_RESPONSE_STATUS => $this->responseCode ?? null,
            ]),
        ];
    }
}
