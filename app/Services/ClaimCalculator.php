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
    public function calculate(array $inputs, array $notes = []): array
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
                'description' => $this->descriptionFor($row, $notes),
                'unit_input' => $value,
                'amount' => $amount,
            ];
        }

        return ['lines' => $lines, 'total' => round($total, 2)];
    }

    /** Rows for the review pop-ups: the config rows plus the old single "Travelling" row (shown only when a claim has it). */
    public static function displayRows(): array
    {
        $rows = [];
        foreach (config('claim_rates.rows') as $row) {
            if ($row['key'] === 'travelling_fuel') {
                foreach (config('claim_rates.legacy_rows', []) as $legacy) {
                    $rows[] = $legacy;
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function descriptionFor(array $row, array $notes): string
    {
        $note = trim((string) ($notes[$row['key']] ?? ''));

        return ! empty($row['text']) && $note !== '' ? 'Other: '.mb_substr($note, 0, 100) : $row['label'];
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
