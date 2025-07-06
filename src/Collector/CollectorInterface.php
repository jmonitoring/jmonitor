<?php

declare(strict_types=1);

namespace Jmonitor\Collector;

interface CollectorInterface
{
    public function beforeCollect(): void;

    public function collect(): mixed;

    public function afterCollect(): void;

    public function getVersion(): int;
}
