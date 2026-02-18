# ulib/grabber

A small library for fetching data from the ULIB phone directory.

## Requirements

- PHP `8.4+`
- `ext-curl`
- `ext-dom`
- `ext-libxml`

## Usage

```php
<?php

declare(strict_types=1);

use Ulib\Grabber\UlibPhoneDirectory;

$grabber = new UlibPhoneDirectory([
    'firstname' => 'Jan',
    'sort' => 1,
]);

$users = $grabber->getUsers();
$paginator = $grabber->getPaginator();
$pageResult = $grabber->getPageResult();
```

Supported query parameters:

`firstname`, `lastname`, `phone`, `room`, `mail`, `department`, `page`, `sort`, `column`.

The second constructor argument is an optional proxy server (`host:port`).

## Smoke test

```bash
./bin/smoke-test.php
```
