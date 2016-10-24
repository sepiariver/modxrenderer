<?php
// DIC configuration

$container = $app->getContainer();

// ModxRenderer\ModxRenderer
$rendererServiceName = $container->get('settings')['renderer']['service_name'];

$container[$rendererServiceName] = function($c) {
    return new MODXRenderer\MODXRenderer($container->get('settings'));
};

var_dump($container->get($rendererServiceName));
