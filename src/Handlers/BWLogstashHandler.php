<?php

namespace BwiseMedia\BWLogger\Handlers;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class BwiseLogstashHandler extends AbstractProcessingHandler
{
    protected array $cfg;

    public function __construct(array $cfg, int $level)
    {
        parent::__construct($level, true);
        $this->cfg = $cfg;
    }

    protected function write(LogRecord $record): void
    {
        $payload = $this->buildPayload($record);
        $driver  = $this->cfg['driver'] ?? 'http';

        if ($driver === 'tcp') {
            $this->sendTcp($payload);
        } else {
            $this->sendHttp($payload);
        }
    }

    protected function buildPayload(LogRecord $record): string
    {
        $context = $record->context ?? [];
        return json_encode([
            'level'       => strtolower($record->level->getName()),
            'message'     => $record->message,
            'timestamp'   => $record->datetime->format('c'),
            'app'         => $this->cfg['app']['name'] ?? env('APP_NAME', 'unknown-app'),
            'environment' => $this->cfg['app']['env'] ?? env('APP_ENV', 'development'),
            ...$context,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function sendHttp(string $payload): void
    {
        $host   = $this->cfg['host'] ?? '127.0.0.1';
        $port   = $this->cfg['port'] ?? 8080;
        $path   = $this->cfg['path'] ?? '/';
        $scheme = $this->cfg['scheme'] ?? 'http';
        $token  = $this->cfg['token'] ?? null;
        $url    = (str_starts_with($host, 'http://') || str_starts_with($host, 'https://'))
            ? rtrim($host, '/') . $path
            : sprintf('%s://%s:%d%s', $scheme, $host, $port, $path);

        $ch = curl_init();
        $headers = ['Content-Type: application/json'];
        if (!empty($token)) $headers[] = "Authorization: Bearer {$token}";

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => (float)($this->cfg['timeout'] ?? 2.0),
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno    = curl_errno($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($errno || $status >= 400) {
            error_log(sprintf('[BWLogger] HTTP send failed status=%s errno=%s error=%s response=%s',
                $status ?: 'n/a', $errno, $error ?: '-', $response ?: '-'));
        }
    }

    protected function sendTcp(string $payload): void
    {
        $host = $this->cfg['host'] ?? '127.0.0.1';
        $port = $this->cfg['port'] ?? 5000;

        $fp = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 1.0);
        if (!$fp) {
            error_log("[BWLogger] TCP connect failed: $errstr ($errno)");
            return;
        }
        fwrite($fp, $payload . "\n");
        fclose($fp);
    }
}
