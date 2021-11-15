<?php

namespace Fido\PHPXray;

interface DictionaryInterface
{


    // MAIN [TYPE_AREA_VALUE]

    /**  aws for AWS SDK calls; remote for other downstream calls. */
    public const SEGMENT_KEY_MAIN_NAMESPACE = 'namespace';

    /** A 64-bit identifier for the segment, unique among segments in the same trace, in 16 hexadecimal digits. */
    public const SEGMENT_KEY_MAIN_ID = 'id';

    /** aws object with information about the AWS resource on which your application served the request.*/
    public const SEGMENT_KEY_MAIN_AWS = 'aws';

    /** A subsegment ID you specify if the request originated from an instrumented application.
     * The X-Ray SDK adds the parent subsegment ID to the tracing header for downstream HTTP calls.
     * In the case of nested subsegments, a subsegment can have a segment or a subsegment as its parent.
     */
    public const SEGMENT_KEY_MAIN_PARENT_ID = 'parent_id';

    /** A unique identifier that connects all segments and subsegments originating from a single client request.
     * Trace ID Format:
     * - A trace_id consists of three numbers separated by hyphens. For example, 1-58406520-a006649127e371903a2de979. This includes:
     * - The version number, that is, 1.
     * - The time of the original request, in Unix epoch time, in 8 hexadecimal digits.
     * - For example, 10:00AM December 1st, 2016 PST in epoch time is 1480615200 seconds, or 58406520 in hexadecimal digits.
     * - A 96-bit identifier for the trace, globally unique, in 24 hexadecimal digits.
     */
    public const SEGMENT_KEY_MAIN_TRACE_ID = 'trace_id';

    /** The logical name of the service that handled the request, up to 200 characters. For example, your application's name or domain name. Names can contain Unicode letters,
     * numbers, and whitespace, and the following symbols: _, ., :, /, %, &, #, =, +, \, -, @
     */
    public const SEGMENT_KEY_MAIN_NAME = 'name';

    /** number that is the time the segment was created, in floating point seconds in epoch time. For example, 1480615200.010 or 1.480615200010E9.
     * Use as many decimal places as you need. Microsecond resolution is recommended when available.
     */
    public const SEGMENT_KEY_MAIN_START_TIME = 'start_time';

    /** number that is the time the segment was closed. For example, 1480615200.090 or 1.480615200090E9. Specify either an end_time or in_progress. */
    public const SEGMENT_KEY_MAIN_END_TIME = 'end_time';

    /** boolean, set to true instead of specifying an end_time to record that a segment is started, but is not complete.
     * Send an in-progress segment when your application receives a request that will take a long time to serve, to trace the request receipt.
     * When the response is sent, send the complete segment to overwrite the in-progress segment. Only send one complete segment, and one or zero in-progress segments, per request.
     */
    public const SEGMENT_KEY_MAIN_IN_PROGRESS = 'in_progress';

    /** array of subsegment objects */
    public const SEGMENT_KEY_MAIN_SUBSEGMENTS = 'subsegments';

    /** Required only if sending a subsegment separately. */
    public const SEGMENT_KEY_MAIN_TYPE = 'type';

    /** A string that identifies the user who sent the request. */
    public const SEGMENT_KEY_MAIN_USER = 'user';

    /** The type of AWS resource running your application.
     * Supported Values:
     * - AWS::EC2::Instance – An Amazon EC2 instance.
     * - AWS::ECS::Container – An Amazon ECS container.
     * - AWS::ElasticBeanstalk::Environment – An Elastic Beanstalk environment.
     * When multiple values are applicable to your application, use the one that is most specific.
     * For example, a Multicontainer Docker Elastic Beanstalk environment runs your application on an Amazon ECS container, which in turn runs on an Amazon EC2 instance.
     * In this case you would set the origin to AWS::ElasticBeanstalk::Environment as the environment is the parent of the other two resources.
     */
    public const SEGMENT_KEY_MAIN_ORIGIN = 'origin';

    /** error, throttle, fault, and cause – error fields that indicate an error occurred and that include information about the exception that caused the error. */
    public const SEGMENT_KEY_MAIN_ERROR = 'error';

    /** error, throttle, fault, and cause – error fields that indicate an error occurred and that include information about the exception that caused the error. */
    public const SEGMENT_KEY_MAIN_THROTTLE = 'throttle';

    /** error, throttle, fault, and cause – error fields that indicate an error occurred and that include information about the exception that caused the error. */
    public const SEGMENT_KEY_MAIN_FAULT = 'fault';

    /** annotations object with key-value pairs that you want X-Ray to index for search. */
    public const SEGMENT_KEY_MAIN_ANNOTATIONS = 'annotations';

    /**  metadata object with any additional data that you want to store in the segment. */
    public const SEGMENT_KEY_MAIN_METADATA = 'metadata';

    /** An object with information about your application. ["version" => string] */
    public const SEGMENT_KEY_MAIN_SERVICE = 'service';

    /** http objects with information about the original HTTP request. */
    public const SEGMENT_KEY_MAIN_HTTP = 'http';

    /** subsegments for queries that your application makes to an SQL database. */
    public const SEGMENT_KEY_MAIN_SQL = 'sql';


    // HTTP

    // HTTP -> REQUEST [TYPE_AREA_VALUE]
    /** request – Information about a request */
    public const SEGMENT_KEY_HTTP_REQUEST = 'request';

    /** Information about a response. */
    public const SEGMENT_KEY_HTTP_RESPONSE = 'response';

    /** url – The full URL of the request, compiled from the protocol, hostname, and path of the request. */
    public const SEGMENT_KEY_HTTP_REQUEST_URL = 'url';

    /** method – The request method. For example, GET. */
    public const SEGMENT_KEY_HTTP_REQUEST_METHOD = 'method';

    /** The IP address of the requester. Can be retrieved from the IP packet's Source Address or, for forwarded requests, from an X-Forwarded-For header. */
    public const SEGMENT_KEY_HTTP_REQUEST_CLIENT_IP = 'client_ip';

    /** The user agent string from the requesters' client. */
    public const SEGMENT_KEY_HTTP_REQUEST_USER_AGENT = 'user_agent';

    /** (segments only) boolean indicating that the client_ip was read from an X-Forwarded-For header and is not reliable as it could have been forged. */
    public const SEGMENT_KEY_HTTP_REQUEST_X_FORWARDED_FOR = 'x_forwarded_for';

    /** (subsegments only) boolean indicating that the downstream call is to another traced service.
     * If this field is set to true, X-Ray considers the trace to be broken until the downstream service
     * uploads a segment with a parent_id that matches the id of the subsegment that contains this block.
     */
    public const SEGMENT_KEY_HTTP_REQUEST_TRACED = 'traced';

    // HTTP -> RESPONSE

    /** number indicating the HTTP status of the response. */
    public const SEGMENT_KEY_HTTP_RESPONSE_STATUS = 'status';

    /** content_length – number indicating the length of the response body in bytes.*/
    public const SEGMENT_KEY_RESPONSE_CONTENT_LENGTH = 'content_length';

    // SQL

    /** For SQL Server or other database connections that don't use URL connection strings, record the connection string, excluding passwords.  */
    public const SEGMENT_KEY_SQL_CONNECTION_STRING = 'preparation';

    /** For a database connection that uses a URL connection string, record the URL, excluding passwords.  */
    public const SEGMENT_KEY_SQL_URL = 'url';

    /** call if the query used a PreparedCall; statement if the query used a PreparedStatement.  */
    public const SEGMENT_KEY_SQL_PREPARATION = 'preparation';

    /** The name of the database engine. */
    public const SEGMENT_KEY_SQL_DATABASE_TYPE = 'database_type';

    /**  The version number of the database engine. */
    public const SEGMENT_KEY_SQL_DATABASE_VERSION = 'database_version';

    /** The name and version number of the database engine driver that your application uses. */
    public const SEGMENT_KEY_SQL_DRIVER_VERSION = 'driver_version';

    /** The database username.  */
    public const SEGMENT_KEY_SQL_USER = 'user';

    /** The database query, with any user provided values removed or replaced by a placeholder. */
    public const SEGMENT_KEY_SQL_SANITIZED_QUERY = 'sanitized_query';


    // AWS

    /** For operations on a DynamoDB table, the name of the table. */
    public const SEGMENT_KEY_AWS_TABLE_NAME = 'table_name';

    /**  If your application accesses resources in a different account, or sends segments to a different account, record the ID of the account that owns the AWS resource that your application accessed.*/
    public const SEGMENT_KEY_AWS_ACCOUNT_ID = 'account_id';

    /** If the resource is in a region different from your application, record the region. For example, us-west-2. */
    public const SEGMENT_KEY_AWS_REGION = 'region';

    /** The name of the API action invoked against an AWS service or resource.  */
    public const SEGMENT_KEY_AWS_OPERATION = 'operation';

    /** For operations on an Amazon SQS queue, the queue's URL. */
    public const SEGMENT_KEY_AWS_QUEUE_URL = 'queue_url';

    /** Unique identifier for the request. */
    public const SEGMENT_KEY_AWS_REQUEST_ID = 'request_id';

    /** array of string naming of the resources involved in the operation */
    public const SEGMENT_KEY_AWS_RESOURCE_NAMES = 'resource_names';


    // DAEMON
    public const DAEMON_ADDRESS_AND_PORT = 'AWS_XRAY_DAEMON_ADDRESS';
    public const DAEMON_ADDRESS = '_AWS_XRAY_DAEMON_ADDRESS';
    public const DAEMON_PORT = '_AWS_XRAY_DAEMON_PORT';


    // ENUMS

    /** ["namespace": "remote"|"aws"]  aws for AWS SDK calls; remote for other downstream calls. */
    public const SEGMENT_ENUM_NAMESPACE_REMOTE = 'remote';
    /** ["namespace": "remote"|"aws"]  aws for AWS SDK calls; remote for other downstream calls. */
    public const SEGMENT_ENUM_NAMESPACE_AWS = 'aws';

    /** Required only if sending a subsegment separately. ["type"=>"subsegment"], only value allowed is 'subsegment'...meh */
    public const SEGMENT_ENUM_MAIN_TYPE_SUBSEGMENT = 'subsegment';
}
