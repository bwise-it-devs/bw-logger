<?php
namespace BwiseMedia\BWLogger\Handlers;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class BWLogstashHandler extends AbstractProcessingHandler
{
    protected array $cfg;
    protected $socket;

    public function __construct(array $cfg, int $level)
    {
        parent::__construct($level, true);
        $this->cfg = $cfg;
    }

    protected function write(LogRecord $record): void
    {
        $payload = json_encode($record->toArray(), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

        if ($this->cfg['driver'] === 'http') {
            $this->sendHttp($payload);
        } else {
            $this->sendTcp($payload);
        }
    }

    protected function sendTcp(string $payload): void
    {
        $endpoint = sprintf('tcp://%s:%d', $this->cfg['host'], $this->cfg['port']);
        $fp = @stream_socket_client($endpoint, $errno, $errstr, $this->cfg['timeout']);
        if ($fp === false) return; // opzionale: lanciare eccezione

        fwrite($fp, $payload . PHP_EOL); // Logstash codec => json_lines
        fclose($fp);
    }

    protected function sendHttp(string $payload): void
    {
        $url = sprintf('http://%s:%d%s', $this->cfg['host'], $this->cfg['port'], $this->cfg['path'] ?? '/');
        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => $this->cfg['timeout'],
            ]
        ]);
        @file_get_contents($url, false, $context);
    }
}
