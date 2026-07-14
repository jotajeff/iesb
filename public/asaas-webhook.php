<?php

declare(strict_types=1);

define('APP_DISABLE_SESSION', true);

require_once dirname(__DIR__) . '/bootstrap/app.php';

use App\Controllers\WebhookController;

$controller = new WebhookController();
$controller->asaas();
