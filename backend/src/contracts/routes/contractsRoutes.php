<?php

use App\Contracts\Controllers\ContractsController;

$app->group('/api/contracts', function ($app) {
    $app->get('/',                         fn ($ctx) => ContractsController::index($ctx));
    $app->get('/:id',                      fn ($ctx) => ContractsController::show($ctx));
    $app->post('/',                        fn ($ctx) => ContractsController::store($ctx));
    $app->put('/:id/status',               fn ($ctx) => ContractsController::updateStatus($ctx));
    $app->post('/:id/items',               fn ($ctx) => ContractsController::addItem($ctx));
    $app->delete('/:id/items/:item_id',    fn ($ctx) => ContractsController::removeItem($ctx));
});
