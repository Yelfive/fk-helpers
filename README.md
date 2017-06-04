# DebugRequestCapture

This is used to capture request for api

```php
<?php

use fk\helpers\debug\Capture;
use fk\helpers\debug\FileWriter;

$filename = __DIR__ . '/capture.log';
$writer = new FileWriter($filename);
$capture = new Capture($writer);

// Capture::softAdd will buffer data
// and write when the next write performs
$capture->softAdd([
    'field' => 'mixed value'
]);

// all redefined data and buffers will
// immediately written
$capture->capture();

// Writes data and buffer
$capture->add([
    'field' => 'mixed value'
]);

```

# fk\helpers\debug\FileWriter

__construct
---


# fk\helpers\debug\DBWriter
