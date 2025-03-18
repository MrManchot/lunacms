<?php

namespace LunaCMS\Core;

use LunaCMS\Services\MailService;
use LunaCMS\Services\CacheService;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Exception;

abstract class Controller
{
    protected array $vars = [];
    protected Environment $twig;
    protected string $template = '';
    protected array $config;
    protected array $params;
    protected array $js = [];
    protected array $css = [];
    protected ?MailService $mailer = null;
    protected ?CacheService $cache = null;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->vars['lang'] = $this->config['lang'] ?? 'en';
        $this->vars['charset'] = $this->config['charset'] ?? 'UTF-8';

        if (!defined('_BASE_PROJECT_')) {
            throw new Exception('Constant _BASE_PROJECT_ is not defined.');
        }

        // Initialisation de Twig
        $this->twig = self::getTemplating();

        // Ajout d'une fonction personnalisée à Twig
        $this->twig->addFunction(new \Twig\TwigFunction('slugify', [$this, 'generateSlug']));

        // Initialisation des services
        $this->initializeServices();
    }

    private function initializeServices(): void
    {
        if (isset($this->config['mail'])) {
            $this->mailer = new MailService($this->config['mail']);
        }

        if (isset($this->config['redis'])) {
            try {
                $this->cache = new CacheService($this->config['redis']);
            } catch (Exception $e) {
                error_log('Redis initialization failed: ' . $e->getMessage());
            }
        }
    }

    public static function getTemplating(): Environment
    {
        if (!defined('_BASE_PROJECT_')) {
            throw new Exception('Constant _BASE_PROJECT_ is not defined.');
        }
        $basePath = rtrim(_BASE_PROJECT_, DIRECTORY_SEPARATOR);
        $loader = new FilesystemLoader($basePath . '/templates');
        return new Environment($loader, [
            'cache' => $basePath . '/cache/twig',
            'auto_reload' => true,
            'debug' => false
        ]);
    }

    public function sendEmail(string $toEmail, string $subject, string $body, string $altBody = null): bool
    {
        if (!$this->mailer) {
            error_log('MailService is not initialized.');
            return false;
        }

        return $this->mailer->sendEmail($toEmail, $subject, $body, $altBody);
    }

    public function getCacheValue(string $key)
    {
        return $this->cache ? $this->cache->get($key) : null;
    }

    public function setCacheValue(string $key, $value, int $ttl = 0): void
    {
        if ($this->cache) {
            $this->cache->set($key, $value, $ttl);
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

    public function treatment(): void
    {
    }

    public function dataAssignment(): void
    {
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
        error_log('Controller error: ' . $e->getMessage());
        http_response_code(500);
        echo '500 - Internal Server Error';
        exit;
    }

    public function generateSlug($string, $separator = '-', $extra = null)
    {
        $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
        $string = preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $string);
        $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');

        $extra = $extra ?? '';
        $string = preg_replace('~[^0-9a-z' . preg_quote($extra, '~') . ']+~i', $separator, $string);

        $string = trim($string, $separator);
        return strtolower($string);
    }
}
