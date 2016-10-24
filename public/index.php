<?php
if (!defined('APP_CORE_PATH')) {
    define('APP_CORE_PATH', realpath(__DIR__ . '/../slim-core/') . '/');
}
if (!defined('PUBLIC_BASE_PATH')) {
    define('PUBLIC_BASE_PATH', realpath(__DIR__) . '/');
}
if (!defined('SITE_URL')) {
    define('SITE_URL', 'http://modxrenderer.local/');
}

$loader = require APP_CORE_PATH . 'vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require APP_CORE_PATH . 'src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require APP_CORE_PATH . 'src/dependencies.php';

// Register middleware
require APP_CORE_PATH . 'src/middleware.php';

// Register routes
require APP_CORE_PATH . 'src/routes.php';

// Run app
$app->run();
