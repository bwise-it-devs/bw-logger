## README.md

# Laravel Bwise Logger

A Laravel-ready logger package that integrates with **Monolog** and can forward logs directly to **Logstash / OpenSearch** or local storage. Inspired by the [@bwisemedia/bw-logger](https://www.npmjs.com/package/@bwisemedia/bw-logger) package for Node.js.

---

## Features

* ✅ Simple, expressive API (`BWLogger::info()`, `::error()`, ...)
* ✅ Auto-discovery support for Laravel 10/11
* ✅ Configurable Logstash handler (TCP or HTTP)
* ✅ Sensitive data sanitization
* ✅ Fallback to local log file (`storage/logs/bwise.log`)

---

## Installation

```bash
composer require bwisemedia/laravel-bw-logger
```

Publish configuration:

```bash
php artisan vendor:publish --tag=bw-logger-config
```

---

## Configuration

`.env` example:

```env
BW_LOG_CHANNEL=bwise
BW_LOGSTASH_ENABLED=true
BW_LOGSTASH_DRIVER=tcp
BW_LOGSTASH_HOST=opensearch-logstash
BW_LOGSTASH_PORT=5000
```

Config file (`config/bw-logger.php`):

```php
return [
    'channel' => env('BW_LOG_CHANNEL', 'bwise'),

    'logstash' => [
        'enabled' => env('BW_LOGSTASH_ENABLED', true),
        'driver'  => env('BW_LOGSTASH_DRIVER', 'tcp'), // tcp|http
        'host'    => env('BW_LOGSTASH_HOST', 'localhost'),
        'port'    => env('BW_LOGSTASH_PORT', 5000),
        'path'    => env('BW_LOGSTASH_HTTP_PATH', '/'),
        'timeout' => env('BW_LOGSTASH_TIMEOUT', 2.0),
    ],

    'app' => [
        'name'    => env('APP_NAME', 'bwise-app'),
        'env'     => env('APP_ENV', 'local'),
        'version' => env('APP_VERSION', null),
    ],

    'sanitize_keys' => ['password', 'token', 'authorization', 'cookie'],
];
```

---

## Usage

Use the facade anywhere in your Laravel app:

```php
use BWLogger;

BWLogger::info('User logged in', ['userId' => 123]);
BWLogger::error('Payment failed', [
    'orderId' => 987,
    'reason'  => 'insufficient_funds'
]);
```

---

## Logstash Example

`logstash.conf`:

```conf
input {
  tcp {
    port => 5000
    codec => json_lines
  }
}
filter {
  mutate { add_field => { "ingest_source" => "laravel-bw-logger" } }
}
output {
  opensearch {
    hosts => ["http://opensearch:9200"]
    index => "laravel-logs-%{+YYYY.MM.dd}"
    user => "admin"
    password => "password"
    ssl => false
  }
}
```

---

## Roadmap

* [ ] Add PHPUnit tests
* [ ] CI workflow with GitHub Actions
* [ ] Advanced context enrichment
* [ ] Laravel logging channel integration

---

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
