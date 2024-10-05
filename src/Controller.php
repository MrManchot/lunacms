<?php

namespace LunaCMS;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use ErrorException;
use Exception;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Redis;

abstract class Controller
{
    protected array $vars = [];
    protected Environment $twig;
    protected string $template = '';
    protected array $config;
    protected array $params;
    protected array $js = [];
    protected array $css = [];
    protected Connection $connection;
    protected PHPMailer $mailer;
    protected Redis $redis;

    public function __construct(Environment $twig, array $config)
    {
        $this->config = $config;
        $this->vars['lang'] = $this->config['lang'] ?? 'en';
        $this->vars['charset'] = $this->config['charset'] ?? 'UTF-8';

        if (!defined('_BASE_PROJECT_')) {
            throw new Exception('Constant _BASE_PROJECT_ is not defined.');
        }
        $basePath = rtrim(_BASE_PROJECT_, DIRECTORY_SEPARATOR);

        $loader = new FilesystemLoader($basePath . '/templates');
        $this->twig = new Environment($loader, [
            'cache' => $basePath . '/cache/twig',
            'auto_reload' => true,
            'debug' => false
        ]);

        $this->initializeDatabaseConnection();
        $this->initializeMailer();
        $this->initializeRedis();
    }

    protected function initializeDatabaseConnection(): void
    {
        try {
            $dbConfig = $this->config['database'];
            $connectionParams = [
                'dbname' => $dbConfig['dbname'],
                'user' => $dbConfig['user'],
                'password' => $dbConfig['password'],
                'host' => $dbConfig['host'],
                'driver' => 'pdo_mysql',
            ];
            $this->connection = DriverManager::getConnection($connectionParams);
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    protected function initializeMailer(): void
    {
        $mailConfig = $this->config['mail'];

        $this->mailer = new PHPMailer(true);

        try {
            $this->mailer->isSMTP();
            $this->mailer->Host       = $mailConfig['host'];
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = $mailConfig['username'];
            $this->mailer->Password   = $mailConfig['password'];
            $this->mailer->SMTPSecure = $mailConfig['encryption'];
            $this->mailer->Port       = $mailConfig['port'];

            $this->mailer->setFrom($mailConfig['from_address'], $mailConfig['from_name']);
            if (!empty($mailConfig['reply_to_address'])) {
                $this->mailer->addReplyTo($mailConfig['reply_to_address'], $mailConfig['reply_to_name']);
            }

            $this->mailer->isHTML(true);
        } catch (PHPMailerException $e) {
            $this->handleError(new Exception('Mailer initialization failed: ' . $e->getMessage()));
        }
    }

    protected function initializeRedis(): void
    {
        try {
            $this->redis = new Redis();
            $this->redis->connect($this->config['redis']['host'], $this->config['redis']['port']);
        } catch (Exception $e) {
            $this->handleError(new Exception('Redis initialization failed: ' . $e->getMessage()));
        }
    }

    public function getRedisValue(string $key)
    {
        try {
            return $this->redis->get($key);
        } catch (Exception $e) {
            $this->handleError(new Exception('Failed to get value from Redis: ' . $e->getMessage()));
        }
    }

    public function setRedisValue(string $key, $value, int $ttl = 0): void
    {
        try {
            $this->redis->set($key, $value, $ttl);
        } catch (Exception $e) {
            $this->handleError(new Exception('Failed to set value in Redis: ' . $e->getMessage()));
        }
    }

    public function sendEmail(string $toEmail, string $toName, string $subject, string $body, string $altBody = null): bool
    {
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            throw new ErrorException("Invalid email address: {$toEmail}");
        }

        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->mailer->addAddress($toEmail, $toName);

            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->AltBody = $altBody ?? strip_tags($body);

            $this->mailer->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log('Mailer Error: ' . $e->getMessage());
            return false;
        }
    }

    public function init(array $params): void
    {
        $this->params = $params;
        try {
            $this->treatment();
            $this->dataAssignment();
            if ($this->template) {
                $this->addVar('js', $this->js);
                $this->addVar('css', $this->css);
                $this->addVar('template', $this->template);
                $this->display();
            }
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

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

    public static function getTemplating(): Environment
    {
        if (!defined('_BASE_PROJECT_')) {
            throw new Exception('Constant _BASE_PROJECT_ is not defined.');
        }
        $loader = new FilesystemLoader(_BASE_PROJECT_ . '/templates');
        return new Environment($loader, [
            'cache' => _BASE_PROJECT_ . '/cache/twig',
            'auto_reload' => true,
            'debug' => false
        ]);
    }

    public function getConfigVar(string $key)
    {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }
        throw new ErrorException("Configuration `{$key}` not found.");
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getMailer(): PHPMailer
    {
        return $this->mailer;
    }

    public function treatment(): void {
    }

    public function dataAssignment(): void {
    }

    protected function addVar(string $key, $value): void
    {
        $this->vars[$key] = $value;
    }

    public function display(): void
    {
        if (empty($this->template)) {
            throw new Exception('No template defined for the controller.');
        }
        echo $this->twig->render($this->template . '.twig', $this->vars);
    }

    protected function handleError(Exception $e): void
    {
        if ($this->getConfigVar('debug')) {
            echo $e->getMessage();
        } else {
            http_response_code(500);
            echo '500 - Internal Server Error';
        }
        exit;
    }

    public function addJs(string $path): void
    {
        $this->js[] = $path;
    }

    public function addCss(string $path): void
    {
        $this->css[] = $path;
    }
}