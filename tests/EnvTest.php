<?php

declare(strict_types=1);

use iSemary\BackupSentry\Env\EnvHandler;
use PHPUnit\Framework\TestCase;

final class EnvTest extends TestCase {
    public function testCanGetEnvKeyValue() {
        $result = (new EnvHandler)->get("BACKUP_SENTRY_ZIP_PASSWORD");
        $this->assertIsString($result);
    }
}
