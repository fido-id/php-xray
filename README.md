# fido-id/php-xray

[![Build Status](https://github.com/fido-id/php-xray/actions/workflows/main.yaml/badge.svg)](https://github.com/fido-id/php-xray/actions/workflows/main.yaml)
![PHP Version](https://img.shields.io/badge/php->=8.0-blue)
[![Coverage Status][Master coverage image]][Master coverage link]

An instrumentation library for AWS X-Ray for PHP 8.x

## Installation:

To use this package, use Composer:

* From CLI: `composer require fido-id/php-xray`
* Or, directly in your `composer.json`:

```json
{
  "require": {
    "fido-id/php-xray": "^0.1.0"
  }
}
```

Start a new trace, submit an http segment, closed with a PSR response.

```php
$client = new \GuzzleHttp\Client();

$trace = Trace::getInstance()
    ->setName('a_new_trace')
    ->begin();

$main = (new Segment())
    ->begin()
    ->setName(\uniqid("subsegment_main_"));

$httpSegment = HttpSegment::open(
    name: \uniqid("http_segment_post_500_"),
    url: 'ifconfig.me/ua',
    method: 'GET'
);

$httpSegment
    ->closeWithPsrResponse($client->get('ifconfig.me/ua'))

$trace->addSubsegment($httpSegment);

$trace->end()->submit(new DaemonSegmentSubmitter());
```

[Master coverage image]: https://codecov.io/gh/fido-id/php-xray/branch/master/graph/badge.svg?token=YSPMGJVP77
[Master coverage link]: https://app.codecov.io/gh/fido-id/php-xray
