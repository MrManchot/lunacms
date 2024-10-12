<?php

namespace LunaCMS;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use Exception;

class Database
{
    private static ?Database $instance = null;
    private Connection $connection;

    // Private constructor to prevent direct object creation
    private function __construct()
    {
        $config = Config::getConfig();
        $dbConfig = $config['database'];

        $connectionParams = [
            'dbname' => $dbConfig['dbname'],
            'user' => $dbConfig['user'],
            'password' => $dbConfig['password'],
            'host' => $dbConfig['host'],
            'driver' => 'pdo_mysql',
        ];

        try {
            $this->connection = DriverManager::getConnection($connectionParams);
        } catch (Exception $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('Could not establish database connection.');
        }
    }

    // Get the singleton instance
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Get the database connection
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    // Prevent cloning the singleton instance
    private function __clone() {}

    // Prevent unserializing the singleton instance
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize a singleton.");
    }
}
