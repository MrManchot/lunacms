<?php

namespace LunaCMS\Core;

use Exception;

class SiteGenerator
{
    protected string $basePath;

    protected array $directories = [
        'assets/js',
        'assets/scss',
        'config',
        'public/css',
        'public/js',
        'public/img',
        'src/Controllers',
        'src/Middleware',
        'src/Services',
        'src/Core',
        'templates/includes',
        'cache/twig',
        'docker/nginx',
        'docker/nginx/logs',
        'docker/ssl',
        'docker/php'
    ];

    protected array $files = [
        'config/config.json' => self::CONFIG_JSON_TEMPLATE,
        'config/routes.php' => self::ROUTES_PHP_CONTENT,
        'public/index.php' => self::INDEX_PHP_TEMPLATE,
        'src/Controllers/PageController.php' => self::PAGE_CONTROLLER_CONTENT,
        'templates/index.twig' => self::INDEX_TWIG_CONTENT,
        'templates/includes/base.twig' => self::BASE_TWIG_CONTENT,
        'gulpfile.js' => self::GULPFILE_JS_CONTENT,
        '.env' => self::ENV_TEMPLATE,
        'composer.json' => self::COMPOSER_JSON_TEMPLATE,
        'docker-compose.yml' => self::DOCKER_COMPOSE_TEMPLATE,
        'docker/nginx/nginx.conf' => self::NGINX_CONF_TEMPLATE
    ];

    private const CONFIG_JSON_TEMPLATE = <<<'JSON'
{
    "lang": "en",
    "charset": "UTF-8",
    "debug": true,
    "site": {
        "name": "Your Site Name",
        "base_url": "https://www.yoursite.com/"
    },
    "database": {
        "host": "localhost",
        "dbname": "lunacms",
        "user": "root",
        "password": "root"
    },
    "mail": {
        "from_address": "no-reply@example.com",
        "from_name": "Your Site Name",
        "reply_to_address": "support@example.com",
        "reply_to_name": "Support Team"
    }
}
JSON;

    private const ROUTES_PHP_CONTENT = <<<'PHP'
<?php

use LunaCMS\Core\Routing;
use App\Controllers\PageController;

Routing::get('/', PageController::class, 'home');
Routing::get('/contact', PageController::class, 'contact');
Routing::get('/user/{id}', PageController::class, 'profile');
PHP;

    private const INDEX_PHP_TEMPLATE = <<<'PHP'
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use LunaCMS\Core\Routing;

session_start();
Routing::dispatch();
PHP;

    private const PAGE_CONTROLLER_CONTENT = <<<'PHP'
<?php

namespace App\Controllers;

use LunaCMS\Core\Controller;

class PageController extends Controller
{
    public function home()
    {
        echo "Welcome to the homepage!";
    }

    public function contact()
    {
        echo "Contact us at contact@example.com";
    }

    public function profile($id)
    {
        echo "User Profile ID: " . htmlspecialchars($id);
    }
}
PHP;

    private const INDEX_TWIG_CONTENT = <<<'TWIG'
{% extends 'includes/base.twig' %}

{% block title %}
    <h1>LunaCMS</h1>
{% endblock %}

{% block content %}
<p>Content</p>
{% endblock %}
TWIG;

    private const BASE_TWIG_CONTENT = <<<'TWIG'
<!doctype html>
<html lang="{{ lang }}">
<head>
    <meta charset="{{ charset }}">
    <title>{{ meta_title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="container">
        {% block title %}{% endblock %}
        <div id="content">{% block content %}{% endblock %}</div>
    </div>
</body>
</html>
TWIG;

    private const COMPOSER_JSON_TEMPLATE = <<<'JSON'
{
    "name": "lunacms/lunacms",
    "type": "project",
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "require": {
        "php": ">=7.4",
        "twig/twig": "^3.0",
        "doctrine/dbal": "^3.0"
    }
}
JSON;

    private const DOCKER_COMPOSE_TEMPLATE = <<<'YML'
services:
  nginx:
    image: nginx:latest
    ports:
      - "8080:80"
      - "8443:443"
    volumes:
      - .:/var/www/lunacms
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
    depends_on:
      - php
      - mariadb

  php:
    build: ./docker/php
    volumes:
      - .:/var/www/lunacms
    working_dir: /var/www/lunacms

  mariadb:
    image: mariadb:latest
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: lunacms
      MYSQL_USER: user
      MYSQL_PASSWORD: password
    ports:
      - "3307:3306"
YML;

    private const NGINX_CONF_TEMPLATE = <<<'NGINX'
server {
    listen 80;
    root /var/www/lunacms;
    index index.php index.html index.htm;
    server_name localhost;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass php:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
NGINX;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
    }

    public function generate(): void
    {
        $this->createDirectories();
        $this->createFiles();
        echo "✅ Site structure successfully created at: {$this->basePath}\n";
    }

    protected function createDirectories(): void
    {
        foreach ($this->directories as $dir) {
            $path = $this->basePath . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    protected function createFiles(): void
    {
        foreach ($this->files as $filename => $content) {
            $filePath = $this->basePath . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($filePath, $content);
        }
    }
}
