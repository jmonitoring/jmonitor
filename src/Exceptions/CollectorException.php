<?php

declare(strict_types=1);

namespace Jmonitor\Exceptions;

use Jmonitor\Collector\CollectorInterface;

/**
 * When a collector fail or can't collect data
 */
class CollectorException extends JmonitorException
{
    private CollectorInterface $collector;

    public function __construct(CollectorInterface $collector, string $message)
    {
        parent::__construct(sprintf('Collector %s failed: %s', get_class($collector), $message));

        $this->collector = $collector;
    }

    public function getCollector(): CollectorInterface
    {
        return $this->collector;
    }
}
