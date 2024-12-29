<?php

declare(strict_types=1);

namespace LunaCMS;

use Exception;
use ErrorException;
use Dotenv\Dotenv;

class Config
{
    private static ?array $config = null;

    public static function getConfig(): array
    {
        if (!defined('_BASE_PROJECT_')) {
            throw new Exception('Constant _BASE_PROJECT_ is not defined.');
        }

        if (self::$config === null) {
            $dotenv = Dotenv::createImmutable(_BASE_PROJECT_);
            $dotenv->safeLoad();

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

            // Exemple de fusion : si .env définit DB_HOST, DB_USER, etc. on les injecte
            // Ici on montre comment surcharger la config en prenant les variables d'env si présentes
            // Tu peux adapter selon tes besoins (BDD, mails, etc.).
            if (isset($_ENV['DB_HOST'])) {
                $parsedConfig['database']['host'] = $_ENV['DB_HOST'];
            }
            if (isset($_ENV['DB_NAME'])) {
                $parsedConfig['database']['dbname'] = $_ENV['DB_NAME'];
            }
            if (isset($_ENV['DB_USER'])) {
                $parsedConfig['database']['user'] = $_ENV['DB_USER'];
            }
            if (isset($_ENV['DB_PASSWORD'])) {
                $parsedConfig['database']['password'] = $_ENV['DB_PASSWORD'];
            }
            if (isset($_ENV['MAIL_HOST'])) {
                $parsedConfig['mail']['host'] = $_ENV['MAIL_HOST'];
            }
            if (isset($_ENV['MAIL_PORT'])) {
                $parsedConfig['mail']['port'] = $_ENV['MAIL_PORT'];
            }
            if (isset($_ENV['MAIL_ENCRYPTION'])) {
                $parsedConfig['mail']['encryption'] = $_ENV['MAIL_ENCRYPTION'];
            }

            self::$config = $parsedConfig;
        }

        return self::$config;
    }

    public static function getConfigVar(string $key)
    {
        $config = self::getConfig();
        if (array_key_exists($key, $config)) {
            return $config[$key];
        }
        throw new ErrorException("Configuration key '{$key}' not found.");
    }
}
