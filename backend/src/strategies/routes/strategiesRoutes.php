<?php

use App\Strategies\Controllers\StrategiesController;

$app->group('/api/strategies', function ($app) {
    $app->get('/',    fn ($ctx) => StrategiesController::index($ctx));
    $app->put('/:id', fn ($ctx) => StrategiesController::update($ctx));
});
