<?php
// DIC configuration

$container = $app->getContainer();

// ModxRenderer\ModxRenderer
$container['renderer'] = function($c) {
    MODXRenderer\MODXRenderer::$service_name = 'renderer';
    $settings = $c->get('settings');
    return new MODXRenderer\MODXRenderer($settings['renderer'], $settings['site']);
};
/*
MODXRenderer\MODXRenderer::$service_name = 'renderer';
$test = new MODXRenderer\MODXRenderer($settings['settings']);
var_dump($test);

exit();
*/
