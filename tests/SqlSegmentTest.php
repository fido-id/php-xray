<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;

class SqlSegmentTest extends TestCase
{
    use TestSubsegmentTrait;

    public function testSerialisesCorrectly(): void
    {
        $segment = $this->getNewSegment();
        $segment->end();

        $serialized = \json_decode(\json_encode($segment), true);
        $this->assertIsNumeric($serialized['end_time']);
        unset($serialized['end_time']);
        $this->assertSame([
            'id'         => $segment->getId(),
            'name'       => 'SQL Segment',
            'start_time' => $segment->getStartTime(),
            'namespace'  => 'remote',
            'sql'        => [
                'sanitized_query' => 'SELECT *',
            ],
        ], $serialized);

        $segment = new SqlSegment(
            name: 'SQL Segment',
            query: 'SELECT *',
            url: 'url',
            preparation: 'preparation',
            databaseType: 'database_type',
            databaseVersion: 'database_version',
            driverVersion: 'driver_version',
            user: 'user',
            traced: true,
            parentId: 'parent_id',
            traceId: 'trace_id',
            error: true,
            fault: true,
            cause: new Cause('working_directory', [], []),
            independent: true,
            lastOpenSegment: 0,
        );
        $segment->end();

        $serialized = \json_decode(\json_encode($segment), true);
        $this->assertIsNumeric($serialized['end_time']);
        unset($serialized['end_time']);
        $this->assertSame([
            'id'         => $segment->getId(),
            'parent_id'  => 'parent_id',
            'trace_id'   => 'trace_id',
            'name'       => 'SQL Segment',
            'start_time' => $segment->getStartTime(),
            'type'       => 'subsegment',
            'fault'      => true,
            'error'      => true,
            'cause'      => [
                'working_directory' => 'working_directory',
                'paths'             => [],
                'exceptions'        => [],
            ],
            'namespace'  => 'remote',
            'traced'     => true,
            'sql'        => [
                'url'              => 'url',
                'preparation'      => 'preparation',
                'database_type'    => 'database_type',
                'database_version' => 'database_version',
                'driver_version'   => 'driver_version',
                'user'             => 'user',
                'sanitized_query'  => 'SELECT *',
            ],
        ], $serialized);
    }

    private function getNewSegment(string $name = 'SQL Segment'): SqlSegment
    {
        return new SqlSegment(
            name: $name,
            query: 'SELECT *'
        );
    }
}
