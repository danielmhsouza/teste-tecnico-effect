<?php

namespace App\Contracts\Services;

use App\Contracts\Services\ContractCalculatorService;
use App\Contracts\Models\ContractItemsModel;
use App\Contracts\Models\ContractsModel;
use App\Services\Models\ServicesModel;

class ContractsService
{

    public static function paginate(int $page, int $perPage): array
    {
        $page    = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset  = ($page - 1) * $perPage;
        $total   = (int) ContractsModel::count();

        if ($total === 0) {
            return [
                'data' => [],
                'meta' => ['total' => 0, 'per_page' => $perPage, 'current_page' => $page, 'last_page' => 1],
            ];
        }

        $contracts = ContractsModel::paginateWithClient($perPage, $offset);

        $contracts = self::attachItemsAndTotals($contracts);

        return [
            'data' => $contracts,
            'meta' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => (int) ceil($total / $perPage),
            ],
        ];
    }


    public static function find(int $id): ?array
    {
        $row = ContractsModel::findWithClient($id);

        if (!$row) {
            return null;
        }

        $contracts = self::attachItemsAndTotals([$row]);
        return $contracts[0];
    }


    public static function create(array $body): array
    {
        $errors = [];

        if (empty($body['client_id'])) {
            $errors[] = 'O campo client_id é obrigatório.';
        }
        if (empty($body['start_date'])) {
            $errors[] = 'O campo start_date é obrigatório.';
        }

        if ($errors) {
            return ['errors' => $errors];
        }

        $id = ContractsModel::insert([
            'client_id'  => (int) $body['client_id'],
            'start_date' => $body['start_date'],
            'end_date'   => $body['end_date'] ?? null,
            'status'     => 'active',
        ]);

        return ['data' => self::find((int) $id)];
    }


    public static function cancel(int $id): array
    {
        $contract = ContractsModel::find($id);
        if (!$contract) {
            return ['notFound' => true];
        }

        ContractsModel::update($id, ['status' => 'canceled']);

        return ['data' => self::find($id)];
    }


    public static function addItem(int $contractId, array $body): array
    {
        $contract = ContractsModel::find($contractId);
        if (!$contract) {
            return ['notFound' => true];
        }

        if ($contract['status'] === 'canceled') {
            return ['canceled' => true];
        }

        $errors = [];
        if (empty($body['service_id'])) {
            $errors[] = 'O campo service_id é obrigatório.';
        }
        if (!isset($body['quantity']) || (int) $body['quantity'] < 1) {
            $errors[] = 'O campo quantity deve ser um inteiro maior que zero.';
        }

        if ($errors) {
            return ['errors' => $errors];
        }

        $service = ServicesModel::find((int) $body['service_id']);
        if (!$service) {
            return ['errors' => ['Serviço não encontrado.']];
        }

        $unitValue = isset($body['unit_value'])
            ? (float) $body['unit_value']
            : (float) $service['base_monthly_value'];

        // Se o serviço já existe no contrato, soma a quantidade ao invés de criar nova linha
        $existing = ContractItemsModel::findWhere([
            'contract_id' => $contractId,
            'service_id'  => (int) $body['service_id'],
        ]);

        if ($existing) {
            ContractItemsModel::update($existing['id'], [
                'quantity' => $existing['quantity'] + (int) $body['quantity'],
            ]);
        } else {
            ContractItemsModel::insert([
                'contract_id' => $contractId,
                'service_id'  => (int) $body['service_id'],
                'quantity'    => (int) $body['quantity'],
                'unit_value'  => $unitValue,
            ]);
        }

        return ['data' => self::find($contractId)];
    }


    public static function removeItem(int $contractId, int $itemId): array
    {
        $contract = ContractsModel::find($contractId);
        if (!$contract) {
            return ['notFound' => true];
        }

        if ($contract['status'] === 'canceled') {
            return ['canceled' => true];
        }

        $item = ContractItemsModel::findWhere(['id' => $itemId, 'contract_id' => $contractId]);
        if (!$item) {
            return ['notFound' => true];
        }

        ContractItemsModel::delete($itemId);
        return ['deleted' => true];
    }


    private static function attachItemsAndTotals(array $contracts): array
    {
        if (empty($contracts)) {
            return [];
        }

        $contractIds = array_column($contracts, 'id');

        $items = ContractItemsModel::findByContracts($contractIds);

        $itemsByContract = [];
        foreach ($items ?: [] as $item) {
            $itemsByContract[(int) $item['contract_id']][] = $item;
        }

        $calculator = ContractCalculatorService::create();

        foreach ($contracts as &$contract) {
            $contract['items']  = $itemsByContract[(int) $contract['id']] ?? [];
            $calc               = $calculator->calculate($contract);
            $contract['base_total']    = $calc['base_total'];
            $contract['monthly_total'] = $calc['monthly_total'];
            $contract['discounts']     = $calc['discounts'];
        }
        unset($contract);

        return $contracts;
    }
}
