<?php

namespace App\Contracts\Controllers;

use App\Contracts\Services\ContractsService;
use Crescent\Core\Context;

class ContractsController
{
    public static function index(Context $ctx): void
    {
        $page    = (int) ($ctx->query['page']     ?? 1);
        $perPage = (int) ($ctx->query['per_page'] ?? 15);

        $ctx->json(ContractsService::paginate($page, $perPage));
    }

    public static function show(Context $ctx): void
    {
        $contract = ContractsService::find((int) $ctx->params['id']);
        if (!$contract) {
            $ctx->status(404)->json(['error' => 'Contrato não encontrado.']);
            return;
        }
        $ctx->json(['data' => $contract]);
    }

    public static function store(Context $ctx): void
    {
        $result = ContractsService::create((array) $ctx->body);

        if (isset($result['errors'])) {
            $ctx->status(422)->json(['errors' => $result['errors']]);
            return;
        }

        $ctx->status(201)->json($result);
    }

    public static function updateStatus(Context $ctx): void
    {
        $result = ContractsService::cancel((int) $ctx->params['id']);

        if (isset($result['notFound'])) {
            $ctx->status(404)->json(['error' => 'Contrato não encontrado.']);
            return;
        }

        $ctx->json($result);
    }

    public static function addItem(Context $ctx): void
    {
        $result = ContractsService::addItem((int) $ctx->params['id'], (array) $ctx->body);

        if (isset($result['notFound'])) {
            $ctx->status(404)->json(['error' => 'Contrato não encontrado.']);
            return;
        }
        if (isset($result['canceled'])) {
            $ctx->status(409)->json(['error' => 'Não é possível adicionar itens a um contrato cancelado.']);
            return;
        }
        if (isset($result['errors'])) {
            $ctx->status(422)->json(['errors' => $result['errors']]);
            return;
        }

        $ctx->status(201)->json($result);
    }

    public static function removeItem(Context $ctx): void
    {
        $result = ContractsService::removeItem(
            (int) $ctx->params['id'],
            (int) $ctx->params['item_id']
        );

        if (isset($result['notFound'])) {
            $ctx->status(404)->json(['error' => 'Item não encontrado.']);
            return;
        }
        if (isset($result['canceled'])) {
            $ctx->status(409)->json(['error' => 'Não é possível remover itens de um contrato cancelado.']);
            return;
        }

        $ctx->noContent();
    }
}
