# Hawk PHP

PHP errors Catcher for [Hawk.so](https://hawk.so).

![](https://capella.pics/c0fe5eeb-027d-427a-9e0d-b2e1dcaaf303)

## Setup

1. [Register](https://hawk.so/join) an account and get Integration Token.

2. Install SDK via [composer](https://getcomposer.org) to install Catcher

```bash
$ composer require codex-team/hawk.php
```

### Configuration

```php
\Hawk\Catcher::init([
    'integrationToken' => 'your integration token'
]);
```

### Send events and exceptions manually

Use `sendException` method to send any caught exception

```php
try {
    throw new Exception("Error Processing Request", 1);
} catch (Exception $e) {
    \Hawk\Catcher::get()->sendException($e);
}
```

Use `sendEvent` method to send any data (logs, notices or something else)

```php
\Hawk\Catcher::get()->sendMessage('your message', [
    ... // Context
]);
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
