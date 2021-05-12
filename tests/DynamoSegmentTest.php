<?php

namespace Pkerrigan\Xray;

use PHPUnit\Framework\TestCase;

/**
 *
 * @author Patrick Kerrigan (patrickkerrigan.uk)
 * @since 17/05/2018
 */
class DynamoSegmentTest extends TestCase
{
    public function testSerialisesCorrectly()
    {
        $segment = new DynamoSegment();
        $segment->setTableName('example-table')
            ->setOperation('UpdateItem')
            ->setRequestId('3AIENM5J4ELQ3SPODHKBIRVIC3VV4KQNSO5AEMVJF66Q9ASUAAJG')
            ->addResourceName('resource', 'value');

        $serialised = $segment->jsonSerialize();

        $this->assertEquals('example-table', $serialised['aws']['table_name']);
        $this->assertEquals('UpdateItem', $serialised['aws']['operation']);
        $this->assertEquals('3AIENM5J4ELQ3SPODHKBIRVIC3VV4KQNSO5AEMVJF66Q9ASUAAJG', $serialised['aws']['request_id']);
        $this->assertEquals('value', $serialised['aws']['resource_names']['resource']);
    }
}
