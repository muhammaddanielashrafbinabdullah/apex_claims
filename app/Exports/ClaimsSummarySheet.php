<?php

namespace App\Exports;

use App\Models\Claim;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/** Sheet 1: one row per person for the chosen month, approved claims added together. */
class ClaimsSummarySheet implements FromArray, WithEvents, WithTitle
{
    private const AMOUNT_FORMAT = '#,##0.00;-#,##0.00;"-"';

    public function __construct(private int $month, private int $year) {}

    public function title(): string
    {
        return 'Claims Summary';
    }

    // The sheet is drawn in registerEvents(), so there is no plain data here.
    public function array(): array
    {
        return [];
    }

    private function rows(): array
    {
        $claims = Claim::query()
            ->with('items')
            ->where('status', 'approved')
            ->forPeriod($this->month, $this->year)
            ->get()
            ->map(fn (Claim $c) => [
                'user_id' => (int) $c->user_id,
                'year' => (int) $c->claim_year,
                'month' => (int) $c->claim_month,
                'staff_no' => $c->staff_no,
                'name' => $c->full_name,
                'submitted_at' => $c->submitted_at ? $c->submitted_at->getTimestamp() : 0,
                'items' => $c->items->map(fn ($i) => [
                    'key' => $i->item_key,
                    'amount' => (float) $i->amount,
                    'disallowed' => (bool) $i->disallowed,
                ])->all(),
            ])->all();

        return ExportHelper::summarise($claims);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rows = $this->rows();

                // Table starts at column B. B = Full Name, C = Staff No, D.. = amounts, last = TOTAL.
                $headings = array_merge(['Full Name', 'Staff No'], array_keys(ExportHelper::COLUMNS), ['TOTAL']);
                $firstCol = 2;
                $firstAmountCol = 4;
                $lastCol = $firstCol + count($headings) - 1;
                $first = 'B';
                $last = Coordinate::stringFromColumnIndex($lastCol);
                $firstAmount = Coordinate::stringFromColumnIndex($firstAmountCol);
                $totalLetter = $last;

                ExportHelper::title($sheet, 'Claims Summary ('.ExportHelper::periodLabel($this->month, $this->year).')');

                // ---- header (row 4): diagonal for the amount columns, centred ----
                $headerRow = 4;
                foreach ($headings as $i => $text) {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($firstCol + $i).$headerRow, $text);
                }
                ExportHelper::styleHeaderRange($sheet, "{$first}{$headerRow}:{$last}{$headerRow}");
                $sheet->getRowDimension($headerRow)->setRowHeight(90);
                // Every heading (Full Name and Staff No too) is diagonal and centred in its cell.
                $sheet->getStyle("{$first}{$headerRow}:{$last}{$headerRow}")->getAlignment()
                    ->setTextRotation(45)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                // ---- data rows ----
                $r = $headerRow;
                foreach ($rows as $row) {
                    $r++;
                    $sheet->setCellValue("B{$r}", $row['name']);
                    $sheet->setCellValueExplicit("C{$r}", $row['staff_no'], DataType::TYPE_STRING);
                    foreach ($row['amounts'] as $i => $amount) {
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($firstAmountCol + $i).$r, $amount);
                    }
                    $sheet->setCellValue("{$totalLetter}{$r}", $row['total']);
                }
                $lastData = $r;

                if (! $rows) {
                    $r++;
                    $sheet->mergeCells("{$first}{$r}:{$last}{$r}");
                    $sheet->setCellValue("{$first}{$r}", 'No approved claims for this month.');
                    $lastData = $r;
                }

                // ---- look: Arial, outline on every cell ----
                ExportHelper::styleTable($sheet, "{$first}".($headerRow + 1).":{$last}{$lastData}");
                $sheet->getStyle("{$firstAmount}".($headerRow + 1).":{$last}{$lastData}")->getNumberFormat()->setFormatCode(self::AMOUNT_FORMAT);
                $sheet->getStyle("{$firstAmount}".($headerRow + 1).":{$last}{$lastData}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("{$totalLetter}".($headerRow + 1).":{$totalLetter}{$lastData}")->getFont()->setBold(true);

                // ---- widths: compact so the whole table fits the screen ----
                $sheet->getColumnDimension('A')->setWidth(2);
                $sheet->getColumnDimension('B')->setWidth(28);
                $sheet->getColumnDimension('C')->setWidth(10);
                for ($c = $firstAmountCol; $c < $lastCol; $c++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(10.5);
                }
                $sheet->getColumnDimension($totalLetter)->setWidth(12);

                $sheet->getSheetView()->setZoomScale(90);

                $page = $sheet->getPageSetup();
                $page->setOrientation('landscape');
                $page->setFitToWidth(1);
                $page->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($headerRow, $headerRow);
            },
        ];
    }
}
