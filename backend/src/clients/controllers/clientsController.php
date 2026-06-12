<?php

namespace App\Clients\Controllers;

use App\Clients\Models\ClientsModel;
use App\Clients\Services\ClientsService;
use Crescent\Core\Context;

class ClientsController
{
    public static function index(Context $ctx): void
    {
        $page    = (int) ($ctx->query['page']    ?? 1);
        $perPage = (int) ($ctx->query['per_page'] ?? 15);

        $ctx->json(ClientsService::paginate($page, $perPage));
    }

    public static function show(Context $ctx): void
    {
        $item = ClientsModel::find((int) $ctx->params['id']);
        if (!$item) {
            $ctx->status(404)->json(['error' => 'Cliente não encontrado.']);
            return;
        }
        $ctx->json(['data' => $item]);
    }

    public static function store(Context $ctx): void
    {
        $result = ClientsService::create((array) $ctx->body);

        if (isset($result['errors'])) {
            $ctx->status(422)->json(['errors' => $result['errors']]);
            return;
        }

        $ctx->status(201)->json($result);
    }

    public static function update(Context $ctx): void
    {
        $result = ClientsService::update((int) $ctx->params['id'], (array) $ctx->body);

        if (isset($result['notFound'])) {
            $ctx->status(404)->json(['error' => 'Cliente não encontrado.']);
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
        $result = ClientsService::delete((int) $ctx->params['id']);

        if (isset($result['notFound'])) {
            $ctx->status(404)->json(['error' => 'Cliente não encontrado.']);
            return;
        }

        $ctx->noContent();
    }
}
