<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;

class DynamoSegmentTest extends TestCase
{
    use TestSubsegmentTrait;

    public function testSerialisesCorrectly(): void
    {
        $segment = $this->getNewSegment();
        $segment->addResourceName('value');
        $segment->end();

        $serialised = $segment->jsonSerialize();

        $this->assertIsNumeric($serialised['end_time']);
        unset($serialised['end_time']);
        $this->assertSame([
            'id'         => $segment->getId(),
            'name'       => 'Dynamo segment',
            'start_time' => $segment->getStartTime(),
            'namespace'  => 'remote',
            'aws'        => [
                'table_name'     => 'example-table',
                'operation'      => 'UpdateItem',
                'request_id'     => '3AIENM5J4ELQ3SPODHKBIRVIC3VV4KQNSO5AEMVJF66Q9ASUAAJG',
                'resource_names' => ['value',],
            ],
        ], $serialised);

        $segment = new DynamoSegment(
            name: 'name',
            tableName: 'tableName',
            operation: 'operation',
            requestId: 'requestId',
            traced: true,
            parentId: 'parentId',
            traceId: 'traceId',
            error: true,
            fault: true,
            cause: new Cause('workDir', [], []),
            independent: true,
            lastOpenSegment: 0
        );
        $segment->end();
        $serialised = \json_decode(\json_encode($segment), true);
        $this->assertIsNumeric($serialised['end_time']);
        unset($serialised['end_time']);
        $this->assertSame([
            'id'         => $segment->getId(),
            'parent_id'  => 'parentId',
            'trace_id'   => 'traceId',
            'name'       => 'name',
            'start_time' => $segment->getStartTime(),
            'type'       => 'subsegment',
            'fault'      => true,
            'error'      => true,
            'cause'      => [
                'working_directory' => 'workDir',
                'paths'             => [],
                'exceptions'        => [],
            ],
            'namespace'  => 'remote',
            'traced'     => true,
            'aws'        => [

                'table_name' => 'tableName',
                'operation'  => 'operation',
                'request_id' => 'requestId',
            ],
        ], $serialised);
    }

    public function testSerialisesCorrectly_with_requestid_null(): void
    {
        $segment = $this->getNewSegmentRequestIdNull();
        $segment->addResourceName('value');
        $segment->end();

        $serialised = $segment->jsonSerialize();
        unset($serialised['end_time']);
        $this->assertSame([
            'id'         => $segment->getId(),
            'name'       => 'Dynamo segment',
            'start_time' => $segment->getStartTime(),
            'namespace'  => 'remote',
            'aws'        => [
                'table_name'     => 'example-table',
                'operation'      => 'UpdateItem',
                'resource_names' => ['value',],
            ],
        ], $serialised);

        $segment->setRequestId('3AIENM5J4ELQ3SPODHKBIRVIC3VV4KQNSO5AEMVJF66Q9ASUAAJG');
        $serialised = $segment->jsonSerialize();
        unset($serialised['end_time']);
        $this->assertSame([
            'id'         => $segment->getId(),
            'name'       => 'Dynamo segment',
            'start_time' => $segment->getStartTime(),
            'namespace'  => 'remote',
            'aws'        => [
                'table_name'     => 'example-table',
                'operation'      => 'UpdateItem',
                'request_id'     => '3AIENM5J4ELQ3SPODHKBIRVIC3VV4KQNSO5AEMVJF66Q9ASUAAJG',
                'resource_names' => ['value',],
            ],
        ], $serialised);
    }

    private function getNewSegment(string $name = 'Dynamo segment'): DynamoSegment
    {
        return new DynamoSegment(
            name: $name,
            tableName: 'example-table',
            operation: 'UpdateItem',
            requestId: '3AIENM5J4ELQ3SPODHKBIRVIC3VV4KQNSO5AEMVJF66Q9ASUAAJG',
        );
    }

    private function getNewSegmentRequestIdNull(string $name = 'Dynamo segment'): DynamoSegment
    {
        return new DynamoSegment(
            name: $name,
            tableName: 'example-table',
            operation: 'UpdateItem',
        );
    }
}
