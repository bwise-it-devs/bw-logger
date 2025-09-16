<?php
namespace BwiseMedia\BWLogger;

use Illuminate\Support\ServiceProvider;
use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;
use BwiseMedia\BWLogger\Handlers\BWLogstashHandler;

class BWLoggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bw-logger.php', 'bw-logger');

        $this->app->singleton(Logger::class, function ($app) {
            $config = $app['config']->get('bw-logger');

            $monolog = new Monolog($config['channel']);

            // Logstash handler (se abilitato)
            if ($config['logstash']['enabled']) {
                $monolog->pushHandler(new BWLogstashHandler($config['logstash'], Monolog::DEBUG));
            } else {
                // fallback locale (storage/logs/bwise.log)
                $monolog->pushHandler(new StreamHandler(storage_path('logs/bwise.log'), Monolog::DEBUG));
            }

            return new Logger($monolog, $config);
        });

        $this->app->alias(Logger::class, 'bw-logger');
    }

    public function boot(): void
    {
        // publish config
        $this->publishes([
            __DIR__.'/../config/bw-logger.php' => config_path('bw-logger.php'),
        ], 'bw-logger-config');
    }
}
