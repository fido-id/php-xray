<?php

namespace Fido\PHPXray;

use Exception;

class Trace extends Segment
{
    use HttpTrait;

    private static self $instance;
    private string $serviceVersion;
    private string $user;

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

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data['http']    = $this->serialiseHttpData();
        $data['service'] = empty($this->serviceVersion) ? null : ['version' => $this->serviceVersion];
        $data['user']    = $this->user ?? null;

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
}
