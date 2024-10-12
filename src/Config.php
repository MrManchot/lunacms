<?php

namespace LunaCMS;

use Exception;
use ErrorException;

class Config
{
    public static function getConfig(): array
    {
        if (!defined('_BASE_PROJECT_')) {
            throw new Exception('Constant _BASE_PROJECT_ is not defined.');
        }
        $configPath = _BASE_PROJECT_ . '/config/config.json';
        if (!file_exists($configPath)) {
            throw new Exception('Configuration file does not exist.');
        }

        $configJson = file_get_contents($configPath);
        $config = json_decode($configJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error parsing configuration file: ' . json_last_error_msg());
        }

        return $config;
    }

    public static function getConfigVar(string $key)
    {
        $config = self::getConfig();
        if (array_key_exists($key, $config)) {
            return $config[$key];
        }
        throw new ErrorException("Configuration `{$key}` not found.");
    }
}
