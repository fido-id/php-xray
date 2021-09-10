<?php

namespace Pkerrigan\Xray;

/**
 *
 * @author Patrick Kerrigan (patrickkerrigan.uk)
 * @since  14/05/2018
 */
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

        $data['aws'] = array_filter([
            'table_name'     => $this->tableName ?? null,
            'operation'      => $this->operation ?? null,
            'request_id'     => $this->requestId ?? null,
            'resource_names' => $this->resourceNames ?? null ?: null,
        ]);

        return $data;
    }
}
