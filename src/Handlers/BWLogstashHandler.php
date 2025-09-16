<?php

namespace BwiseMedia\BWLogger\Handlers;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class BWLogstashHandler extends AbstractProcessingHandler
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
        // prendi il contesto e togli tutto ciò che non vogliamo nel payload base
        $ctx = $record->context ?? [];
        unset($ctx['app'], $ctx['environment'], $ctx['timestamp'], $ctx['level'], $ctx['message'], $ctx['ts']);

        // payload in stile Node (app/env stringa, timestamp ISO8601 UTC con Z)
        $base = [
            'level'       => strtolower($record->level->getName()),
            'message'     => $record->message,
            'timestamp'   => $record->datetime->setTimezone(new \DateTimeZone('UTC'))
                                        ->format('Y-m-d\TH:i:s.v\Z'),
            'app'         => $this->cfg['app']['name'] ?? env('APP_NAME', 'unknown-app'),
            'environment' => $this->cfg['app']['env']  ?? env('APP_ENV', 'development'),
        ];

        // unione: i campi base vincono, il contesto aggiunge extra (come fa il logger JS)
        $payload = $base + $ctx;

        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function sendHttp(string $payload): void
    {
        $host    = (string) ($this->cfg['host'] ?? '127.0.0.1'); // hostname o endpoint completo
        $port    = (int)    ($this->cfg['port'] ?? 8080);
        $path    = (string) ($this->cfg['path'] ?? '/');
        $scheme  = (string) ($this->cfg['scheme'] ?? 'http');
        $token   = $this->cfg['token'] ?? null;
        $timeout = (float)  ($this->cfg['timeout'] ?? 2.0);
        $debug   = !empty($this->cfg['debug']);
        $verify  = array_key_exists('verify_ssl', $this->cfg) ? (bool)$this->cfg['verify_ssl'] : true;
        $endpoint = $this->cfg['endpoint'] ?? null;

        if ($path === '' || $path[0] !== '/') $path = '/'.$path;

        if ($endpoint) {
            $url = rtrim($endpoint, '/').($this->cfg['path'] ?? '/');
        } else {
            // host+port+path
            $host = (string)($this->cfg['host'] ?? '127.0.0.1');
            $port = (int)($this->cfg['port'] ?? 8080);
            $path = (string)($this->cfg['path'] ?? '/');
            $scheme = (string)($this->cfg['scheme'] ?? 'http');
            if ($path === '' || $path[0] !== '/') $path = '/'.$path;
            $url = sprintf('%s://%s:%d%s', $scheme, $host, $port, $path);
        }

        $headers = ['Content-Type: application/json'];
        if (!empty($token)) $headers[] = 'Authorization: Bearer '.$token;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_SSL_VERIFYPEER => $verify,
            CURLOPT_SSL_VERIFYHOST => $verify ? 2 : 0,
            CURLOPT_HEADER         => false,
            CURLOPT_FAILONERROR    => false,
        ]);

        if ($debug) error_log('[BWLogger] url='.$url.' payload='.$payload);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno    = curl_errno($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($errno || $status >= 400) {
            error_log(sprintf('[BWLogger] HTTP failed status=%s errno=%s error=%s resp=%s',
                $status ?: 'n/a', $errno, $error ?: '-', $debug ? (string)$response : '-'));
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
