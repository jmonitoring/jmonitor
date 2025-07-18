<?php

/*
 * This file is part of Jmonitoring/Jmonitor
 *
 * (c) Jonathan Plantey <jonathan.plantey@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jmonitor\Tests\Collector\Php;

use Jmonitor\Collector\Php\PhpCollector;
use PHPUnit\Framework\TestCase;

class PhpCollectorTest extends TestCase
{
    public function testCollect(): void
    {
        $collector = new PhpCollector();
        $result = $collector->collect();

        $this->assertArrayHasKey('version', $result);
        $this->assertArrayHasKey('ini_file', $result);
        $this->assertArrayHasKey('ini_files', $result);
        $this->assertArrayHasKey('memory_limit', $result);
        $this->assertArrayHasKey('max_execution_time', $result);
        $this->assertArrayHasKey('post_max_size', $result);
        $this->assertArrayHasKey('upload_max_filesize', $result);
        $this->assertArrayHasKey('date.timezone', $result);
        $this->assertArrayHasKey('loaded_extensions', $result);
        $this->assertArrayHasKey('opcache', $result);
        $this->assertArrayHasKey('fpm', $result);
    }

    public function testGetVersion()
    {
        $collector = new PhpCollector();

        $this->assertSame(1, $collector->getVersion());
    }
}
