<?php

namespace App\Strategies\Services;

use App\Strategies\Models\StrategiesModel;

class StrategiesService
{
    public static function all(): array
    {
        return StrategiesModel::all('id ASC') ?: [];
    }

    public static function update(int $id, array $body): array
    {
        if (!StrategiesModel::find($id)) {
            return ['notFound' => true];
        }

        $errors = [];

        if (isset($body['discount_rate'])) {
            $rate = (float) $body['discount_rate'];
            if ($rate <= 0 || $rate > 1) {
                $errors[] = 'discount_rate deve ser um valor entre 0.01 e 1.00 (ex: 0.10 = 10%).';
            }
        }

        if (isset($body['threshold_value']) && (float) $body['threshold_value'] <= 0) {
            $errors[] = 'threshold_value deve ser maior que zero.';
        }

        if ($errors) {
            return ['errors' => $errors];
        }

        $fields = [];
        if (isset($body['label']))           $fields['label']           = trim($body['label']);
        if (isset($body['discount_rate']))   $fields['discount_rate']   = (float) $body['discount_rate'];
        if (isset($body['threshold_value'])) $fields['threshold_value'] = (float) $body['threshold_value'];
        if (isset($body['is_active']))       $fields['is_active']       = (int) (bool) $body['is_active'];

        if (!empty($fields)) {
            StrategiesModel::update($id, $fields);
        }

        return ['data' => StrategiesModel::find($id)];
    }
}
