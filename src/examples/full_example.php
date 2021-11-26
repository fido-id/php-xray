<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Fido\PHPXray\Cause;
use Fido\PHPXray\DynamoSegment;
use Fido\PHPXray\HttpSegment;
use Fido\PHPXray\Segment;
use Fido\PHPXray\Submission\DaemonSegmentSubmitter;
use Fido\PHPXray\examples\Thrower;
use Fido\PHPXray\Trace;

$client = new \GuzzleHttp\Client();

$trace = Trace::getInstance()->setName(\uniqid("a_trace_name_"))->begin();

$main = (new Segment())
    ->begin()
    ->setName(\uniqid("subsegment_main_"));

$httpSegment = HttpSegment::open(
    name: \uniqid("http_segment_post_500_"),
    url: 'ifconfig.me/ua',
    method: 'GET'
);

$genericSubSegment =
    (new Segment())
        ->begin()
        ->setName(\uniqid("generic_sub_segment_"));

$dynamoDBSubSegment =
    (new DynamoSegment())
        ->begin()
        ->setName(\uniqid("dynamo_db_sub_segment_"))
        ->setOperation('query')
        ->setRequestId('a_request_id')
        ->setTableName(\uniqid("a_table_name_"));

try {

    try {
        new Thrower('42', 42, new \stdClass(), ['42',42]);
    } catch (\Throwable $e) {
        throw new \Exception('wrapper', 0, $e);
    }

} catch (\Throwable $t) {
    $exception = $t;

}

$genericSubSegmentWithException =
    (new Segment())
        ->begin()
        ->setName(\uniqid("generic_sub_segment_exception_"))
        ->setError(true)
        ->setCause(Cause::fromThrowable($exception));


$main->addSubsegment($genericSubSegment->end());
$main->addSubsegment($httpSegment->closeWithPsrResponse($client->get('ifconfig.me/ua')));
$main->addSubsegment($dynamoDBSubSegment->end());
$main->addSubsegment($genericSubSegmentWithException->end());
$trace->addSubsegment($main->end());

$trace
    ->end()
    ->submit(new DaemonSegmentSubmitter());

print_r("https://eu-west-1.console.aws.amazon.com/xray/home?region=eu-west-1#/traces/" . $trace->getTraceId() . "\n");
