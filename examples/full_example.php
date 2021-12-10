<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Thrower.php';

use Fido\PHPXray\Cause;
use Fido\PHPXray\DynamoSegment;
use Fido\PHPXray\HttpSegment;
use Fido\PHPXray\Segment;
use Fido\PHPXray\Submission\DaemonSegmentSubmitter;
use Fido\PHPXray\Trace;
use Fido\PHPXray\TraceSingletonAccessor;
use GuzzleHttp\Client;

$client = new Client();

$trace = new Trace(\uniqid("a_trace_name_"));
TraceSingletonAccessor::setInstance($trace);

$main = new Segment(\uniqid("segment_main_"));

$httpSegment = new HttpSegment(
    name: \uniqid("http_segment_post_500_"),
    url: 'ifconfig.me/ua',
    method: 'GET'
);
$httpSegment->closeWithPsrResponse($client->get('ifconfig.me/ua'));


$genericSubSegment = new Segment(\uniqid("generic_sub_segment_"));
$dynamoDBSubSegment = new DynamoSegment(
    name: \uniqid("dynamo_db_sub_segment_"),
    tableName: "a_table_name",
    operation: 'query',
    requestId: 'a_request_id'
);

try {

    try {
        new Thrower('42', 42, new \stdClass(), ['42',42]);
    } catch (\Throwable $e) {
        throw new \Exception('wrapper', 0, $e);
    }

} catch (\Throwable $t) {
    $exception = $t;

}


$genericSubSegmentWithException = new Segment(\uniqid("segment_main_"));
$genericSubSegmentWithException->setError(true);
$genericSubSegmentWithException->setCause(Cause::fromThrowable($exception));


$main->addSubsegment($genericSubSegment);
$main->addSubsegment($httpSegment);
$main->addSubsegment($dynamoDBSubSegment);
$main->addSubsegment($genericSubSegmentWithException);
TraceSingletonAccessor::getInstance()->addSubsegment($main);

TraceSingletonAccessor::getInstance()->end();
(new DaemonSegmentSubmitter())->submitSegment(TraceSingletonAccessor::getInstance());

print_r("https://eu-west-1.console.aws.amazon.com/xray/home?region=eu-west-1#/traces/" . $trace->getTraceId() . "\n");
