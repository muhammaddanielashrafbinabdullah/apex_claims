<?php

namespace App\Exports;

use App\Models\Claim;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Approved claims only. One row per approved claim:
 * Staff No | Staff Name | one column per claim type | Total Amount.
 * Disallowed lines are left out; a type that was not claimed shows "-".
 */
class ClaimsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    use Exportable;

    /** Excel column heading => item categories (claim_rates "group") that add up into it. */
    private const COLUMNS = [
        'Meal' => ['Meal Allowance'],
        'Lodging' => ['Lodging Allowance'],
        'Hardship' => ['Hardship (Office Work)', 'Hardship (Site Work)'],
        'Car' => ['Car Mileage'],
        'Motorcycle' => ['Motorcycle Mileage'],
        'Travelling' => ['Travelling'],
        'Entertainment' => ['Entertainment'],
        'Project Purchases' => ['Project Purchases'],
        'Accommodation' => ['Accommodation'],
        'Insurance & Roadtax' => ['Insurance & Roadtax'],
        'Vehicle Upkeep' => ['Vehicle Upkeep'],
    ];

    public function __construct(private ?int $month = null, private ?int $year = null) {}

    public function query(): Builder
    {
        return Claim::query()
            ->with('items')
            ->where('status', 'approved')
            ->forPeriod($this->month, $this->year)
            ->orderBy('submitted_at');
    }

    public function headings(): array
    {
        return array_merge(['Staff No', 'Staff Name'], array_keys(self::COLUMNS), ['Total Amount (RM)']);
    }

    /** @param  Claim  $claim */
    public function map($claim): array
    {
        $valid = $claim->items->where('disallowed', false);

        $row = [$claim->staff_no, $claim->full_name];

        foreach (self::COLUMNS as $categories) {
            $lines = $valid->whereIn('category', $categories);
            $row[] = $lines->isEmpty() ? '-' : round((float) $lines->sum('amount'), 2);
        }

        $row[] = round((float) $claim->total_amount, 2);

        return $row;
    }

    public function columnFormats(): array
    {
        $formats = [];
        // Columns C .. (C + number of types) = claim types, then the total column.
        $last = 2 + count(self::COLUMNS) + 1; // 1-based index of the Total column
        for ($i = 3; $i <= $last; $i++) {
            $formats[Coordinate::stringFromColumnIndex($i)] = NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1;
        }

        return $formats;
    }

    /** Blue header row with bold white text and filter arrows on every column. */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(count($this->headings()));
                $range = "A1:{$lastCol}1";

                $sheet->getStyle($range)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E9C']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->setAutoFilter($range);
                $sheet->freezePane('A2');

                // Dashes (no claim) are centred under their number columns.
                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 2) {
                    $sheet->getStyle("C2:{$lastCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            },
        ];
    }
}
