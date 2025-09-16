<?php
namespace BwiseMedia\BWLogger;

use Illuminate\Support\ServiceProvider;
use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;
use BwiseMedia\BWLogger\Handlers\BWLogstashHandler;

class BWLoggerServiceProvider extends ServiceProvider
{
    private function mapLevel(string $level): int {
        return match (strtolower($level)) {
            'debug' => Monolog::DEBUG,
            'info' => Monolog::INFO,
            'notice' => Monolog::NOTICE,
            'warning' => Monolog::WARNING,
            'error' => Monolog::ERROR,
            'critical' => Monolog::CRITICAL,
            'alert' => Monolog::ALERT,
            'emergency' => Monolog::EMERGENCY,
            default => Monolog::DEBUG,
        };
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bw-logger.php', 'bw-logger');

        $this->app->singleton(\BwiseMedia\BWLogger\Logger::class, function ($app) {
            $cfg = $app['config']->get('bw-logger');
            $monolog = new Monolog($cfg['channel'] ?? 'bwise');

            // livelli minimi (nuovi: vedi sezione D per la config)
            $fileMin     = $this->mapLevel($cfg['file_min_level'] ?? 'debug');
            $logstashMin = $this->mapLevel($cfg['logstash_min_level'] ?? 'info');

            // handler FILE: sempre presente
            $monolog->pushHandler(new StreamHandler(
                storage_path('logs/bwise.log'),
                $fileMin
            ));

            // handler LOGSTASH: opzionale
            if (($cfg['logstash']['enabled'] ?? false) === true) {
                $monolog->pushHandler(new BWLogstashHandler(
                    $cfg['logstash'],
                    $logstashMin
                ));
            }

            return new \BwiseMedia\BWLogger\Logger($monolog, $cfg);
        });

        $this->app->alias(\BwiseMedia\BWLogger\Logger::class, 'bw-logger');
    }

    public function boot(): void
    {
        // publish config
        $this->publishes([
            __DIR__.'/../config/bw-logger.php' => config_path('bw-logger.php'),
        ], 'bw-logger-config');
    }
}
