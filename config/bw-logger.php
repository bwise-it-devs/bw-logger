<?php

return [
    'channel' => env('BW_LOG_CHANNEL', 'bwise'),

    'logstash' => [
        'enabled' => env('BW_LOGSTASH_ENABLED', env('LOGGER_SEND_TO_ELK', true)),
        'driver'  => env('BW_LOGSTASH_DRIVER', env('LOGGER_TRANSPORT_TYPE', 'http')),

        // modalità 1: host+port+path (raccomandata)
        'host'    => env('BW_LOGSTASH_HOST', '127.0.0.1'),
        'port'    => (int) env('BW_LOGSTASH_PORT', 8080),
        'path'    => env('BW_LOGSTASH_HTTP_PATH', '/'),
        'scheme'  => env('BW_LOGSTASH_SCHEME', 'http'),

        // oppure endpoint completo stile Node (se preferisci):
        // se usi HTTP_LOGGER_ENDPOINT, lascia vuoti HOST/PORT/PATH
        'endpoint'=> env('HTTP_LOGGER_ENDPOINT', null),

        'token'     => env('BW_LOGSTASH_TOKEN', env('HTTP_LOGGER_TOKEN')),
        'timeout'   => (float) env('BW_LOGSTASH_TIMEOUT', 2.0),
        'debug'     => (bool)  env('BW_LOGSTASH_DEBUG', false),
        'verify_ssl'=> (bool)  env('BW_LOGSTASH_VERIFY_SSL', true),
    ],

    'logstash_min_level' => env('BW_LOG_LOGSTASH_LEVEL', env('LOGGER_SEND_TO_ELK_MIN_LEVEL', 'error')),
    'file_min_level'     => env('BW_LOG_FILE_LEVEL', 'debug'),

    'app' => [
        'name'    => env('APP_NAME', 'bwise-app'),
        'env'     => env('APP_ENV', 'local'),
        'version' => env('APP_VERSION', null),
    ],

    'sanitize_keys' => ['password', 'token', 'authorization', 'cookie'],
];
