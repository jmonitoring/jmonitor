<?php

declare(strict_types=1);

namespace Jmonitor;

use Jmonitor\Collector\CollectorInterface;
use Psr\Http\Client\ClientInterface;

class Jmonitor
{
    public const VERSION = '1.0';

    /**
     * @var CollectorInterface[]
     */
    private array $collectors = [];

    private Client $client;

    public function __construct(string $projectApiKey, ?ClientInterface $httpClient = null)
    {
        $this->client = new Client($projectApiKey, $httpClient);
    }

    public function addCollector(CollectorInterface $collector): void
    {
        $this->collectors[] = $collector;
    }

    public function collect(): void
    {
        $metrics = [];

        foreach ($this->collectors as $collector) {
            $time = microtime(true);

            $collector->beforeCollect();

            $metrics[] = [
                'version' => $collector->getVersion(),
                'name' => $collector->getName(),
                'metrics' => $collector->collect(),
                'time' => microtime(true) - $time,
            ];

            $collector->afterCollect();
        }

        if (count($metrics) === 0) {
            if (count($this->collectors) === 0) {
                throw new \RuntimeException('Please add some collectors before sending metrics.');
            }

            throw new \RuntimeException('All collectors failed to collect data.'); // @phpstan-ignore-line
        }

        $this->client->sendMetrics($metrics);
    }
}
