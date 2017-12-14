# Request Capture

Place in entry script, usually a `index.php` file.



## Usage

```php
<?php

use fk\helpers\debug\{
    Capture, FileWriter
};

/**
 * @var string $filename To log file to write in
 * @var array $startingLines The lines to specify the start of current writing session
 */
$writer = new FileWriter($filename, $startingLines);

/**@var bool $debug False not to capture anything */
Capture::singleton($writer, $debug);

```

And then every thing is ready to go.

## Available writers

## FileWriter

## DBWriter