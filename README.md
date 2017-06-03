# DebugRequestCapture

This is used to capture request for api

```php
<?php

use fk\helpers\debug\Capture;

(new Capture(__DIR__ . '/../storage/logs/request_capture.log'))
    ->capture(function () {
        // return response to log
    });
```