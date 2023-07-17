<?php

namespace iSemary\BackupSentry\Console;

use Illuminate\Console\Command;

class BackupSentryCommand extends Command {
    protected $signature = 'backup-sentry:publish';

    protected $description = 'Publish BackupSentry configuration';

    public function handle() {
        $this->call('vendor:publish', [
            '--provider' => 'iSemary\BackupSentry\BackupSentryServiceProvider',
            '--tag' => 'config', // Specify the configuration tag
        ]);

        $this->info('BackupSentry configuration published successfully!');
    }
}
