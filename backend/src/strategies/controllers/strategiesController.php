<?php

namespace App\Strategies\Controllers;

use App\Strategies\Services\StrategiesService;
use Crescent\Core\Context;

class StrategiesController
{
    public static function index(Context $ctx): void
    {
        $ctx->json(['data' => StrategiesService::all()]);
    }

    public static function update(Context $ctx): void
    {
        $result = StrategiesService::update((int) $ctx->params['id'], (array) $ctx->body);

        if (isset($result['notFound'])) {
            $ctx->status(404)->json(['error' => 'Estratégia não encontrada.']);
            return;
        }
        if (isset($result['errors'])) {
            $ctx->status(422)->json(['errors' => $result['errors']]);
            return;
        }

        $ctx->json($result);
    }
}
