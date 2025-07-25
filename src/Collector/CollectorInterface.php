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

namespace Jmonitor\Collector;

interface CollectorInterface
{
    public function beforeCollect(): void;

    /**
     * @return mixed
     */
    public function collect();

    public function afterCollect(): void;

    public function getVersion(): int;

    public function getName(): string;
}
