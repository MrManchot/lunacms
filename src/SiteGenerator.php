<?php

namespace LunaCMS;

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
        'templates/includes',
        'cache/twig',
        'docker/nginx',
        'docker/nginx/logs',
        'docker/ssl',
        'docker/php'
    ];

    protected array $files = [
        'assets/js/script.js' => self::SCRIPT_JS_CONTENT,
        'assets/scss/main.scss' => self::MAIN_SCSS_CONTENT,
        'config/config.json' => self::CONFIG_JSON_TEMPLATE,
        'config/routes.php' => self::ROUTES_PHP_CONTENT,
        'public/index.php' => self::INDEX_PHP_TEMPLATE,
        'src/Controllers/PageController.php' => self::PAGE_CONTROLLER_CONTENT,
        'templates/index.twig' => self::INDEX_TWIG_CONTENT,
        'templates/includes/base.twig' => self::BASE_TWIG_CONTENT,
        'gulpfile.js' => self::GULPFILE_JS_CONTENT,
        '.env' => self::ENV_TEMPLATE,
        'docker-compose.yml' => self::DOCKER_COMPOSE_TEMPLATE,
        'docker/nginx/nginx.conf' => self::NGINX_CONF_TEMPLATE,
        'docker/php/Dockerfile' => self::DOCKERFILE_PHP_TEMPLATE,
        'docker/php/php-fpm.conf' => self::PHP_FPM_TEMPLATE,
        'docker/php/custom-php.ini' => self::CUSTOM_PHP_INI_TEMPLATE
    ];

    private const SCRIPT_JS_CONTENT = <<<'JS'
console.log('LunaCMS init');
JS;

    private const MAIN_SCSS_CONTENT = <<<'SCSS'
$main-color: #216291;

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    color: $main-color;
}

.container {
    margin: 0 auto;
    max-width: 1300px;
}
SCSS;

    private const CONFIG_JSON_TEMPLATE = <<<'JSON'
{
    "lang": "en",
    "charset": "UTF-8",
    "debug": true,
    "site": {
        "name": "Your Site Name",
        "base_url": "https://www.yoursite.com/"
    },
    "mail": {
        "from_address": "no-reply@example.com",
        "from_name": "Your Site Name",
        "reply_to_address": "support@example.com",
        "reply_to_name": "Support Team"
    },
    "openai": {
        "api_url": "https://api.openai.com/v1/",
        "default_model": "gpt-4o-mini",
        "default_temperature": 0.7
    },
    "salt": "{{SALT}}"
}
JSON;

    private const ENV_TEMPLATE = <<<'ENV'

# Projet
PROJECT_NAME=lunacms
SERVER_NAME=localhost

# Nginx
NGINX_HTTP_PORT=8080
NGINX_HTTPS_PORT=8443

# PHP & Base de données
PHP_VERSION=latest
MARIADB_VERSION=latest
MYSQL_ROOT_PASSWORD=rootpassword
MYSQL_DATABASE=mydatabase
MYSQL_USER=myuser
MYSQL_PASSWORD=mypassword
MYSQL_HOST=${PROJECT_NAME}_mariadb
MYSQL_PORT=3307

# SSL
SSL_CERTIFICATE=certificat.crt
SSL_CERTIFICATE_KEY=certificat.key

# Mail
MAIL_HOST=
MAIL_PORT=
MAIL_ENCRYPTION=

# API Keys
OPENAI_API_KEY=

ENV;


    private const ROUTES_PHP_CONTENT = <<<'PHP'
<?php

return [
    '' => 'App\\Controllers\\PageController'
];
PHP;

    private const INDEX_PHP_TEMPLATE = <<<'PHP'
<?php

session_start();
ini_set('session.gc_maxlifetime', 31536000);
ini_set('session.cookie_lifetime', 31536000);
ini_set('session.gc_probability', 0);

if (!defined('_BASE_PROJECT_')) {
    define('_BASE_PROJECT_', '{{BASE_PATH}}');
}

require_once _BASE_PROJECT_ . '/vendor/autoload.php';

try {
    $config = \LunaCMS\Config::getConfig();
} catch (Exception $e) {
    die('Failed to load configuration: ' . $e->getMessage());
}

if (!empty($config['debug'])) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

$routesPath = _BASE_PROJECT_ . '/config/routes.php';
if (!file_exists($routesPath)) {
    die('Routes configuration file does not exist.');
}

$routes = require $routesPath;
foreach ($routes as $route => $controller) {
    \LunaCMS\Routing::get($route, $controller);
}

try {
    new \LunaCMS\Routing();
} catch (Exception $e) {
    die('Failed to initialize routing: ' . $e->getMessage());
}
PHP;

    private const PAGE_CONTROLLER_CONTENT = <<<'PHP'
<?php

namespace App\Controllers;

use LunaCMS\Controller;

class PageController extends Controller
{
    public function dataAssignment(): void
    {
        $this->template = $this->params['slug'] ?? 'index';
        $this->addCss('/css/main.css');
        $this->addJs('/js/script.js');
        $this->addVar('meta_title', $this->site['name']);
        $this->addVar('site_title', $this->site['name']);
        $this->addVar('current_page', basename($_SERVER['REQUEST_URI'], '?'));
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
        <meta name="description" content="{{ meta_description }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {% for css_file in css %}
            <link rel="stylesheet" href="{{ css_file.file }}?t={{ css_file.version }}">
        {% endfor %}
    </head>
    <body class="template-{{ template }}">
        <div class="container">
            {% block title %}{% endblock %}
            <div id="content">{% block content %}{% endblock %}</div>
            {% for js_file in js %}
                <script src="{{ js_file.file }}?t={{ js_file.version }}"></script>
            {% endfor %}
        </div>
    </body>
</html>
TWIG;

    private const GULPFILE_JS_CONTENT = <<<'JS'
const gulp = require('gulp');
const dartSass = require('sass');
const gulpSass = require('gulp-sass')(dartSass);
const uglify = require('gulp-uglify');
const cleanCSS = require('gulp-clean-css');

gulp.task('scss', () =>
    gulp.src('assets/scss/main.scss')
        .pipe(gulpSass({ logger: dartSass.Logger.silent }).on('error', gulpSass.logError))
        .pipe(cleanCSS())
        .pipe(gulp.dest('public/css'))
);

gulp.task('js', () =>
    gulp.src('assets/js/**/*.js')
        .pipe(uglify())
        .pipe(gulp.dest('public/js'))
);

gulp.task('watch', () => {
    gulp.watch('assets/scss/**/*.scss', gulp.series('scss'));
    gulp.watch('assets/js/**/*.js', gulp.series('js'));
});

gulp.task('default', gulp.series('scss', 'js', 'watch'));
JS;



    private const DOCKER_COMPOSE_TEMPLATE = <<<'YML'
services:
  nginx:
    image: nginx:latest
    container_name: ${PROJECT_NAME}_nginx
    ports:
      - "${NGINX_HTTP_PORT}:80"
      - "${NGINX_HTTPS_PORT}:443"
    volumes:
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./docker/nginx/logs:/var/log/nginx
      - ./docker/ssl:/var/www/${PROJECT_NAME}/docker/ssl
      - .:/var/www/${PROJECT_NAME}
    depends_on:
      - php
      - mariadb
    networks:
      - default

  php:
    build:
      context: ./docker/php
      args:
        PHP_VERSION: ${PHP_VERSION}
    container_name: ${PROJECT_NAME}_php
    volumes:
      - .:/var/www/${PROJECT_NAME}
    working_dir: /var/www/${PROJECT_NAME}
    networks:
      - default

  mariadb:
    image: mariadb:${MARIADB_VERSION}
    container_name: ${PROJECT_NAME}_mariadb
    restart: always
    environment:
      MARIADB_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MARIADB_DATABASE: ${MYSQL_DATABASE}
      MARIADB_USER: ${MYSQL_USER}
      MARIADB_PASSWORD: ${MYSQL_PASSWORD}
    ports:
      - "${MYSQL_PORT}:3306"
    volumes:
      - mariadb_data:/var/lib/mysql
    networks:
      - default

  adminer:
    image: adminer:latest
    container_name: ${PROJECT_NAME}_adminer
    restart: always
    environment:
      ADMINER_DEFAULT_SERVER: ${PROJECT_NAME}_mariadb
    ports:
      - "${ADMINER_PORT}:8080"
    networks:
      - default

volumes:
  mariadb_data:

networks:
  default:
    name: ${PROJECT_NAME}_network
    driver: bridge
YML;

    private const NGINX_CONF_TEMPLATE = <<<'NGINX'
events {}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    server {
        listen 80;
        server_name ${SERVER_NAME};

        return 301 https://$host$request_uri;
    }

    server {
        listen 443 ssl;
        server_name ${SERVER_NAME};

        root /var/www/${PROJECT_NAME};
        index index.php index.html index.htm;

        ssl_certificate /var/www/${PROJECT_NAME}/docker/ssl/${SSL_CERTIFICATE};
        ssl_certificate_key /var/www/${PROJECT_NAME}/docker/ssl/${SSL_CERTIFICATE_KEY};

        location / {
            try_files $uri $uri/ /index.php$is_args$args;
        }

        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_pass ${PROJECT_NAME}_php:9000;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param DOCUMENT_ROOT $document_root;
        }
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
        $this->updateComposerAutoload();
        echo "Site structure successfully created at: {$this->basePath}\n";
    }

    protected function createDirectories(): void
    {
        foreach ($this->directories as $dir) {
            $path = $this->basePath . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($path)) {
                if (!mkdir($path, 0755, true)) {
                    throw new Exception("Failed to create directory: {$path}");
                }
                echo "Directory created: {$path}\n";
            } else {
                echo "Directory already exists: {$path}\n";
            }
            if ($dir === 'cache/twig') {
                if (!chmod($path, 0777)) {
                    throw new Exception("Failed to set permissions 777 on: {$path}");
                }
                echo "Permissions 777 set for directory: {$path}\n";
            }
        }
    }

    protected function createFiles(): void
    {
        $overwriteFiles = [
            'config/routes.php',
            'public/index.php',
            'src/Controllers/PageController.php',
            'gulpfile.js',
        ];
        foreach ($this->files as $filename => $content) {
            $filePath = $this->basePath . DIRECTORY_SEPARATOR . $filename;
            $dirPath = dirname($filePath);
            if (!is_dir($dirPath)) {
                if (!mkdir($dirPath, 0755, true)) {
                    throw new Exception("Failed to create directory: {$dirPath}");
                }
                echo "Directory created for file: {$dirPath}\n";
            }
            if (!file_exists($filePath) || in_array($filename, $overwriteFiles)) {
                if ($filename === 'public/index.php') {
                    $indexContent = str_replace('{{BASE_PATH}}', $this->basePath, self::INDEX_PHP_TEMPLATE);
                    if (file_put_contents($filePath, $indexContent) === false) {
                        throw new Exception("Failed to create file: {$filePath}");
                    }
                } elseif ($filename === 'config/config.json') {
                    $escapedBasePath = str_replace('\\', '\\\\', $this->basePath);
                    $salt = $this->generateSalt();
                    $configContent = str_replace('{{SALT}}', $salt, self::CONFIG_JSON_TEMPLATE);
                    if (file_put_contents($filePath, $configContent) === false) {
                        throw new Exception("Failed to create file: {$filePath}");
                    }
                } elseif ($filename === '.env') {
                    if (file_put_contents($filePath, $content) === false) {
                        throw new Exception("Failed to create file: {$filePath}");
                    }
                } else {
                    if (file_put_contents($filePath, $content) === false) {
                        throw new Exception("Failed to create file: {$filePath}");
                    }
                }
                echo "File created: {$filePath}\n";
            } else {
                echo "File already exists: {$filePath}\n";
            }
        }
    }

    protected function updateComposerAutoload(): void
    {
        $composerPath = $this->basePath . DIRECTORY_SEPARATOR . 'composer.json';
        if (file_exists($composerPath)) {
            $composerJson = file_get_contents($composerPath);
            if ($composerJson === false) {
                throw new Exception("Failed to read composer.json at: {$composerPath}");
            }
            $composerData = json_decode($composerJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error parsing composer.json: " . json_last_error_msg());
            }
            if (!isset($composerData['autoload']['psr-4'])) {
                $composerData['autoload']['psr-4'] = [
                    "App\\" => "src/"
                ];
                echo "Added PSR-4 autoload section to composer.json.\n";
            } elseif (!array_key_exists("App\\", $composerData['autoload']['psr-4'])) {
                $composerData['autoload']['psr-4']["App\\"] = "src/";
                echo "Added App\\ namespace to composer.json autoload section.\n";
            } else {
                echo "PSR-4 autoload section for App\\ already exists in composer.json.\n";
                return;
            }
            $newComposerJson = json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if ($newComposerJson === false) {
                throw new Exception("Failed to encode composer.json data.");
            }
            if (file_put_contents($composerPath, $newComposerJson) === false) {
                throw new Exception("Failed to write updated composer.json to: {$composerPath}");
            }
            echo "composer.json has been updated successfully.\n";
        } else {
            echo "composer.json does not exist at: {$composerPath}. Skipping autoload update.\n";
        }
    }

    protected function generateSalt(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }
}
