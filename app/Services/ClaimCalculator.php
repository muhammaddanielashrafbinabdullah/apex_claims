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
            [$value, $terms] = self::evaluate($inputs[$row['key']] ?? 0);
            if ($value <= 0) {
                continue;
            }

            $amount = $this->amountFor($row, $value);
            $total += $amount;

            $lines[] = [
                'item_key' => $row['key'],
                'category' => $row['group'],
                'description' => $this->descriptionFor($row, $notes),
                'breakdown' => ! empty($row['sum']) && count($terms) > 1
                    ? implode(' + ', array_map(fn ($t) => number_format($t, 2, '.', ''), $terms))
                    : null,
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

    /**
     * Read an amount the claimant typed: a plain number ("12.50") or several amounts added together
     * ("1.10 + 2.40 + 3.00"). Nothing is evaluated as code: only digits, dots, '+' and spaces are accepted.
     *
     * @return array{0: float, 1: array<int, float>}  [sum, the individual amounts]
     */
    public static function evaluate(mixed $v): array
    {
        if (is_int($v) || is_float($v)) {
            return $v > 0 ? [round((float) $v, 2), [(float) $v]] : [0.0, []];
        }
        if (is_string($v) && is_numeric($v)) {
            return (float) $v > 0 ? [round((float) $v, 2), [(float) $v]] : [0.0, []];
        }
        if (! is_string($v) || ! preg_match(self::AMOUNT_PATTERN, $v)) {
            return [0.0, []];
        }

        $terms = array_map('floatval', array_map('trim', explode('+', $v)));

        return [round(array_sum($terms), 2), $terms];
    }

    /** One or more amounts separated by + (each with up to 2 decimals). */
    public const AMOUNT_PATTERN = '/^\s*\d+(\.\d{1,2})?(\s*\+\s*\d+(\.\d{1,2})?)*\s*$/';
}
