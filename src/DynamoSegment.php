<?php

declare(strict_types=1);

namespace Fido\PHPXray;

class DynamoSegment extends RemoteSegment
{
    /** @var string[] */
    protected array $resourceNames = [];

    public function __construct(
        string $name,
        protected string $tableName,
        protected string $operation,
        protected ?string $requestId = null,
        bool $traced = false,
        ?string $parentId = null,
        ?string $traceId = null,
        bool $error = false,
        bool $fault = false,
        ?Cause $cause = null,
        bool $independent = false,
        int $lastOpenSegment = 0
    )
    {
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

    public function setRequestId(?string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function addResourceName(string $value): self
    {
        $this->resourceNames[] = $value;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data[DictionaryInterface::SEGMENT_KEY_MAIN_AWS] = \array_filter([
            DictionaryInterface::SEGMENT_KEY_AWS_TABLE_NAME => $this->tableName,
            DictionaryInterface::SEGMENT_KEY_AWS_OPERATION => $this->operation,
            DictionaryInterface::SEGMENT_KEY_AWS_REQUEST_ID => $this->requestId,
            DictionaryInterface::SEGMENT_KEY_AWS_RESOURCE_NAMES => $this->resourceNames,
        ]);

        return $data;
    }
}
