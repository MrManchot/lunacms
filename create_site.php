<?php

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Access denied.\n";
    exit(1);
}

// Vérification de la version de PHP
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    echo "PHP 7.4 or higher is required.\n";
    exit(1);
}


require_once __DIR__ . '/src/SiteGenerator.php';

use LunaCMS\SiteGenerator;

if ($argc < 2) {
    echo "Usage: php create_site.php /path/to/new/site\n";
    exit(1);
}

$basePath = $argv[1];

// Vérification du chemin spécifié
if (!is_dir($basePath) && !mkdir($basePath, 0755, true)) {
    echo "Error: Unable to create or access the specified path: $basePath\n";
    exit(1);
}

try {
    $generator = new SiteGenerator($basePath);
    $generator->generate();
    echo "Site structure successfully created at: $basePath\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
