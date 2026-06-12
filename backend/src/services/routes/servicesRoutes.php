<?php

use App\Services\Controllers\ServicesController;

$app->group('/api/services', function ($app) {
    $app->get('/',      fn ($ctx) => ServicesController::index($ctx));
    $app->get('/:id',   fn ($ctx) => ServicesController::show($ctx));
    $app->post('/',     fn ($ctx) => ServicesController::store($ctx));
    $app->put('/:id',   fn ($ctx) => ServicesController::update($ctx));
    $app->delete('/:id',fn ($ctx) => ServicesController::destroy($ctx));
});
