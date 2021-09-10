<?php

namespace Pkerrigan\Xray;

use Exception;
use JsonSerializable;
use Pkerrigan\Xray\Submission\SegmentSubmitter;

/**
 *
 * @author Patrick Kerrigan (patrickkerrigan.uk)
 * @since 13/05/2018
 */
class Segment implements JsonSerializable
{
    /**
     * @var string
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    protected $id;
    /**
     * @var string
     */
    protected $parentId;
    /**
     * @var string
     */
    protected $traceId;
    /**
     * @var string|null
     */
    protected $name;
    /**
     * @var float
     */
    protected $startTime;
    /**
     * @var float
     */
    protected $endTime;
    /**
     * @var Segment[]
     */
    protected $subsegments = [];
    /**
     * @var bool
     */
    protected $error = false;
    /**
     * @var bool
     */
    protected $fault = false;
    /**
     * @var bool
     */
    protected $sampled = false;
    /**
     * @var bool
     */
    protected $independent = false;
    /**
     * @var string[]
     */
    private $annotations;
    /**
     * @var string[]
     */
    private $metadata;
    /**
     * @var int
     */
    private $lastOpenSegment = 0;

    public function __construct()
    {
        try {
            $this->id = bin2hex(random_bytes(8));
        } catch (Exception $e) {
        }
    }

    /**
     * @param string $traceHeader
     * @return static
     */
    public function setTraceHeader(string $traceHeader = null): Segment
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
     * @return static
     */
    public function begin(): Segment
    {
        $this->startTime = microtime(true);

        return $this;
    }

    /**
     * @return static
     */
    public function end(): Segment
    {
        $this->endTime = microtime(true);

        return $this;
    }

    /**
     * @param string $name
     * @return static
     */
    public function setName(string $name): Segment
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param bool $error
     * @return static
     */
    public function setError(bool $error): Segment
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @param bool $fault
     * @return static
     */
    public function setFault(bool $fault): Segment
    {
        $this->fault = $fault;

        return $this;
    }

    /**
     * @param Segment $subsegment
     * @return static
     */
    public function addSubsegment(Segment $subsegment): Segment
    {
        if (!$this->isOpen()) {
            return $this;
        }

        $this->subsegments[] = $subsegment;
        $subsegment->setSampled($this->isSampled());

        return $this;
    }

    /**
     * @param SegmentSubmitter $submitter
     */
    public function submit(SegmentSubmitter $submitter)
    {
        if (!$this->isSampled()) {
            return;
        }

        $submitter->submitSegment($this);
    }

    /**
     * @return bool
     */
    public function isSampled(): bool
    {
        return $this->sampled;
    }

    /**
     * @param bool $sampled
     * @return static
     */
    public function setSampled(bool $sampled): Segment
    {
        $this->sampled = $sampled;

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string|null $parentId
     * @return static
     */
    public function setParentId(string $parentId = null): Segment
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * @param string $traceId
     * @return static
     */
    public function setTraceId(string $traceId): Segment
    {
        $this->traceId = $traceId;

        return $this;
    }

    public function getTraceId(): string
    {
        return $this->traceId;
    }

    /**
     * @return bool
     */
    public function isOpen(): bool
    {
        return !is_null($this->startTime) && is_null($this->endTime);
    }

    /**
     * @param bool $independent
     * @return static
     */
    public function setIndependent(bool $independent): Segment
    {
        $this->independent = $independent;

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return static
     */
    public function addAnnotation(string $key, string $value): Segment
    {
        $this->annotations[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @return static
     */
    public function addMetadata(string $key, $value): Segment
    {
        $this->metadata[$key] = $value;

        return $this;
    }

    /**
     * @return Segment
     */
    public function getCurrentSegment(): Segment
    {
        for ($max = count($this->subsegments); $this->lastOpenSegment < $max; $this->lastOpenSegment++) {
            if ($this->subsegments[$this->lastOpenSegment]->isOpen()) {
                return $this->subsegments[$this->lastOpenSegment]->getCurrentSegment();
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'id' => $this->id,
            'parent_id' => $this->parentId,
            'trace_id' => $this->traceId,
            'name' => $this->name ?? null,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'subsegments' => empty($this->subsegments) ? null : $this->subsegments,
            'type' => $this->independent ? 'subsegment' : null,
            'fault' => $this->fault,
            'error' => $this->error,
            'annotations' => empty($this->annotations) ? null : $this->annotations,
            'metadata' => empty($this->metadata) ? null : $this->metadata
        ]);
    }
}
