<?php

declare(strict_types=1);

use iSemary\BackupSentry\Compress;
use PHPUnit\Framework\TestCase;

final class CompressTest extends TestCase {
    public function testCanCompressFile() {
        $result = (new Compress)->zip(__DIR__ . '/test.zip', __DIR__ . 'composer.json', '123');
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }
}
