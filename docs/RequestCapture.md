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

## DatabaseWriter

```php
<?php

/**
 * @var string $dsn
 * @var string $user
 * @var string $password
 * @var string $table 
 */
$writer = new \fk\helpers\debug\DatabaseWriter($dsn, $user, $password, $table, function ($error) {
    // Log the $error message
    \Illuminate\Support\Facades\Log::error($error);
});
// Record the route
$capture = new fk\helpers\debug\Capture($writer, true);
$capture->write(['route' => $_SERVER['REQUEST_URI']]);

```