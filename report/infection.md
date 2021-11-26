Escaped mutants:
================

1) /home/ranpafin/php-xray/src/DynamoSegment.php:49    [M] UnwrapArrayFilter

--- Original
+++ New
@@ @@
     public function jsonSerialize() : array
     {
         $data = parent::jsonSerialize();
-        $data['aws'] = array_filter(['table_name' => $this->tableName ?? null, 'operation' => $this->operation ?? null, 'request_id' => $this->requestId ?? null, 'resource_names' => $this->resourceNames ?? null ?: null]);
+        $data['aws'] = ['table_name' => $this->tableName ?? null, 'operation' => $this->operation ?? null, 'request_id' => $this->requestId ?? null, 'resource_names' => $this->resourceNames ?? null ?: null];
         return $data;
     }
 }


2) /home/ranpafin/php-xray/src/DynamoSegment.php:50    [M] Coalesce

--- Original
+++ New
@@ @@
     public function jsonSerialize() : array
     {
         $data = parent::jsonSerialize();
-        $data['aws'] = array_filter(['table_name' => $this->tableName ?? null, 'operation' => $this->operation ?? null, 'request_id' => $this->requestId ?? null, 'resource_names' => $this->resourceNames ?? null ?: null]);
+        $data['aws'] = array_filter(['table_name' => null ?? $this->tableName, 'operation' => $this->operation ?? null, 'request_id' => $this->requestId ?? null, 'resource_names' => $this->resourceNames ?? null ?: null]);
         return $data;
     }
 }


3) /home/ranpafin/php-xray/src/DynamoSegment.php:51    [M] Coalesce

--- Original
+++ New
@@ @@
     public function jsonSerialize() : array
     {
         $data = parent::jsonSerialize();
-        $data['aws'] = array_filter(['table_name' => $this->tableName ?? null, 'operation' => $this->operation ?? null, 'request_id' => $this->requestId ?? null, 'resource_names' => $this->resourceNames ?? null ?: null]);
+        $data['aws'] = array_filter(['table_name' => $this->tableName ?? null, 'operation' => null ?? $this->operation, 'request_id' => $this->requestId ?? null, 'resource_names' => $this->resourceNames ?? null ?: null]);
         return $data;
     }
 }


4) /home/ranpafin/php-xray/src/DynamoSegment.php:52    [M] Coalesce

--- Original
+++ New
@@ @@
     public function jsonSerialize() : array
     {
         $data = parent::jsonSerialize();
-        $data['aws'] = array_filter(['table_name' => $this->tableName ?? null, 'operation' => $this->operation ?? null, 'request_id' => $this->requestId ?? null, 'resource_names' => $this->resourceNames ?? null ?: null]);
+        $data['aws'] = array_filter(['table_name' => $this->tableName ?? null, 'operation' => $this->operation ?? null, 'request_id' => null ?? $this->requestId, 'resource_names' => $this->resourceNames ?? null ?: null]);
         return $data;
     }
 }


5) /home/ranpafin/php-xray/src/DynamoSegment.php:53    [M] Coalesce

--- Original
+++ New
@@ @@
     public function jsonSerialize() : array
     {
         $data = parent::jsonSerialize();
-        $data['aws'] = array_filter(['table_name' => $this->tableName ?? null, 'operation' => $this->operation ?? null, 'request_id' => $this->requestId ?? null, 'resource_names' => $this->resourceNames ?? null ?: null]);
+        $data['aws'] = array_filter(['table_name' => $this->tableName ?? null, 'operation' => $this->operation ?? null, 'request_id' => $this->requestId ?? null, 'resource_names' => null ?? $this->resourceNames ?: null]);
         return $data;
     }
 }


6) /home/ranpafin/php-xray/src/HttpSegment.php:18    [M] UnwrapArrayFilter

--- Original
+++ New
@@ @@
     {
         $data = parent::jsonSerialize();
         $data['http'] = $this->serialiseHttpData();
-        return array_filter($data);
+        return $data;
     }
 }


7) /home/ranpafin/php-xray/src/Segment.php:54    [M] CastBool

--- Original
+++ New
@@ @@
             $this->setTraceId($variables['Root']);
         }
         if (isset($variables['Sampled'])) {
-            $this->setSampled((bool) $variables['Sampled'] ?? false);
+            $this->setSampled($variables['Sampled'] ?? false);
         }
         if (isset($variables['Parent'])) {
             $this->setParentId($variables['Parent'] ?? null);


8) /home/ranpafin/php-xray/src/Segment.php:54    [M] FalseValue

--- Original
+++ New
@@ @@
             $this->setTraceId($variables['Root']);
         }
         if (isset($variables['Sampled'])) {
-            $this->setSampled((bool) $variables['Sampled'] ?? false);
+            $this->setSampled((bool) $variables['Sampled'] ?? true);
         }
         if (isset($variables['Parent'])) {
             $this->setParentId($variables['Parent'] ?? null);


9) /home/ranpafin/php-xray/src/Segment.php:57    [M] Coalesce

--- Original
+++ New
@@ @@
             $this->setSampled((bool) $variables['Sampled'] ?? false);
         }
         if (isset($variables['Parent'])) {
-            $this->setParentId($variables['Parent'] ?? null);
+            $this->setParentId(null ?? $variables['Parent']);
         }
         return $this;
     }


10) /home/ranpafin/php-xray/src/Trace.php:64    [M] DecrementInteger

--- Original
+++ New
@@ @@
             $this->generateTraceId();
         }
         if (!$this->isSampled()) {
-            $this->sampled = random_int(0, 99) < $samplePercentage;
+            $this->sampled = random_int(-1, 99) < $samplePercentage;
         }
         return $this;
     }


11) /home/ranpafin/php-xray/src/Trace.php:64    [M] IncrementInteger

--- Original
+++ New
@@ @@
             $this->generateTraceId();
         }
         if (!$this->isSampled()) {
-            $this->sampled = random_int(0, 99) < $samplePercentage;
+            $this->sampled = random_int(1, 99) < $samplePercentage;
         }
         return $this;
     }


12) /home/ranpafin/php-xray/src/Trace.php:64    [M] DecrementInteger

--- Original
+++ New
@@ @@
             $this->generateTraceId();
         }
         if (!$this->isSampled()) {
-            $this->sampled = random_int(0, 99) < $samplePercentage;
+            $this->sampled = random_int(0, 98) < $samplePercentage;
         }
         return $this;
     }


13) /home/ranpafin/php-xray/src/Trace.php:64    [M] IncrementInteger

--- Original
+++ New
@@ @@
             $this->generateTraceId();
         }
         if (!$this->isSampled()) {
-            $this->sampled = random_int(0, 99) < $samplePercentage;
+            $this->sampled = random_int(0, 100) < $samplePercentage;
         }
         return $this;
     }


14) /home/ranpafin/php-xray/src/Trace.php:64    [M] LessThan

--- Original
+++ New
@@ @@
             $this->generateTraceId();
         }
         if (!$this->isSampled()) {
-            $this->sampled = random_int(0, 99) < $samplePercentage;
+            $this->sampled = random_int(0, 99) <= $samplePercentage;
         }
         return $this;
     }


15) /home/ranpafin/php-xray/src/Trace.php:64    [M] LessThanNegotiation

--- Original
+++ New
@@ @@
             $this->generateTraceId();
         }
         if (!$this->isSampled()) {
-            $this->sampled = random_int(0, 99) < $samplePercentage;
+            $this->sampled = random_int(0, 99) >= $samplePercentage;
         }
         return $this;
     }


Timed Out mutants:
==================

1) /home/ranpafin/php-xray/src/Submission/DaemonSegmentSubmitter.php:28    [M] DecrementInteger

--- Original
+++ New
@@ @@
     private int $port;
     /** @var Socket */
     private $socket;
-    public function __construct(string $host = '127.0.0.1', int $port = 2000)
+    public function __construct(string $host = '127.0.0.1', int $port = 1999)
     {
         if (isset($_SERVER['AWS_XRAY_DAEMON_ADDRESS'])) {
             [$host, $port] = explode(":", $_SERVER['AWS_XRAY_DAEMON_ADDRESS']);


Skipped mutants:
================

Not Covered mutants:
====================

1) /home/ranpafin/php-xray/src/Submission/DaemonSegmentSubmitter.php:38    [M] Concat

--- Original
+++ New
@@ @@
         $this->host = $_SERVER['_AWS_XRAY_DAEMON_ADDRESS'] ?? $host;
         $this->port = (int) ($_SERVER['_AWS_XRAY_DAEMON_PORT'] ?? $port);
         if (!($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
-            throw new Error('Can\'t create socket: ' . socket_last_error());
+            throw new Error(socket_last_error() . 'Can\'t create socket: ');
         }
         $this->socket = $socket;
     }


2) /home/ranpafin/php-xray/src/Submission/DaemonSegmentSubmitter.php:38    [M] ConcatOperandRemoval

--- Original
+++ New
@@ @@
         $this->host = $_SERVER['_AWS_XRAY_DAEMON_ADDRESS'] ?? $host;
         $this->port = (int) ($_SERVER['_AWS_XRAY_DAEMON_PORT'] ?? $port);
         if (!($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
-            throw new Error('Can\'t create socket: ' . socket_last_error());
+            throw new Error(socket_last_error());
         }
         $this->socket = $socket;
     }


3) /home/ranpafin/php-xray/src/Submission/DaemonSegmentSubmitter.php:38    [M] ConcatOperandRemoval

--- Original
+++ New
@@ @@
         $this->host = $_SERVER['_AWS_XRAY_DAEMON_ADDRESS'] ?? $host;
         $this->port = (int) ($_SERVER['_AWS_XRAY_DAEMON_PORT'] ?? $port);
         if (!($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
-            throw new Error('Can\'t create socket: ' . socket_last_error());
+            throw new Error('Can\'t create socket: ');
         }
         $this->socket = $socket;
     }


4) /home/ranpafin/php-xray/src/Submission/DaemonSegmentSubmitter.php:38    [M] Throw_

--- Original
+++ New
@@ @@
         $this->host = $_SERVER['_AWS_XRAY_DAEMON_ADDRESS'] ?? $host;
         $this->port = (int) ($_SERVER['_AWS_XRAY_DAEMON_PORT'] ?? $port);
         if (!($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
-            throw new Error('Can\'t create socket: ' . socket_last_error());
+            new Error('Can\'t create socket: ' . socket_last_error());
         }
         $this->socket = $socket;
     }
