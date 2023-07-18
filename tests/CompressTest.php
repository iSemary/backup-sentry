<?php

declare(strict_types=1);

use iSemary\BackupSentry\Compress;
use iSemary\BackupSentry\Config;
use iSemary\BackupSentry\Storage\StorageHandler;
use PHPUnit\Framework\TestCase;

final class CompressTest extends TestCase {

    public function testCanCompressFile() {
        $result = (new Compress)->zip(__DIR__ . '/test.zip', __DIR__ . '/../README.md', '123', true);
        $this->assertFileExists(__DIR__ . '/test.zip');
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testCanCleanUp() {
        $config = new Config;
        $result = (new StorageHandler($config))->cleanUp([__DIR__ . '/test.zip']);
        $this->assertFileDoesNotExist(__DIR__ . '/test.zip');
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }
}
