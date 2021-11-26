<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;

class DynamoSegmentTest extends TestCase
{
    public function testSerialisesCorrectly(): void
    {
        $segment = new DynamoSegment();
        $segment
            ->setTableName('example-table')
            ->setOperation('UpdateItem')
            ->setRequestId('3AIENM5J4ELQ3SPODHKBIRVIC3VV4KQNSO5AEMVJF66Q9ASUAAJG')
            ->addResourceName('value')
        ;

        $serialised = $segment->jsonSerialize();

        foreach ($serialised['aws'] as $item){
            self::assertNotNull($item);
        }

        $this->assertEquals('example-table', $serialised['aws']['table_name']);
        $this->assertEquals('UpdateItem', $serialised['aws']['operation']);
        $this->assertEquals('3AIENM5J4ELQ3SPODHKBIRVIC3VV4KQNSO5AEMVJF66Q9ASUAAJG', $serialised['aws']['request_id']);
        $this->assertContains('value', $serialised['aws']['resource_names']);
    }

    public function testResourceNameCanBeEmpty(): void
    {
        $segment = new DynamoSegment();
        $segment
            ->setTableName('example-table')
            ->setOperation('UpdateItem')
            ->setRequestId('3AIENM5J4ELQ3SPODHKBIRVIC3VV4KQNSO5AEMVJF66Q9ASUAAJG')
        ;

        $serialised = $segment->jsonSerialize();

        foreach ($serialised['aws'] as $item){
            self::assertNotEmpty($item);
        }

        $this->assertEquals('example-table', $serialised['aws']['table_name']);
        $this->assertEquals('UpdateItem', $serialised['aws']['operation']);
    }
}
