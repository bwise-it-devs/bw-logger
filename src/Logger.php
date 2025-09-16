<?php
namespace BwiseMedia\BWLogger;

use Monolog\Logger as Monolog;
use BwiseMedia\BWLogger\Helpers\ContextSanitizer;

class Logger
{
    public function __construct(
        protected Monolog $monolog,
        protected array $cfg
    ) {}

    public function info(string $message, array $context = []): void
    {
        $this->monolog->info($message, $this->enrich($context));
    }
    public function error(string $message, array $context = []): void
    {
        $this->monolog->error($message, $this->enrich($context));
    }
    public function debug(string $message, array $context = []): void
    {
        $this->monolog->debug($message, $this->enrich($context));
    }
    // … altri livelli
    protected function enrich(array $context): array
    {
        // opzionale: sanitizzazione
        if (class_exists(\BwiseMedia\BWLogger\Helpers\ContextSanitizer::class)) {
            $context = \BwiseMedia\BWLogger\Helpers\ContextSanitizer::sanitize(
                $context,
                $this->cfg['sanitize_keys'] ?? []
            );
        }

        // rimuovi chiavi che non devono sovrascrivere il payload
        unset($context['app'], $context['environment'], $context['timestamp'], $context['level'], $context['message'], $context['ts']);

        // aggiungi meta in stile Node (stringhe, non oggetti)
        $context['app']         = $this->cfg['app']['name'] ?? env('APP_NAME', 'unknown-app');
        $context['environment'] = $this->cfg['app']['env']  ?? env('APP_ENV', 'development');

        return $context;
    }
}
