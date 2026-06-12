<?php

namespace App\Services\Controllers;

use App\Services\Models\ServicesModel;
use App\Services\Services\ServicesService;
use Crescent\Core\Context;

class ServicesController
{
    public static function index(Context $ctx): void
    {
        $page    = (int) ($ctx->query['page']     ?? 1);
        $perPage = (int) ($ctx->query['per_page'] ?? 15);

        $ctx->json(ServicesService::paginate($page, $perPage));
    }

    public static function show(Context $ctx): void
    {
        $item = ServicesModel::find((int) $ctx->params['id']);
        if (!$item) {
            $ctx->status(404)->json(['error' => 'Serviço não encontrado.']);
            return;
        }
        $ctx->json(['data' => $item]);
    }

    public static function store(Context $ctx): void
    {
        $result = ServicesService::create((array) $ctx->body);

        if (isset($result['errors'])) {
            $ctx->status(422)->json(['errors' => $result['errors']]);
            return;
        }

        $ctx->status(201)->json($result);
    }

    public static function update(Context $ctx): void
    {
        $result = ServicesService::update((int) $ctx->params['id'], (array) $ctx->body);

        if (isset($result['notFound'])) {
            $ctx->status(404)->json(['error' => 'Serviço não encontrado.']);
            return;
        }
        if (isset($result['errors'])) {
            $ctx->status(422)->json(['errors' => $result['errors']]);
            return;
        }

        $ctx->json($result);
    }

    public static function destroy(Context $ctx): void
    {
        $result = ServicesService::delete((int) $ctx->params['id']);

        if (isset($result['notFound'])) {
            $ctx->status(404)->json(['error' => 'Serviço não encontrado.']);
            return;
        }

        $ctx->noContent();
    }
}
