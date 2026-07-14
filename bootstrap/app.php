<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Env;
use App\Core\Router;
use App\Support\Session;

$vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';

if (is_file($vendorAutoload)) {
    require_once $vendorAutoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        $baseDir = dirname(__DIR__) . '/app/';

        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require $file;
        }
    });
}

if (!defined('APP_DISABLE_SESSION') || APP_DISABLE_SESSION !== true) {
    Session::start();
}
Env::load(dirname(__DIR__) . '/.env');

$router = new Router();
$registerRoutes = require dirname(__DIR__) . '/config/routes.php';
$registerRoutes($router);

return new App($router);
