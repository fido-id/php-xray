<?php

declare(strict_types=1);

namespace Fido\PHPXray;

use Psr\Http\Message\ResponseInterface;

class HttpSegment extends RemoteSegment implements HttpInterface
{
    protected string $url;
    protected string $method;
    protected int $responseCode = 0;

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

    public static function open(string $name, string $url, string $method): self
    {
        $self = new self();

        $self->setName($name);
        $self->setUrl($url);
        $self->setMethod($method);
        $self->begin();

        return $self;
    }

    public function closeWithPsrResponse(ResponseInterface $response): self
    {
        $this->setResponseCode($response->getStatusCode());

        if ($response->getStatusCode() >= 500 && $response->getStatusCode() < 600) {
            $this->setFault(true);
        }

        if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
            $this->setError(true);
        }

        $this->addMetadata("content", $response->getBody()->getContents());
        $this->addMetadata("reason", $response->getReasonPhrase());
        $this->addMetadata("headers", $response->getHeaders());

        $this->end();

        return $this;
    }
}
