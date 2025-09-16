<?php

return [
    // canale di default per Monolog
    'channel' => env('BW_LOG_CHANNEL', 'bwise'),

    // invio diretto a Logstash (TCP/HTTP)
    'logstash' => [
        'enabled'   => env('BW_LOGSTASH_ENABLED', true),
        'driver'    => env('BW_LOGSTASH_DRIVER', 'tcp'), // tcp|http
        'host'      => env('BW_LOGSTASH_HOST', 'localhost'),
        'port'      => env('BW_LOGSTASH_PORT', 5000),
        'path'      => env('BW_LOGSTASH_HTTP_PATH', '/'),
        'timeout'   => env('BW_LOGSTASH_TIMEOUT', 2.0),
        'token'   => env('BW_LOGSTASH_TOKEN', null),
    ],

    // campi comuni
    'app' => [
        'name'    => env('APP_NAME', 'bwise-app'),
        'env'     => env('APP_ENV', 'local'),
        'version' => env('APP_VERSION', null),
    ],

    // mascheramento dati sensibili
    'sanitize_keys' => ['password', 'token', 'authorization', 'cookie'],
];
