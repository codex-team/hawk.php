# hawk.php

PHP errors Catcher module for [Hawk.so](https://hawk.so)

## Usage

[Register](https://hawk.so/join) an account and get a project token.

### Install module

Use [composer](https://getcomposer.org) to install Catcher

```bash
composer require codex-team/hawk.php:*
```

#### Download and require php file

You can download `hawk.php` file from this repository and require it in your project.

```php
require 'hawk.php';
```

### Add namespaces

Add this line at the top of your PHP script. [Why?](http://php.net/manual/en/language.namespaces.importing.php)

```php
use \Hawk\HawkCatcher;
```

### Enable Catcher

Create an instance and pass project token.

```php
HawkCatcher::instance('abcd1234-1234-abcd-1234-123456abcdef');
```

#### Custom Hawk server

If you want to use custom Hawk server then pass a url to this catcher.

```php
HawkCatcher::instance(
    'abcd1234-1234-abcd-1234-123456abcdef',
    'http://myownhawk.com/catcher/php'
);
```

## Links

Repository: https://github.com/codex-team/hawk.php

Report a bug: https://github.com/codex-team/hawk.php/issues

Composer Package: https://packagist.org/packages/codex-team/hawk.php

CodeX Team: https://ifmo.su
