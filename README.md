# WilyCode HTTP Response

[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4)](https://www.php.net/)
[![MIT License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Tests](https://github.com/wilycode/http-response/actions/workflows/tests.yml/badge.svg)](https://github.com/wilycode/http-response/actions/workflows/tests.yml)

**Lightweight, typed PSR-7 response creation using PSR-17 factories, with helpers for HTML, JSON, redirects and empty responses.**

> ### WilyCode PHP Ecosystem
>
> This library is part of **[WilyCode](https://github.com/wilycode/wilycode)**, a collection of independent PHP components. Each package has its own repository, Composer installation and releases.
>
> **[Explore the WilyCode ecosystem →](https://github.com/wilycode/wilycode)**

## Requirements

- PHP 8.4 or newer.
- `psr/http-message` (PSR-7 contracts) and `psr/http-factory` (PSR-17 contracts).
- A concrete PSR-7/PSR-17 implementation in your application, such as [`nyholm/psr7`](https://github.com/Nyholm/psr7). The library does **not** force a specific implementation.

## Installation

```bash
composer require wilycode/http-response:^0.1
```

For a standalone project that has no PSR-7/PSR-17 implementation yet, you can additionally install:

```bash
composer require nyholm/psr7
```

## Quick start

```php
<?php

use Nyholm\Psr7\Factory\Psr17Factory;
use WilyCode\Http\Response\HttpStatus;
use WilyCode\Http\Response\ResponseFactory;

$psr17 = new Psr17Factory();
$responses = new ResponseFactory($psr17, $psr17);

$html = $responses->html('<h1>Hello</h1>');
$json = $responses->json(['id' => 15, 'title' => 'Zażółć'], HttpStatus::Created);
$redirect = $responses->redirect('/articles');
$noContent = $responses->empty();
```

All methods return `Psr\Http\Message\ResponseInterface`. Responses are immutable PSR-7 objects.

## Response methods

| Method | Default status | Behavior |
| --- | --- | --- |
| `html(string $html, HttpStatus $status = HttpStatus::Ok, array $headers = [])` | `200 OK` | Sets `Content-Type: text/html; charset=utf-8`. |
| `json(array $data, HttpStatus $status = HttpStatus::Ok, array $headers = [])` | `200 OK` | JSON-encodes the array and sets `Content-Type: application/json; charset=utf-8`. |
| `redirect(string $url, HttpStatus $status = HttpStatus::SeeOther, array $headers = [])` | `303 See Other` | Sets a `Location` header and an empty body. |
| `empty(HttpStatus $status = HttpStatus::NoContent, array $headers = [])` | `204 No Content` | Produces an empty response body. |

### JSON responses

```php
$response = $responses->json(
    ['id' => 15, 'title' => 'Article'],
    HttpStatus::Created,
    ['Location' => '/articles/15'],
);
```

JSON output uses `JSON_THROW_ON_ERROR`, `JSON_UNESCAPED_UNICODE`, and `JSON_UNESCAPED_SLASHES`. Invalid JSON values raise `JsonException` instead of being silently converted. Arrays may contain objects, lists and nested values as supported by `json_encode()`.

### Custom headers

```php
$response = $responses->html(
    '<p>Hello</p>',
    headers: ['Cache-Control' => 'no-store', 'Content-Type' => 'application/xhtml+xml'],
);
```

Headers accept strings or lists of strings. User-provided headers are applied after defaults, so they override default header values (including differently cased names, as defined by PSR-7).

### Redirects

```php
$response = $responses->redirect('/articles'); // 303 See Other
$response = $responses->redirect('/articles', HttpStatus::Found); // 302 Found
$response = $responses->redirect('/old', HttpStatus::MovedPermanently); // 301 Moved Permanently
```

The factory does **not** validate whether a redirect URL is safe for your application's authorization rules. Validate or restrict user-controlled destinations in your application to avoid open redirects.

### Empty responses

```php
$response = $responses->empty(); // 204 No Content
$response = $responses->empty(HttpStatus::NotModified); // 304 Not Modified
```

## Status codes

The `HttpStatus` backed enum exposes the HTTP status code and name:

```php
HttpStatus::Created->value; // 201
HttpStatus::Created->name; // "Created"
```

Response methods deliberately accept `HttpStatus` rather than arbitrary integers. The enum includes the common success, redirect, client error and server error statuses. See [`src/HttpStatus.php`](src/HttpStatus.php) for the current set.

## Integration

`ResponseFactory` implements [`ResponseFactoryInterface`](src/ResponseFactoryInterface.php), making it suitable for dependency injection. It requires a PSR-17 `ResponseFactoryInterface` and `StreamFactoryInterface` in its constructor. You may supply one object that implements both or two separate objects.

The library does not require a router, template engine, session layer, framework or particular PSR-7 implementation.

## Development

```bash
composer install
composer validate --strict
composer test
```

Test fixtures use `nyholm/psr7` as a **development-only** dependency. GitHub Actions runs PHPUnit on PHP 8.4.

Tests and CI files are retained in Git but excluded from standard Composer distribution archives. Installing with `--prefer-source` clones the repository and therefore includes those files.

## License

MIT. See [LICENSE](LICENSE).
