<?php

namespace Pkerrigan\Xray;

use Exception;

/**
 *
 * @author Patrick Kerrigan (patrickkerrigan.uk)
 * @since 13/05/2018
 */
class Trace extends Segment
{
    use HttpTrait;

    /**
     * @var static
     */
    private static $instance;
    /**
     * @var string
     */
    private $serviceVersion;
    /**
     * @var string
     */
    private $user;

    /**
     * @return static
     */
    public static function getInstance(): Trace
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @param string $traceHeader
     * @return static
     */
    public function setTraceHeader(string $traceHeader = null): Trace
    {
        if (is_null($traceHeader)) {
            return $this;
        }

        $parts = explode(';', $traceHeader);

        $variables = array_map(function ($str): array {
            return explode('=', $str);
        }, $parts);

        $variables = array_column($variables, 1, 0);

        if (isset($variables['Root'])) {
            $this->setTraceId($variables['Root']);
        }
        if (isset($variables['Sampled'])) {
            $this->setSampled((bool)$variables['Sampled'] ?? false);
        }
        if (isset($variables['Parent'])) {
            $this->setParentId($variables['Parent'] ?? null);
        }

        return $this;
    }

    /**
     * @param string $serviceVersion
     * @return static
     */
    public function setServiceVersion(string $serviceVersion): Trace
    {
        $this->serviceVersion = $serviceVersion;

        return $this;
    }

    /**
     * @param string $user
     * @return static
     */
    public function setUser(string $user): Trace
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param string $clientIpAddress
     * @return static
     */
    public function setClientIpAddress(string $clientIpAddress): Trace
    {
        $this->clientIpAddress = $clientIpAddress;

        return $this;
    }

    /**
     * @param string $userAgent
     * @return static
     */
    public function setUserAgent(string $userAgent): Trace
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function begin(int $samplePercentage = 10): Segment
    {
        parent::begin();

        if (is_null($this->traceId)) {
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

        $data['http'] = $this->serialiseHttpData();
        $data['service'] = empty($this->serviceVersion) ? null : ['version' => $this->serviceVersion];
        $data['user'] = $this->user;

        return array_filter($data);
    }

    private function generateTraceId()
    {
        $startHex = dechex((int)$this->startTime);
        try {
            $uuid = bin2hex(random_bytes(12));
            $this->setTraceId("1-{$startHex}-{$uuid}");
        } catch (Exception $e) {
        }
    }
}
