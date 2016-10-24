<?php
// Routes

$app->get('/', function ($request, $response, $args) {

$this->get('renderer'); // this didn't error out! TODO: do stuff with it to test

return $response;

});
