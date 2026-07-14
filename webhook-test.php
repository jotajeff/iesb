<?php
declare(strict_types=1);

header('Content-Type: application/json');

echo json_encode([
    'status' => 'ok',
    'message' => 'Webhook endpoint is accessible',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'N/A',
], JSON_PRETTY_PRINT);
