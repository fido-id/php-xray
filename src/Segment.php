<?php

namespace Fido\PHPXray;

use JsonSerializable;
use Webmozart\Assert\Assert;

class Segment implements JsonSerializable
{
    protected string $id;
    private float    $startTime;
    private float    $endTime;
    /**
     * @var array<string, string>
     */
    private array  $annotations = [];
    /**
     * @var array<string, mixed>
     */
    private array $metadata = [];
    /**
     * @var Segment[]
     */
    protected array $subsegments = [];

    /**
     * @throws \Exception if an appropriate source of randomness cannot be found.
     */
    public function __construct(
        protected string $name,
        protected ?string $parentId = null,
        protected ?string $traceId = null,
        protected bool $error = false,
        protected bool $fault = false,
        //todo handle cause object OR exception ID
        protected ?Cause $cause = null,
        protected bool $independent = false,
        private int $lastOpenSegment = 0
    ) {
        $this->id = bin2hex(random_bytes(8));
        $this->begin();
    }

    public function setTraceHeader(string $traceHeader): void
    {
        $parts = explode(';', $traceHeader);

        $variables = array_map(function ($str): array {
            return explode('=', $str);
        }, $parts);

        $variables = array_column($variables, 1, 0);

        if (isset($variables['Root'])) {
            $this->setTraceId($variables['Root']);
        }

        if (isset($variables['Parent'])) {
            $this->setParentId($variables['Parent']);
        }
    }

    public function begin(): void
    {
        $this->startTime = microtime(true);
    }

    public function end(): void
    {
        $this->endTime = microtime(true);
        foreach ($this->subsegments as $subsegment) {
            if ($subsegment->isOpen()) {
                $subsegment->end();
            }
        }
    }

    public function isOpen(): bool
    {
        return isset($this->startTime) && !isset($this->endTime);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTraceId(): ?string
    {
        return $this->traceId;
    }

    public function setTraceId(string $traceId): void
    {
        $this->traceId = $traceId;
    }

    public function setParentId(?string $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function setIndependent(bool $independent): void
    {
        $this->independent = $independent;
    }

    public function setCause(?Cause $cause): void
    {
        $this->cause = $cause;
    }

    public function setError(bool $error): void
    {
        $this->error = $error;
    }

    public function setFault(bool $fault): void
    {
        $this->fault = $fault;
    }

    public function getStartTime(): float
    {
        return $this->startTime;
    }

    public function addAnnotation(string $key, string $value): void
    {
        $this->annotations[$key] = $value;
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function addSubsegment(Segment $subsegment): self
    {
        Assert::true($this->isOpen(), 'Cant add a subsegment to a closed segment!');

        $subsegment->setParentId($this->id);

        $this->subsegments[] = $subsegment;

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
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        if ($this->isOpen()) {
            throw new \RuntimeException("Segment must be closed before serialization.");
        }

        return \array_filter([
            DictionaryInterface::SEGMENT_KEY_MAIN_ID          => $this->id,
            DictionaryInterface::SEGMENT_KEY_MAIN_PARENT_ID   => $this->parentId,
            DictionaryInterface::SEGMENT_KEY_MAIN_TRACE_ID    => $this->traceId,
            DictionaryInterface::SEGMENT_KEY_MAIN_NAME        => $this->name,
            DictionaryInterface::SEGMENT_KEY_MAIN_START_TIME  => $this->startTime,
            DictionaryInterface::SEGMENT_KEY_MAIN_END_TIME    => $this->endTime,
            DictionaryInterface::SEGMENT_KEY_MAIN_SUBSEGMENTS => $this->subsegments,
            DictionaryInterface::SEGMENT_KEY_MAIN_TYPE        => $this->independent ? DictionaryInterface::SEGMENT_ENUM_MAIN_TYPE_SUBSEGMENT : null,
            DictionaryInterface::SEGMENT_KEY_MAIN_FAULT       => $this->fault,
            DictionaryInterface::SEGMENT_KEY_MAIN_ERROR       => $this->error,
            DictionaryInterface::SEGMENT_KEY_MAIN_CAUSE       => $this->cause,
            DictionaryInterface::SEGMENT_KEY_MAIN_ANNOTATIONS => $this->annotations,
            DictionaryInterface::SEGMENT_KEY_MAIN_METADATA    => $this->metadata,
        ]);
    }
}
