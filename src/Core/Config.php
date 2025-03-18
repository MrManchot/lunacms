<?php

namespace LunaCMS\Core;

use Exception;
use Dotenv\Dotenv;

class Config
{
    private static ?array $config = null;

    public static function load(): void
    {
        if (!defined('_BASE_PROJECT_')) {
            throw new Exception('Constant _BASE_PROJECT_ is not defined.');
        }

        // Charger les variables d'environnement
        $dotenv = Dotenv::createImmutable(_BASE_PROJECT_);
        $dotenv->safeLoad();

        // Charger la configuration JSON
        $configPath = _BASE_PROJECT_ . '/config/config.json';

        if (!file_exists($configPath)) {
            throw new Exception("Configuration file does not exist at: {$configPath}");
        }

        $configJson = file_get_contents($configPath);

        if ($configJson === false) {
            throw new Exception("Failed to read configuration file: {$configPath}");
        }

        $parsedConfig = json_decode($configJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error parsing configuration file: ' . json_last_error_msg());
        }

        if (!is_array($parsedConfig)) {
            throw new Exception('Configuration file must return an associative array.');
        }

        // Fusionner les variables d'environnement avec la config JSON
        self::$config = self::mergeWithEnv($parsedConfig);
    }

    public static function get(string $key, $default = null)
    {
        if (self::$config === null) {
            self::load();
        }

        return self::$config[$key] ?? $default;
    }

    private static function mergeWithEnv(array $config): array
    {
        $envOverrides = [
            'database' => [
                'host' => getenv('MYSQL_HOST'),
                'dbname' => getenv('MYSQL_DATABASE'),
                'user' => getenv('MYSQL_USER'),
                'password' => getenv('MYSQL_PASSWORD')
            ],
            'mail' => [
                'host' => getenv('MAIL_HOST'),
                'port' => getenv('MAIL_PORT'),
                'username' => getenv('MAIL_USERNAME'),
                'password' => getenv('MAIL_PASSWORD'),
                'encryption' => getenv('MAIL_ENCRYPTION')
            ],
            'redis' => [
                'host' => getenv('REDIS_HOST'),
                'port' => getenv('REDIS_PORT')
            ],
            'openai' => [
                'api_url' => getenv('OPENAI_API_URL'),
                'default_model' => getenv('OPENAI_DEFAULT_MODEL'),
                'default_temperature' => getenv('OPENAI_DEFAULT_TEMPERATURE')
            ]
        ];

        // Appliquer les valeurs des variables d'environnement si elles existent
        foreach ($envOverrides as $section => $values) {
            if (!isset($config[$section])) {
                $config[$section] = [];
            }

            foreach ($values as $key => $envValue) {
                if ($envValue !== false) {
                    $config[$section][$key] = $envValue;
                }
            }
        }

        return $config;
    }
}
