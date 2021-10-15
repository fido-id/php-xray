<?php

namespace Fido\PHPXray;

class DynamoSegment extends RemoteSegment
{

    /**
     * @var string|null
     */
    protected $tableName;
    
    /**
     * @var string|null
     */
    protected $operation;

    /**
     * @var string|null
     */
    protected $requestId;

    /**
     * @var array|null
     */
    protected $resourceNames;

    /**
     * @param string $tableName
     * @return static
     */
    public function setTableName(string $tableName): DynamoSegment
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @param string $operation
     * @return static
     */
    public function setOperation(string $operation): DynamoSegment
    {
        $this->operation = $operation;

        return $this;
    }

    /**
     * @param string $requestId
     * @return static
     */
    public function setRequestId(string $requestId): DynamoSegment
    {
        $this->requestId = $requestId;

        return $this;
    }

    /**
     * @param string $value
     * @return static
     */
    public function addResourceName(string $value): DynamoSegment
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
            'table_name' => $this->tableName,
            'operation' => $this->operation,
            'request_id' => $this->requestId,
            'resource_names' => $this->resourceNames
        ]);

        return $data;
    }
}
