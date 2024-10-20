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
    "base_path": "{{BASE_PATH}}",
    "lang": "en",
    "charset": "UTF-8",
    "database": {
        "host": "localhost",
        "dbname": "luncms",
        "user": "root",
        "password": ""
    },
    "debug": true,
    "site": {
        "name": "Your Site Name",
        "base_url": "https://www.yoursite.com/"
    },
    "mail": {
        "host": "smtp.example.com",
        "port": 587,
        "username": "your_email@example.com",
        "password": "your_email_password",
        "encryption": "tls",
        "from_address": "no-reply@example.com",
        "from_name": "Your Site Name",
        "reply_to_address": "support@example.com",
        "reply_to_name": "Support Team"
    },
    "salt": "{{SALT}}"
}
JSON;

    private const ROUTES_PHP_CONTENT = <<<'PHP'
<?php

return [
    '' => 'App\Controllers\PageController'
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
    $config = LunaCMS\Config::getConfig();
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
    LunaCMS\Routing::get($route, $controller);
}

try {
    new LunaCMS\Routing();
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
        $this->css[] = '/css/main.css';
        $this->js[] = '/js/main.js';
        $this->addVar('meta_title', $this->site['name']);
        $this->addVar('site_title', $this->site['name']);
        $this->addVar('current_page', basename($_SERVER['REQUEST_URI'], '?'));
    }
}
PHP;

    private const INDEX_TWIG_CONTENT = <<<'TWIG'
{% extends 'includes/base.twig' %}

{% block title %}
    <h1>Title</h1>
{% endblock %}

{% block content %}
    <p>LunaCMS</p>
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
        {% for file in css %}
            <link rel="stylesheet" href="{{ file }}">
        {% endfor %}
    </head>
    <body class="template-{{ template }}">
        <div class="container">
            {% block title %}{% endblock %}
            {% block content %}{% endblock %}
            <div id="content"> {% block content %}{% endblock %}</div>
            {% for file in js %}
                <script src="{{ file }}?t={{ random() }}"></script>
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

// Compile SCSS to CSS
gulp.task('scss', () =>
    gulp.src('assets/scss/main.scss') // Only compile main.scss which imports other partials like card.scss
        .pipe(gulpSass({
            logger: dartSass.Logger.silent
        }).on('error', gulpSass.logError))
        .pipe(cleanCSS())
        .pipe(gulp.dest('public/css'))
);

// Minify each JavaScript file individually
gulp.task('js', () =>
    gulp.src('assets/js/**/*.js')
        .pipe(uglify())
        .pipe(gulp.dest('public/js'))
);

// Watch files for changes
gulp.task('watch', () => {
    gulp.watch('assets/scss/**/*.scss', gulp.series('scss'));
    gulp.watch('assets/js/**/*.js', gulp.series('js'));
});

// Default task
gulp.task('default', gulp.series('scss', 'js', 'watch'));
JS;

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
                    $configContent = str_replace(['{{BASE_PATH}}', '{{SALT}}'], [$escapedBasePath, $salt], self::CONFIG_JSON_TEMPLATE);
                    if (file_put_contents($filePath, $configContent) === false) {
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
