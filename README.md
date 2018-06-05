# Hawk PHP

PHP errors Catcher for [Hawk.so](https://hawk.so).

![](https://capella.pics/c0fe5eeb-027d-427a-9e0d-b2e1dcaaf303)

## Usage

1. [Register](https://hawk.so/join) an account and get an Integration Token.

2. Install module

Use [composer](https://getcomposer.org) to install Catcher

```bash
$ composer require codex-team/hawk.php
```

3. Use as a [standalone catcher](#standalone-error-catcher) or use with [Monolog](#monolog-support).

## Standalone error catcher

Create an instance with Token at the entry point of your project.

```php
\Hawk\HawkCatcher::instance('abcd1234-1234-abcd-1234-123456abcdef');
```

### Enable handlers

By default Hawk will catch everything. You can run function with no params.

```php
\Hawk\HawkCatcher::enableHandlers();
```

It's similar to

```php
\Hawk\HawkCatcher::enableHandlers(
    TRUE,       // exceptions
    TRUE,       // errors
    TRUE        // shutdown
);
```

You can pass types of errors you want to track:

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

### Catch handled exceptions

You can catch exceptions manually with `catchException` method.

```php
try {
    throw new Exception("Error Processing Request", 1);
} catch (Exception $e) {
    \Hawk\HawkCatcher::catchException($e);
}
```

## Monolog support

Add a handler to the Monolog. It will catch errors/exception and ignore general logs.

```php
$logger = new \Monolog\Logger('hawk-test');

$HAWK_TOKEN = 'abcd1234-1234-abcd-1234-123456abcdef';
$logger->pushHandler(new \Hawk\Monolog\Handler($HAWK_TOKEN, \Monolog\Logger::DEBUG));
```

Now you can use logger's functions to process handled exceptions. Pass it to context array in 'exception' field.

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

It catches all errors and sends them to Hawk.

Throwing unhandled error example (without try-catch construction):

```php
/** Fatal Error: "Just an error in a high quality code" */
throw new Error('Just an error in a high quality code', E_USER_ERROR);
```

## Issues and improvements

Feel free to ask questions or improve the project.

## Links

Repository: https://github.com/codex-team/hawk.php

Report a bug: https://github.com/codex-team/hawk.php/issues

Composer Package: https://packagist.org/packages/codex-team/hawk.php

CodeX Team: https://ifmo.su

## License

MIT
