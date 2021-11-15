<?php

namespace Fido\PHPXray;

class DynamoSegment extends RemoteSegment
{
    protected string $tableName;
    protected string $operation;
    protected string $requestId;
    /** @var string[] */
    protected array $resourceNames;

    public function setTableName(string $tableName): self
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function setOperation(string $operation): self
    {
        $this->operation = $operation;

        return $this;
    }

    public function setRequestId(string $requestId): self
    {
        $this->requestId = $requestId;

        return $this;
    }

    public function addResourceName(string $value): self
    {
        $this->resourceNames[] = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data[DictionaryInterface::SEGMENT_KEY_MAIN_AWS] = \array_filter([
            DictionaryInterface::SEGMENT_KEY_AWS_TABLE_NAME => $this->tableName ?? null,
            DictionaryInterface::SEGMENT_KEY_AWS_OPERATION => $this->operation ?? null,
            DictionaryInterface::SEGMENT_KEY_AWS_REQUEST_ID => $this->requestId ?? null,
            DictionaryInterface::SEGMENT_KEY_AWS_RESOURCE_NAMES => ($this->resourceNames ?? null) ?: null,
        ]);

        return $data;
    }
}
