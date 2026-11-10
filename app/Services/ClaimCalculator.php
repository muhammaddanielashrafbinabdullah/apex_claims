<?php

namespace App\Services;

/**
 * Server-side recalculation of the claim rate matrix.
 * Mirrors resources/js/claim-calculator.js, both driven by config/claim_rates.php.
 */
class ClaimCalculator
{
    /**
     * @param  array<string, mixed>  $inputs  raw staff input keyed by row key
     * @return array{lines: array<int, array>, total: float}
     */
    public function calculate(array $inputs): array
    {
        $lines = [];
        $total = 0.0;

        foreach (config('claim_rates.rows') as $row) {
            $value = $this->num($inputs[$row['key']] ?? 0);
            if ($value <= 0) {
                continue;
            }

            $amount = $this->amountFor($row, $value);
            $total += $amount;

            $lines[] = [
                'item_key' => $row['key'],
                'category' => $row['group'],
                'description' => $row['label'],
                'unit_input' => $value,
                'amount' => $amount,
            ];
        }

        return ['lines' => $lines, 'total' => round($total, 2)];
    }

    private function amountFor(array $row, float $value): float
    {
        return match ($row['type']) {
            'per_unit' => round($value * $row['rate'], 2),
            'fixed_threshold' => $value > $row['threshold'] ? (float) $row['amount'] : 0.0,
            'direct' => round($value, 2),
            default => 0.0,
        };
    }

    private function num(mixed $v): float
    {
        return is_numeric($v) && $v > 0 ? (float) $v : 0.0;
    }
}
