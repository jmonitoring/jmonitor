<?php

/*
 * This file is part of Jmonitoring/Jmonitor
 *
 * (c) Jonathan Plantey <jonathan.plantey@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Jmonitor\Collector\Apache;

use Jmonitor\Collector\AbstractCollector;
use Jmonitor\Exceptions\CollectorException;

/**
 * Collects metrics using the Apache mod_status module.
 */
class ApacheCollector extends AbstractCollector
{
    /**
     * @var string
     */
    private $modStatusUrl;

    /**
     * @var array<string, mixed>
     */
    private $datas = [];

    public function __construct(string $modStatusUrl)
    {
        if (substr($modStatusUrl, 0, 4) === 'http' && substr($modStatusUrl, -5) !== '?auto') {
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
            'modules' => $this->getApacheModules(),
        ];
    }

    public function getVersion(): int
    {
        return 1;
    }

    /**
     * testing purpose
     * @return string|false
     */
    private function getModStatusContent()
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

    private function getApacheModules(): array
    {
        if (!function_exists('\apache_get_modules')) {
            return [];
        }

        return \apache_get_modules();
    }

    public function getName(): string
    {
        return 'apache';
    }
}
