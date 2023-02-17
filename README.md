# fido-id/php-xray

[![Build Status](https://github.com/fido-id/php-xray/actions/workflows/main.yaml/badge.svg)](https://github.com/fido-id/php-xray/actions/workflows/main.yaml)
[![PHP Version](https://img.shields.io/badge/php->=8.0-blue)](https://www.php.net/releases/8.0/en.php)
[![Coverage Status][Master coverage image]](https://app.codecov.io/gh/fido-id/php-xray)

[Master coverage image]: https://codecov.io/gh/fido-id/php-xray/branch/master/graph/badge.svg?token=YSPMGJVP77

An instrumentation library for [AWS X-Ray](https://docs.aws.amazon.com/xray/latest/devguide/aws-xray.html) for PHP 8.x
[LICENSE](LICENSE.md)
[CHANGELOG](CHANGELOG-0.x.md)

## Installation

To use this package, use [Composer](https://getcomposer.org/):

* From CLI: `composer require fido-id/php-xray`
* Or, directly in your `composer.json`:

```json
{
  "require": {
    "fido/php-xray": "^0.1.0",
  }
}
```

## Usage

Start a new trace, create a new segment, add any desired subsegment, close it and submit it.

eg.

```php
$client = new \GuzzleHttp\Client();

$trace = new Trace(name: 'a_new_trace');

$httpSegment = new HttpSegment(
    name: \uniqid("http_segment_post_500_"),
    url: 'ifconfig.me/ua',
    method: 'GET'
);

$httpSegment
    ->closeWithPsrResponse($client->get('ifconfig.me/ua'))

$trace->addSubsegment($httpSegment);

$trace->end()->submit(new DaemonSegmentSubmitter());
```

> Closing segments is mandatory before submitting, closing a parent segment will automatically close any child segment.

### Available built-in segments

- `Segment`: Default simple segment extended by any other segment in this list.
- `RemoteSegment`: A segment with the `$traced` boolean property, extended by the other segment.
- `DynamoSegment`: A segment thought for Dynamo operations with `$tableName`, `$operation` and `$requestId` dynamo related properties.
- `HttpSegment`: A segment thought for operation over HTTP with `$url`, `$method` and `$responseCode`related properties. It also features a `closeWithPsrResponse` helper method
  which allows to populate and close the segment with an object implementing the `Psr\Http\Message\ResponseInterface` interface.
- `SqlSegment`: A segment thought for SQL operations with `$query` property and `$url`,`$preparation`,`$databaseType`,`$databaseVersion`,`$driverVersion` and `$user` optional
  properties.

> You may want to extend one of the above class to instrument custom segments for [metadata](https://docs.aws.amazon.com/xray/latest/devguide/xray-api-segmentdocuments.html#api-segmentdocuments-metadata), [annotation](https://docs.aws.amazon.com/xray/latest/devguide/xray-api-segmentdocuments.html#api-segmentdocuments-annotations) and [aws data](https://docs.aws.amazon.com/xray/latest/devguide/xray-api-segmentdocuments.html#api-segmentdocuments-aws) custom handling (Remember to extend `__construct` and `jsonSerialize` methods accordingly).

### Fault and error handling

Any segment has the `$fault` and `$error` boolean properties that can be used accordingly, also you can set the cause with a `Cause` object.

eg.

```php
$trace = new Trace(name: 'a_new_trace');
    
$pdo = new \PDO('a_totally_valid_dsn');
$query = "SELECT * FROM table_name";

$sqlSegment = new SqlSegment(
    name: \uniqid("subsegment_sql_"),
    query: $query
);

try {
    $pdo->exec($query);
} catch (\Throwable $exception) {
    $sqlSegment->setError(true);
    $sqlSegment->setCause(Cause::fromThrowable($exception));
}

$trace->addSubsegment($sqlSegment);

$trace->end()->submit(new DaemonSegmentSubmitter());

```

## How to test the software

You can run the library test suite with [PHPUnit](https://phpunit.de/) by running `composer test` script, you can also run `composer mutation` script for mutation testing report.

## Known issues

- Segments currently support only `Cause` object but not `exception ID`.
- Submitting open segment is not supported yet.

## Getting help

If you have questions, concerns, bug reports, etc, please file an issue in this repository's Issue Tracker.

## Getting involved

Feedbacks and pull requests are very welcome, more on _how_ to contribute on [CONTRIBUTING](CONTRIBUTING.md).

----

## Credits and references

This library is inspired by [patrickkerrigan/php-xray](https://github.com/patrickkerrigan/php-xray), initially we thought to fork it but ended up re-writing it from scratch using PHP8 named constructors instead of fluent approach which allow us to have always valid entities instantiated.
