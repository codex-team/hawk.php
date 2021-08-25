# Hawk PHP

PHP errors Catcher for [Hawk.so](https://hawk.so).

![](https://capella.pics/image/4c6e5fee-da7e-4bc5-a898-f19d12acb005)

## Setup

1. [Register](https://garage.hawk.so/sign-up) an account, create a Project and get an Integration Token.

2. Install SDK via [composer](https://getcomposer.org) to install the Catcher

Catcher provides support for PHP 7.2 or later

```bash
$ composer require codex-team/hawk.php
```

### Configuration

```php
\Hawk\Catcher::init([
    'integrationToken' => 'your integration token'
]);
```

After initialization you can set `user` or `context` for any event that will be send to Hawk

```php
\Hawk\Catcher::get()
    ->setUser([
        'name' => 'user name',
        'photo' => 'user photo',
    ])
    ->setContext([
        ...
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

### Filtering sensitive information

Use the `beforeSend` hook to filter any data you don't want to send to Hawk. Use setters to clear any property.

```php
\Hawk\Catcher::init([
    // ...
    'beforeSend' => function (\Hawk\EventPayload $eventPayload) {
        $user = $eventPayload->getUser();
        
        if (!empty($user['email'])){
            unset($user['email']);
        
            $eventPayload->setUser($user);
        }

        return $eventPayload;
    }
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
