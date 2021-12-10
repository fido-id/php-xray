<?php

namespace Fido\PHPXray;

use PHPUnit\Framework\TestCase;

class SqlSegmentTest extends TestCase
{
    public function testSerialisesCorrectly(): void
    {
        $segment = new SqlSegment(
            name: 'Test segment',
            query: 'SELECT *',
            url: 'pgsql://test@localhost',
            preparation: 'prepared',
            databaseType: 'PostgreSQL',
            databaseVersion: '10.4',
            driverVersion: '10',
            user: 'test',
        );
        $segment->end();

        $serialised = $segment->jsonSerialize();

        $this->assertEquals('remote', $serialised['namespace']);
        $this->assertEquals('SELECT *', $serialised['sql']['sanitized_query']);
        $this->assertEquals('PostgreSQL', $serialised['sql']['database_type']);
        $this->assertEquals('10.4', $serialised['sql']['database_version']);
        $this->assertEquals('10', $serialised['sql']['driver_version']);
        $this->assertEquals('test', $serialised['sql']['user']);
        $this->assertEquals('prepared', $serialised['sql']['preparation']);
        $this->assertEquals('pgsql://test@localhost', $serialised['sql']['url']);
    }
}
