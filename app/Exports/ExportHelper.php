<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Shared pieces of the Excel export: period wording, the big header block, and the per-person summary logic.
 */
class ExportHelper
{
    /** Summary columns, in the order requested. Heading => list of item-key matchers. */
    public const COLUMNS = [
        'Meal Allowance' => ['meal_days'],
        'Fixed > 25 days' => ['site_work_days'],
        'Accommodation' => ['accommodation'],
        'Lodging Allowance' => ['lodging_*'],
        'Hardship Office' => ['hardship_office_*'],
        'Hardship Site' => ['hardship_site_*'],
        'Travelling' => ['travelling*'],
        'Mileage' => ['car_*', 'moto_*'],
        'Entertainment' => ['entertainment'],
        'Project Purchases' => ['project_purchases'],
        'Insurance' => ['insurance_roadtax'],
        'Vehicle Upkeep' => ['vehicle_upkeep'],
    ];

    public static function periodLabel(int $month, int $year): string
    {
        return date('F Y', mktime(0, 0, 0, $month, 1, $year));
    }

    /** Export month: the chosen filter, otherwise the current month (real time). */
    public static function resolvePeriod(?int $month, ?int $year): array
    {
        $now = now(config('claim_rates.timezone', 'Asia/Kuala_Lumpur'));

        return [$month ?: (int) $now->month, $year ?: (int) $now->year];
    }

    /** Which summary column an item belongs to (index into COLUMNS), or null if none. */
    public static function columnIndexFor(string $itemKey): ?int
    {
        $i = 0;
        foreach (self::COLUMNS as $matchers) {
            foreach ($matchers as $m) {
                $hit = str_ends_with($m, '*') ? str_starts_with($itemKey, substr($m, 0, -1)) : $itemKey === $m;
                if ($hit) {
                    return $i;
                }
            }
            $i++;
        }

        return null;
    }

    /**
     * One row per person (and per month), adding up their approved claims. Declined lines are skipped.
     *
     * @param  array<int, array{user_id:int, year:int, month:int, staff_no:?string, name:?string, submitted_at:int, items:array<int, array{key:string, amount:float|string, disallowed:bool}>}>  $claims
     * @return array<int, array{name:string, staff_no:string, year:int, month:int, amounts:array<int,float>, total:float}>
     */
    public static function summarise(array $claims): array
    {
        $groups = [];
        foreach ($claims as $c) {
            $k = $c['user_id'].'|'.$c['year'].'-'.str_pad((string) $c['month'], 2, '0', STR_PAD_LEFT);
            $groups[$k] ??= ['claims' => [], 'user' => $c['user_id'], 'year' => $c['year'], 'month' => $c['month']];
            $groups[$k]['claims'][] = $c;
        }

        $rows = [];
        foreach ($groups as $g) {
            // The name and staff number come from the person's most recent claim in that month.
            usort($g['claims'], fn ($a, $b) => $a['submitted_at'] <=> $b['submitted_at']);
            $latest = end($g['claims']);

            $amounts = array_fill(0, count(self::COLUMNS), 0.0);
            foreach ($g['claims'] as $c) {
                foreach ($c['items'] as $item) {
                    if ($item['disallowed']) {
                        continue;
                    }
                    $idx = self::columnIndexFor($item['key']);
                    if ($idx !== null) {
                        $amounts[$idx] += (float) $item['amount'];
                    }
                }
            }
            $amounts = array_map(fn ($v) => round($v, 2), $amounts);

            $rows[] = [
                'name' => (string) ($latest['name'] ?? ''),
                'staff_no' => (string) ($latest['staff_no'] ?? ''),
                'year' => $g['year'],
                'month' => $g['month'],
                'amounts' => $amounts,
                'total' => round(array_sum($amounts), 2),
            ];
        }

        // By month first, then name.
        usort($rows, fn ($a, $b) => [$a['year'], $a['month'], mb_strtolower($a['name'])] <=> [$b['year'], $b['month'], mb_strtolower($b['name'])]);

        return $rows;
    }

    /** One big underlined title in column B (row 2). Table starts below it. */
    public static function title(Worksheet $sheet, string $text): void
    {
        $sheet->getRowDimension(1)->setRowHeight(10);
        $sheet->getRowDimension(2)->setRowHeight(30);
        $sheet->getRowDimension(3)->setRowHeight(10);
        $sheet->setCellValue('B2', $text);
        $sheet->getStyle('B2')->applyFromArray([
            'font' => ['name' => 'Arial', 'bold' => true, 'size' => 16, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
    }

    /** Arial Narrow for the data cells (headers use Arial), thin black outline on every cell. */
    public static function styleTable(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['name' => 'Arial Narrow', 'size' => 10, 'color' => ['rgb' => '000000']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);
    }

    /** Table header cells: bold black text on light gray, centred, with black outline. */
    public static function styleHeaderRange(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['name' => 'Arial', 'bold' => true, 'size' => 10, 'color' => ['rgb' => '000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9D9D9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);
    }
}
