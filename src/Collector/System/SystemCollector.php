<?php

declare(strict_types=1);

namespace Jmonitor\Collector\System;

use Jmonitor\Collector\AbstractCollector;
use Jmonitor\Collector\System\Adapter\AdapterInterface;

class SystemCollector extends AbstractCollector
{
    private AdapterInterface $adapter;

    private array $longTermPropertyCache = [];

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function collect(): array
    {
        return [
            'disk' => [
                'total' => $this->adapter->getDiskTotalSpace('/'),
                'free' => $this->adapter->getDiskFreeSpace('/'),
            ],
            'cpu' => [
                'cores' => array_key_exists('core_count', $this->longTermPropertyCache) ? $this->longTermPropertyCache['core_count'] : $this->longTermPropertyCache['core_count'] = $this->adapter->getCoreCount(),
                'load' => $this->adapter->getLoadPercent(),
                'load1' => $this->adapter->getLoad1(),
                'load5' => $this->adapter->getLoad5(),
                'load15' => $this->adapter->getLoad15(),
            ],
            'ram' => [
                'total' => array_key_exists('total_memory', $this->longTermPropertyCache) ? $this->longTermPropertyCache['total_memory'] : $this->longTermPropertyCache['total_memory'] = $this->adapter->getTotalMemory(),
                'available' => $this->adapter->getAvailableMemory(),
            ],
            'os' => [
                'pretty_name' => array_key_exists('os_pretty_name', $this->longTermPropertyCache) ? $this->longTermPropertyCache['os_pretty_name'] : $this->longTermPropertyCache['os_pretty_name'] = $this->adapter->getOsPrettyName(),
                'uptime' => $this->adapter->getUptime(),
            ],
            'time' => time(),
            'timezone' => date_default_timezone_get(),
            'hostname' => gethostname(),
        ];
    }

    public function getVersion(): int
    {
        return 1;
    }

    public function afterCollect(): void
    {
        $this->adapter->reset();
    }
}
