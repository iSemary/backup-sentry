<?php

namespace iSemary\BackupSentry;

use Illuminate\Support\ServiceProvider;
use iSemary\BackupSentry\Console\BackupSentryCommand;

class BackupSentryServiceProvider extends ServiceProvider {
    public function boot() {
        if ($this->app->runningInConsole()) {
            // Publishes the configuration file when 'backup-sentry:publish' is executed
            $this->publishes([
                __DIR__ . '/../config/BackupSentry.php' => config_path('BackupSentry.php'),
            ], 'config');

            $this->commands([
                BackupSentryCommand::class,
            ]);
        }
    }
}
