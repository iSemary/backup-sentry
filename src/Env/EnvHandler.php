<?php

namespace iSemary\BackupSentry\Env;

use Dotenv\Dotenv;

class EnvHandler {

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
        $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        // Go back to the root project directory
        $projectPath = dirname($reflection->getFileName(), 3);
        // Load the env file of the root project file
        $dotenv = Dotenv::createImmutable($projectPath . '/');
        $dotenv->load();
        // Return the value of the .env file
        return isset($_ENV[$envKey]) ? $_ENV[$envKey] : false;
    }
}
