# Hawk PHP

PHP errors Catcher module for [Hawk.so](https://hawk.so).

![](https://capella.pics/c0fe5eeb-027d-427a-9e0d-b2e1dcaaf303)

## Usage

1. [Register](https://hawk.so/join) an account and get a project token.

2. Install module

Use [composer](https://getcomposer.org) to install Catcher

```bash
$ composer require codex-team/hawk.php
```

3. Use as [standalone catcher](standalone-error-catcher), use with [Monolog](monolog-support).

## Standalone error catcher

Create an instance with token to the entry point of your project (usually `index.php` or `bootstrap.php`).

```php
\Hawk\HawkCatcher::instance('abcd1234-1234-abcd-1234-123456abcdef');
```

You can store token in the environment file

```php
\Hawk\HawkCatcher::instance($_SERVER['HAWK_TOKEN']);
```

### Custom Hawk server

If you want to use custom Hawk server then pass a url to this catcher.

```php
\Hawk\HawkCatcher::instance(
    'abcd1234-1234-abcd-1234-123456abcdef',
    'http://myownhawk.com/catcher/php'
);
```

### Enable handlers

If you want to catch error automatically run the following command with boolean params to enable some handlers.

```php
\Hawk\HawkCatcher::enableHandlers(
    TRUE,       // exceptions
    TRUE,       // errors
    TRUE        // shutdown
);
```

By default Hawk will catch everything. You can run function with no params.

```php
\Hawk\HawkCatcher::enableHandlers();
```

You can pass types of errors to be caught

```php
// Catch run-time warnings or compile-time parse errors
\Hawk\HawkCatcher::enableHandlers(
    TRUE,                // exceptions
    E_WARNING | E_PARSE, // errors
    TRUE                 // shutdown
);
```

```php
// Catch everything except notices
\Hawk\HawkCatcher::enableHandlers(
    TRUE,              // exceptions
    E_ALL & ~E_NOTICE, // errors
    TRUE               // shutdown
);
```

### Catch exceptions

You can catch exceptions by yourself without enabling handlers.

```php
try {
    throw new Exception("Error Processing Request", 1);
} catch (Exception $e) {
    \Hawk\HawkCatcher::catchException($e);
}
```

## Monolog support

If you want to use Hawk Catcher with Monolog then simply add a handler.
It will catch provided errors and exception. Common string logs will be ignored.

```php
$logger = new \Monolog\Logger('hawk-test');

$HAWK_TOKEN = 'abcd1234-1234-abcd-1234-123456abcdef';
$logger->pushHandler(new \Hawk\Monolog\Handler($HAWK_TOKEN), \Monolog\Logger::DEBUG);

/**
 * If you want to use custom Hawk server (local dev as example) then pass
 * catcher's url as second param to \Hawk\Monolog\Handler constructor.
 *
 * $logger->pushHandler(
 *     new \Hawk\Monolog\Handler(
 *         'abcd1234-1234-abcd-1234-123456abcdef',
 *         'localhost:3000/catcher/php'
 *     ),
 *     \Monolog\Logger::DEBUG
 * );
 */
```

Now you can use logger's functions to process handled exceptions.

```php
try {
   throw new Exception('Something went wrong');
} catch (\Exception $e) {
   $logger->error($e->getMessage(), ['exception' => $e]);
}
```

### Default error catcher

Register Monolog's handler as catcher.

```php
/** Set monolog as default error handler */
$handler = \Monolog\ErrorHandler::register($logger);
```

It will catch all errors and send them to Hawk.

Example of throwing unhandled error (without try-catch construction):

```php
/** Fatal Error: "Just an error in a high quality code" */
throw new Error('Just an error in a high quality code', E_USER_ERROR);
```

## Issues and improvements

Feed free to ask questions or improve the project.

## Links

Repository: https://github.com/codex-team/hawk.php

Report a bug: https://github.com/codex-team/hawk.php/issues

Composer Package: https://packagist.org/packages/codex-team/hawk.php

CodeX Team: https://ifmo.su

## License

MIT
