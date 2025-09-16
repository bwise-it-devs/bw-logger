<?php

return [
    // canale di default per Monolog
    'channel' => env('BW_LOG_CHANNEL', 'bwise'),
    'file_min_level'     => env('BW_LOG_FILE_LEVEL', 'debug'),

    // invio diretto a Logstash (TCP/HTTP)
    'logstash' => [
        'enabled' => env('LOGGER_SEND_TO_ELK', true),
        'driver'  => env('LOGGER_TRANSPORT_TYPE', 'http'),
        'host'    => env('HTTP_LOGGER_ENDPOINT', 'http://127.0.0.1:8080'),
        'port'    => env('TCP_LOGGER_PORT', 5000),
        'token'   => env('HTTP_LOGGER_TOKEN'),
        'timeout' => 2.0,
    ],
    'logstash_min_level' => env('LOGGER_SEND_TO_ELK_MIN_LEVEL', 'error'),

    // campi comuni
    'app' => [
        'name'    => env('APP_NAME', 'bwise-app'),
        'env'     => env('APP_ENV', 'local'),
        'version' => env('APP_VERSION', null),
    ],

    // mascheramento dati sensibili
    'sanitize_keys' => ['password', 'token', 'authorization', 'cookie'],
];
