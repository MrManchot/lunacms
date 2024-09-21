<?php

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Access denied.\n";
    exit(1);
}

require_once __DIR__ . '/src/SiteGenerator.php';

use LunaCMS\SiteGenerator;

if ($argc < 2) {
    echo "Usage: php create_site.php /path/to/new/site\n";
    exit(1);
}

$basePath = $argv[1];

try {
    $generator = new SiteGenerator($basePath);
    $generator->generate();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
