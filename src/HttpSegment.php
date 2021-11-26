<?php

declare(strict_types=1);

namespace Fido\PHPXray;

class HttpSegment extends RemoteSegment implements HttpInterface
{
    protected string $url;
    protected string $method;
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

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data[DictionaryInterface::SEGMENT_KEY_MAIN_HTTP] = $this->serialiseHttpData();

        return $data;
    }

    public function serialiseHttpData(): array
    {
        return [
            DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST => [
                DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST_URL => $this->url,
                DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST_METHOD => $this->method,
            ],
            DictionaryInterface::SEGMENT_KEY_HTTP_RESPONSE => [
                DictionaryInterface::SEGMENT_KEY_HTTP_RESPONSE_STATUS => $this->responseCode,
            ],
        ];
    }
}
