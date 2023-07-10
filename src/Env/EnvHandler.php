<?php

namespace iSemary\BackupSentry\Env;

use Dotenv\Dotenv;
use iSemary\BackupSentry\Config;

class EnvHandler {

    protected $projectPath;

    /**
     * The function retrieves the value of a specific environment variable from a .env file in the root
     * project directory.
     * 
     * @param envKey The `envKey` parameter is the key or name of the environment variable that you want to
     * retrieve the value for from the `.env` file.
     * 
     * @return The value of the environment variable specified by the  parameter.
     */
    public function get($envKey): ?string {
        // Get the root project directory from the config
        $this->projectPath = (new Config)->projectPath;
        // Load the env file of the root project file
        $dotenv = Dotenv::createImmutable($this->projectPath . '/');
        $dotenv->load();
        // Return the value of the .env file
        return isset($_ENV[$envKey]) ? $_ENV[$envKey] : false;
    }
}
