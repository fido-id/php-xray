<?php

declare(strict_types=1);

namespace Fido\PHPXray;

use Psr\Http\Message\ResponseInterface;

class HttpSegment extends RemoteSegment implements HttpInterface
{
    public function __construct(
        string $name,
        protected string $url,
        protected string $method,
        protected int $responseCode = 0,
        bool $traced = false,
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
            traced: $traced,
            parentId: $parentId,
            traceId: $traceId,
            error: $error,
            fault: $fault,
            cause: $cause,
            independent: $independent,
            lastOpenSegment: $lastOpenSegment,
        );
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data[DictionaryInterface::SEGMENT_KEY_MAIN_HTTP] = $this->serializeHttpData();

        return $data;
    }

    public function serializeHttpData(): array
    {
        return [
            DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST  => [
                DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST_URL    => $this->url,
                DictionaryInterface::SEGMENT_KEY_HTTP_REQUEST_METHOD => $this->method,
            ],
            DictionaryInterface::SEGMENT_KEY_HTTP_RESPONSE => [
                DictionaryInterface::SEGMENT_KEY_HTTP_RESPONSE_STATUS => $this->responseCode,
            ],
        ];
    }

    public function closeWithPsrResponse(ResponseInterface $response): void
    {
        $this->responseCode = $response->getStatusCode();

        if ($response->getStatusCode() >= 500) {
            $this->setFault(true);
        }

        if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
            $this->setError(true);
        }

        $this->addMetadata("content", $response->getBody()->getContents());
        $this->addMetadata("reason", $response->getReasonPhrase());
        $this->addMetadata("headers", $response->getHeaders());

        $response->getBody()->rewind();
        
        $this->end();
    }
}
