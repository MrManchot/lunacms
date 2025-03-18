<?php

namespace LunaCMS\Core;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use Exception;

class Database
{
    private static ?Database $instance = null;
    private Connection $connection;

    private function __construct()
    {
        $config = Config::get('database');

        if (!$config) {
            throw new Exception('Database configuration is missing.');
        }

        $connectionParams = [
            'dbname' => $config['dbname'] ?? '',
            'user' => $config['user'] ?? '',
            'password' => $config['password'] ?? '',
            'host' => $config['host'] ?? 'localhost',
            'driver' => 'pdo_mysql',
            'charset' => 'utf8mb4'
        ];

        try {
            $this->connection = DriverManager::getConnection($connectionParams);
        } catch (Exception $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('Could not establish database connection.');
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function fetchOne(string $query, array $params = []): ?array
    {
        try {
            return $this->connection->fetchAssociative($query, $params) ?: null;
        } catch (Exception $e) {
            error_log('Database fetchOne error: ' . $e->getMessage());
            return null;
        }
    }

    public function fetchAll(string $query, array $params = []): array
    {
        try {
            return $this->connection->fetchAllAssociative($query, $params);
        } catch (Exception $e) {
            error_log('Database fetchAll error: ' . $e->getMessage());
            return [];
        }
    }

    public function executeQuery(string $query, array $params = []): bool
    {
        try {
            $this->connection->executeQuery($query, $params);
            return true;
        } catch (Exception $e) {
            error_log('Database executeQuery error: ' . $e->getMessage());
            return false;
        }
    }

    public function insert(string $table, array $data): bool
    {
        try {
            $this->connection->insert($table, $data);
            return true;
        } catch (Exception $e) {
            error_log('Database insert error: ' . $e->getMessage());
            return false;
        }
    }

    public function update(string $table, array $data, array $criteria): bool
    {
        try {
            $this->connection->update($table, $data, $criteria);
            return true;
        } catch (Exception $e) {
            error_log('Database update error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete(string $table, array $criteria): bool
    {
        try {
            $this->connection->delete($table, $criteria);
            return true;
        } catch (Exception $e) {
            error_log('Database delete error: ' . $e->getMessage());
            return false;
        }
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new Exception("Cannot unserialize a singleton.");
    }
}
