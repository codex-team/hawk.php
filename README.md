# hawk.php

PHP errors Catcher module for [Hawk.so](https://hawk.so)

## Usage

[Register](https://hawk.so/join) an account and get a project token.

### Install module

Use [composer](https://getcomposer.org) to install Catcher

```bash
$ composer require codex-team/hawk.php
$ composer install
```

#### Download and require php file

You can download this repository and require `Hawk.php` file in your project.

```php
require './hawk.php/src/Hawk.php';
```

### Init HawkCatcher

Create an instance and pass token in the entry point of your project (usually `index.php` or `bootstrap.php`).

```php
\Hawk\HawkCatcher::instance('abcd1234-1234-abcd-1234-123456abcdef');
```

You can store token in the environment file

```php
\Hawk\HawkCatcher::instance($_SERVER['HAWK_TOKEN']);
```

#### Custom Hawk server

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

### Catch exception

You can catch exceptions by yourself without enabling handlers.

```php
try {
    throw new Exception("Error Processing Request", 1);
} catch (Exception $e) {
    \Hawk\HawkCatcher::catchException($e);
}
```

## Links

Repository: https://github.com/codex-team/hawk.php

Report a bug: https://github.com/codex-team/hawk.php/issues

Composer Package: https://packagist.org/packages/codex-team/hawk.php

CodeX Team: https://ifmo.su
