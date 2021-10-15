<?php

namespace Fido\PHPXray;

trait HttpTrait
{
    /**
     * @var string
     */
    protected $url;
    /**
     * @var string
     */
    protected $method;
    /**
     * @var string
     */
    protected $clientIpAddress;
    /**
     * @var string
     */
    protected $userAgent;
    /**
     * @var int
     */
    protected $responseCode;

    /**
     * @param string $url
     * @return static
     */
    public function setUrl(string $url): Segment
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @param string $method
     * @return static
     */
    public function setMethod(string $method): Segment
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @param int $responseCode
     * @return static
     */
    public function setResponseCode(int $responseCode): Segment
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    /**
     * @return array
     */
    protected function serialiseHttpData(): array
    {
        return [
            'request' => array_filter([
                'url' => $this->url,
                'method' => $this->method,
                'client_ip' => $this->clientIpAddress,
                'user_agent' => $this->userAgent
            ]),
            'response' => array_filter([
                'status' => $this->responseCode
            ])
        ];
    }
}
