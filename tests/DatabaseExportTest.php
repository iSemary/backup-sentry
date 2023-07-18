<?php

declare(strict_types=1);

use iSemary\BackupSentry\Config;
use iSemary\BackupSentry\DB\Export;
use iSemary\BackupSentry\Storage\StorageHandler;
use PHPUnit\Framework\TestCase;

final class DatabaseExportTest extends TestCase {
    public function testCanExportDatabase() {
        $config = new Config;
        $result = (new Export($config))->run();
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertIsString($result['file_name']);
    }

}
