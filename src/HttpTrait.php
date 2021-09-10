<?php

namespace Fido\PHPXray;

trait HttpTrait
{
    protected string $url;
    protected string $method;
    protected string $clientIpAddress;
    protected string $userAgent;
    protected int    $responseCode;

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
     * @return array{request: array<string, mixed>, response: array<string, mixed>}
     */
    protected function serialiseHttpData(): array
    {
        return [
            'request'  => array_filter([
                'url'        => $this->url ?? null,
                'method'     => $this->method ?? null,
                'client_ip'  => $this->clientIpAddress ?? null,
                'user_agent' => $this->userAgent ?? null,
            ]),
            'response' => array_filter([
                'status' => $this->responseCode ?? null,
            ]),
        ];
    }
}
