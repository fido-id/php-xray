<?php

namespace Fido\PHPXray;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;

class HttpSegmentTest extends TestCase
{
    use TestSubsegmentTrait;

    public function testSerialisesCorrectly(): void
    {
        $segment = new HttpSegment(
            name: 'HTTP Segment',
            url: 'url',
            method: 'method',
            responseCode: 200,
            traced: true,
            parentId: 'parent_id',
            traceId: 'trace_id',
            error: false,
            fault: false,
            cause: new Cause('working_dir', [], []),
            independent: true,
            lastOpenSegment: 1,
        );
        $segment->end();

        $serialized = \json_decode(\json_encode($segment), true);
        $this->assertIsNumeric($serialized['end_time']);
        unset($serialized['end_time']);

        $this->assertSame([
            'id'         => $segment->getId(),
            'parent_id'  => 'parent_id',
            'trace_id'   => 'trace_id',
            'name'       => 'HTTP Segment',
            'start_time' => $segment->getStartTime(),
            'type'       => 'subsegment',
            'cause'      => [
                'working_directory' => 'working_dir',
                'paths'             => [],
                'exceptions'        => [],
            ],
            'namespace'  => 'remote',
            'traced'     => true,
            'http'       => [
                'request'  => [
                    'url'    => 'url',
                    'method' => 'method',
                ],
                'response' => [
                    'status' => 200,
                ],
            ],
        ], $serialized);

        $segment = $this->getNewSegment();
        $segment->end();

        $serialized = \json_decode(\json_encode($segment), true);
        $this->assertIsNumeric($serialized['end_time']);
        unset($serialized['end_time']);
        $this->assertSame([
            'id'         => $segment->getId(),
            'name'       => 'HTTP Segment',
            'start_time' => $segment->getStartTime(),
            'namespace'  => 'remote',
            'http'       => [
                'request'  => [
                    'url'    => 'http://example.com/',
                    'method' => 'GET',
                ],
                'response' => [
                    'status' => 0,
                ],
            ],
        ], $serialized);
    }

    public function testCloseWithPsrResponse400(): void
    {
        $segment = $this->getNewSegment();
        $segment->closeWithPsrResponse(new Response(400));

        $serialized = \json_decode(\json_encode($segment), true);
        $this->assertIsNumeric($serialized['end_time']);
        unset($serialized['end_time']);

        $this->assertSame([
            'id'         => $segment->getId(),
            'name'       => 'HTTP Segment',
            'start_time' => $segment->getStartTime(),
            'error'      => true,
            'metadata'   => [
                'content' => '',
                'reason'  => 'Bad Request',
                'headers' => [],
            ],
            'namespace'  => 'remote',
            'http'       => [
                'request'  => [
                    'url'    => 'http://example.com/',
                    'method' => 'GET',
                ],
                'response' => [
                    'status' => 400,
                ],
            ],
        ], $serialized);
    }

    public function testCloseWithPsrResponse500(): void
    {
        $segment = $this->getNewSegment();
        $segment->closeWithPsrResponse(new Response(500));

        $serialized = \json_decode(\json_encode($segment), true);
        $this->assertIsNumeric($serialized['end_time']);
        unset($serialized['end_time']);

        $this->assertSame([
            'id'         => $segment->getId(),
            'name'       => 'HTTP Segment',
            'start_time' => $segment->getStartTime(),
            'fault'      => true,
            'metadata'   => [
                'content' => '',
                'reason'  => 'Internal Server Error',
                'headers' => [],
            ],
            'namespace'  => 'remote',
            'http'       => [
                'request'  => [
                    'url'    => 'http://example.com/',
                    'method' => 'GET',
                ],
                'response' => [
                    'status' => 500,
                ],
            ],
        ], $serialized);
    }

    public function testCloseWithPsrResponse(): void
    {
        $segment = $this->getNewSegment();
        $segment->closeWithPsrResponse(new Response(200));
        $serialized = \json_decode(\json_encode($segment), true);
        $this->assertArrayNotHasKey('error', $serialized);
        $this->assertArrayNotHasKey('fault', $serialized);

        $segment = $this->getNewSegment();
        $segment->closeWithPsrResponse(new Response(100));
        $serialized = \json_decode(\json_encode($segment), true);
        $this->assertArrayNotHasKey('error', $serialized);
        $this->assertArrayNotHasKey('fault', $serialized);
    }

    public function testRewindResponse(): void
    {
        $segment = $this->getNewSegment();
        $stream = Utils::streamFor('string data');
        $response = new Response(200, [], $stream);
        $segment->closeWithPsrResponse($response);

        $this->assertSame('string data', $response->getBody()->getContents());
    }

    private function getNewSegment(string $name = 'HTTP Segment'): HttpSegment
    {
        return new HttpSegment(
            name: $name,
            url: 'http://example.com/',
            method: 'GET',
        );
    }
}
