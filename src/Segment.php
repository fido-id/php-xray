<?php

namespace Fido\PHPXray;

use JsonSerializable;
use Fido\PHPXray\Submission\SegmentSubmitter;

class Segment implements JsonSerializable
{
    protected string  $id;
    protected ?string $parentId = null;
    protected string  $traceId;
    protected ?string $name     = null;
    protected float   $startTime;
    protected float   $endTime;
    /** @var Segment[] */
    protected array $subsegments = [];
    protected bool  $error       = false;
    protected bool  $fault       = false;
    protected bool  $sampled     = false;
    protected bool  $independent = false;
    /** @var array<string, mixed> */
    private array $annotations;
    /** @var array<string, mixed> */
    private array $metadata;
    private int   $lastOpenSegment = 0;

    /**
     * @throws \Exception if an appropriate source of randomness cannot be found.
     */
    public function __construct()
    {
        $this->id = bin2hex(random_bytes(8));
    }

    public function setTraceHeader(string $traceHeader = null): self
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

    public function begin(): self
    {
        $this->startTime = microtime(true);

        return $this;
    }

    public function end(): self
    {
        $this->endTime = microtime(true);

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setError(bool $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function setFault(bool $fault): self
    {
        $this->fault = $fault;

        return $this;
    }

    public function addSubsegment(Segment $subsegment): self
    {
        if (!$this->isOpen()) {
            return $this;
        }

        $this->subsegments[] = $subsegment;
        $subsegment->setSampled($this->isSampled());

        return $this;
    }

    public function submit(SegmentSubmitter $submitter): void
    {
        if (!$this->isSampled()) {
            return;
        }

        $submitter->submitSegment($this);
    }

    public function isSampled(): bool
    {
        return $this->sampled;
    }

    public function setSampled(bool $sampled): self
    {
        $this->sampled = $sampled;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setParentId(?string $parentId): self
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function setTraceId(string $traceId): self
    {
        $this->traceId = $traceId;

        return $this;
    }

    public function getTraceId(): string
    {
        return $this->traceId;
    }

    public function isOpen(): bool
    {
        return isset($this->startTime) && !isset($this->endTime);
    }

    public function setIndependent(bool $independent): self
    {
        $this->independent = $independent;

        return $this;
    }

    public function addAnnotation(string $key, string $value): self
    {
        $this->annotations[$key] = $value;

        return $this;
    }

    public function addMetadata(string $key, mixed $value): self
    {
        $this->metadata[$key] = $value;

        return $this;
    }

    public function getCurrentSegment(): self
    {
        for ($max = count($this->subsegments); $this->lastOpenSegment < $max; $this->lastOpenSegment++) {
            if ($this->subsegments[$this->lastOpenSegment]->isOpen()) {
                return $this->subsegments[$this->lastOpenSegment]->getCurrentSegment();
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'id'          => $this->id,
            'parent_id'   => $this->parentId,
            'trace_id'    => $this->traceId ?? null,
            'name'        => $this->name,
            'start_time'  => $this->startTime ?? null,
            'end_time'    => $this->endTime ?? null,
            'subsegments' => $this->subsegments ?? null ?: null,
            'type'        => $this->independent ? 'subsegment' : null,
            'fault'       => $this->fault ?? null,
            'error'       => $this->error ?? null,
            'annotations' => $this->annotations ?? null ?: null,
            'metadata'    => $this->metadata ?? null ?: null,
        ]);
    }
}
