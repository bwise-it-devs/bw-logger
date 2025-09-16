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
        $context = ContextSanitizer::sanitize($context, $this->cfg['sanitize_keys']);
        return array_merge($context, [
            'app' => $this->cfg['app'],
            'ts'  => now()->toIso8601String(),
        ]);
    }
}
