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

## Extensibility

The library is split into independent parts so new grabbers are easy to add:

- HTTP transport: `/Users/padox/Sites/personal/ulib-grabber/src/Http/HttpClientInterface.php`
- Default cURL transport: `/Users/padox/Sites/personal/ulib-grabber/src/Http/CurlHttpClient.php`
- Parser contract: `/Users/padox/Sites/personal/ulib-grabber/src/Parser/PhoneDirectoryParserInterface.php`
- Default parser: `/Users/padox/Sites/personal/ulib-grabber/src/Parser/PhoneDirectoryParser.php`

You can inject your own parser or HTTP client:

```php
<?php

declare(strict_types=1);

use Ulib\Grabber\Http\HttpClientInterface;
use Ulib\Grabber\Parser\PhoneDirectoryParser;
use Ulib\Grabber\UlibPhoneDirectory;

$customHttpClient = new class() implements HttpClientInterface {
    public function get(string $url, ?string $proxy = null): string
    {
        // custom transport implementation
        return file_get_contents($url) ?: '';
    }
};

$customParser = new PhoneDirectoryParser();
$grabber = new UlibPhoneDirectory([], null, $customParser, $customHttpClient);
```

## Smoke test

```bash
./bin/smoke-test.php
```
