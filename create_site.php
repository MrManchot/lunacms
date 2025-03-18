<?php

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Access denied. This script must be run from the command line.\n";
    exit(1);
}

// Vérification de la version PHP
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    echo "Error: PHP 7.4 or higher is required.\n";
    exit(1);
}

require_once __DIR__ . '/src/Core/SiteGenerator.php';

use LunaCMS\Core\SiteGenerator;

// Vérification des arguments
if ($argc < 2) {
    echo "Usage: php create_site.php /path/to/new/site\n";
    exit(1);
}

$basePath = rtrim($argv[1], DIRECTORY_SEPARATOR);

// Vérification du chemin spécifié
if (file_exists($basePath) && !is_dir($basePath)) {
    echo "Error: A file exists at the specified path. Please choose another location.\n";
    exit(1);
}

// Création du dossier si nécessaire
if (!is_dir($basePath) && !mkdir($basePath, 0755, true)) {
    echo "Error: Unable to create or access the specified path: $basePath\n";
    exit(1);
}

try {
    echo "Generating site structure at: $basePath ...\n";
    $generator = new SiteGenerator($basePath);
    $generator->generate();
    echo "✅ Site structure successfully created at: $basePath\n";
} catch (Exception $e) {
    error_log("Site generation failed: " . $e->getMessage());
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
