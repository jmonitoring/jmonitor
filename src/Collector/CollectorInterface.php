<?php

declare(strict_types=1);

namespace Jmonitor\Collector;

interface CollectorInterface
{
    public function collect(): mixed;

    public function getVersion(): int;
}
