<?php
// DIC configuration

$container = $app->getContainer();

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// ModxRenderer\ModxRenderer
$rendererServiceName = $container->get('settings')['renderer']['service_name'];
$container[$rendererServiceName] = function($c) {
    return new ModxRenderer\ModxRenderer($c);
};

// Symfony\Finder
$container['finder'] = function($c) {
    return new Symfony\Component\Finder\Finder();
};

// Intervention\Image
$container['image'] = function($c) {
    return new Intervention\Image\ImageManager();
};
