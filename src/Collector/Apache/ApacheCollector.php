<?php

declare(strict_types=1);

namespace Jmonitor\Collector\Apache;

use Jmonitor\Collector\CollectorInterface;
use Jmonitor\Exceptions\CollectorException;

/**
 * Collects metrics using the Apache mod_status module.
 */
class ApacheCollector implements CollectorInterface
{
    private string $modStatusUrl;

    /**
     * @var array<string, mixed>
     */
    private array $datas = [];

    public function __construct(string $modStatusUrl)
    {
        if (str_starts_with($modStatusUrl, 'http') && !str_ends_with($modStatusUrl, '?auto')) {
            $modStatusUrl .= '?auto';
        }

        $this->modStatusUrl = $modStatusUrl;
    }

    /**
     * @return array<string, mixed>
     */
    public function collect(): array
    {
        $this->loadDatas();

        return [
            'server_version' => $this->getData('ServerVersion'),
            'server_mpm' => $this->getData('ServerMPM'),
            'uptime' => (int) $this->getData('Uptime'),
            'load1' => (float) $this->getData('Load1'),
            'load5' => (float) $this->getData('Load5'),
            'load15' => (float) $this->getData('Load15'),
            'total_accesses' => (int) $this->getData('Total Accesses'),
            'total_bytes' => (int) $this->getData('Total kBytes') * 1024,
            'requests_per_second' => (int) round((float) $this->getData('ReqPerSec')),
            'bytes_per_second' => (int) $this->getData('BytesPerSec'),
            'bytes_per_request' => (int) $this->getData('BytesPerReq'),
            'duration_per_request' => (int) $this->getData('DurationPerReq'),
            'workers' => [
                'busy' => (int) $this->getData('BusyWorkers'),
                'idle' => (int) $this->getData('IdleWorkers'),
            ],
            'scoreboard' => $this->parseScoreboard($this->getData('Scoreboard')),
        ];
    }

    public function getVersion(): int
    {
        return 1;
    }

    /**
     * testing purpose
     */
    private function getModStatusContent(): string|false
    {
        return file_get_contents($this->modStatusUrl);
    }

    private function loadDatas(): void
    {
        $this->datas = [];

        $content = $this->getModStatusContent();

        if (!$content) {
            throw new CollectorException('Could not fetch data from ' . $this->modStatusUrl, __CLASS__);
        }

        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $parts = explode(':', $line);
            $this->datas[$parts[0]] = isset($parts[1]) ? trim($parts[1]) : null;
        }
    }

    private function getData(string $key): ?string
    {
        return $this->datas[$key] ?? null;
    }

    /**
     * @return array<string, int>
     */
    private function parseScoreboard(string $scoreboard): array
    {
        $result = [];

        foreach (str_split($scoreboard) as $char) {
            $result[$char] = ($result[$char] ?? 0) + 1;
        }

        return $result;
    }
}
