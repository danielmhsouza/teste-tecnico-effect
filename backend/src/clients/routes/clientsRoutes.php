<?php

use App\Clients\Controllers\ClientsController;

$app->group('/api/clients', function ($app) {
    $app->get('/',      fn ($ctx) => ClientsController::index($ctx));
    $app->get('/:id',   fn ($ctx) => ClientsController::show($ctx));
    $app->post('/',     fn ($ctx) => ClientsController::store($ctx));
    $app->put('/:id',   fn ($ctx) => ClientsController::update($ctx));
    $app->delete('/:id',fn ($ctx) => ClientsController::destroy($ctx));
});
