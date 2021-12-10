<?php

namespace Fido\PHPXray;

use Fido\PHPXray\Submission\SegmentSubmitter;
use JsonSerializable;
use Webmozart\Assert\Assert;

class Segment implements JsonSerializable
{
    protected string  $id;
    protected ?string $parentId = null;
    protected string  $traceId;
    protected ?string $name     = null;
    protected float   $startTime;
    protected float   $endTime;
    /** @var Segment[] */
    protected array  $subsegments = [];
    protected bool   $error       = false;
    protected bool   $fault       = false;
    //todo handle cause object OR exception ID
    protected ?Cause $cause       = null;
    protected bool   $independent = false;
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
        Assert::true($this->isOpen(), 'Cant add a subsegment to a closed segment!');

        $subsegment->setParentId($this->id);

        $this->subsegments[] = $subsegment;

        return $this;
    }

    public function submit(SegmentSubmitter $submitter): void
    {
        $submitter->submitSegment($this);
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

    public function getCause(): ?Cause
    {
        return $this->cause;
    }

    public function setCause(?Cause $cause): Segment
    {
        $this->cause = $cause;
        return $this;
    }

    /**
     * @inheritDoc
     * @return array<string, string|bool|float|null|array|Cause>
     */
    public function jsonSerialize(): array
    {
        return \array_filter([
            DictionaryInterface::SEGMENT_KEY_MAIN_ID          => $this->id,
            DictionaryInterface::SEGMENT_KEY_MAIN_PARENT_ID   => $this->parentId,
            DictionaryInterface::SEGMENT_KEY_MAIN_TRACE_ID    => $this->traceId ?? null,
            DictionaryInterface::SEGMENT_KEY_MAIN_NAME        => $this->name,
            DictionaryInterface::SEGMENT_KEY_MAIN_START_TIME  => $this->startTime ?? null,
            DictionaryInterface::SEGMENT_KEY_MAIN_END_TIME    => $this->endTime ?? null,
            DictionaryInterface::SEGMENT_KEY_MAIN_SUBSEGMENTS => ($this->subsegments ?? null) ?: null,
            DictionaryInterface::SEGMENT_KEY_MAIN_TYPE        => $this->independent ? DictionaryInterface::SEGMENT_ENUM_MAIN_TYPE_SUBSEGMENT : null,
            DictionaryInterface::SEGMENT_KEY_MAIN_FAULT       => $this->fault ?? null,
            DictionaryInterface::SEGMENT_KEY_MAIN_ERROR       => $this->error ?? null,
            DictionaryInterface::SEGMENT_KEY_MAIN_CAUSE       => $this->cause ?? null,
            DictionaryInterface::SEGMENT_KEY_MAIN_ANNOTATIONS => ($this->annotations ?? null) ?: null,
            DictionaryInterface::SEGMENT_KEY_MAIN_METADATA    => ($this->metadata ?? null) ?: null,
        ]);
    }
}
