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
    private const AMOUNT_FORMAT = '#,##0.00;-#,##0.00;[Color15]"-"';

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
                // Headings are diagonal (60 degrees) but every header cell is merged over rows 4-5,
                // which keeps the cell itself a normal rectangle. Data starts on row 6.
                $headings = array_map('mb_strtoupper', array_merge(['Full Name', 'Staff No'], array_keys(ExportHelper::COLUMNS), ['Total']));
                $firstAmountCol = 4;
                $lastCol = 1 + count($headings);
                $first = 'B';
                $last = Coordinate::stringFromColumnIndex($lastCol);
                $firstAmount = Coordinate::stringFromColumnIndex($firstAmountCol);
                $totalLetter = $last;
                $bandRow = 4;
                $headerRow = 5;
                $dataStart = 6;

                ExportHelper::title($sheet, 'Claims Summary ('.ExportHelper::periodLabel($this->month, $this->year).')');
                ExportHelper::redLine($sheet, "{$first}3:{$last}3");

                // ---- headings ----
                ExportHelper::styleHeaderRange($sheet, "{$first}{$bandRow}:{$last}{$headerRow}");
                $sheet->getStyle("{$first}{$bandRow}:{$last}{$headerRow}")->getFont()->setName('Arial')->setBold(true)->setSize(10);
                // No wrapping on tilted text: wrapping is what squeezed the words together.
                $sheet->getStyle("{$first}{$bandRow}:{$last}{$headerRow}")->getAlignment()->setWrapText(false);
                $sheet->getRowDimension($bandRow)->setRowHeight(60);
                $sheet->getRowDimension($headerRow)->setRowHeight(60);
                foreach ($headings as $i => $text) {
                    $col = Coordinate::stringFromColumnIndex(2 + $i);
                    $sheet->mergeCells("{$col}{$bandRow}:{$col}{$headerRow}");
                    $sheet->setCellValue("{$col}{$bandRow}", $text);
                    $sheet->getStyle("{$col}{$bandRow}")->getAlignment()
                        ->setTextRotation(60)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                }

                // ---- data rows ----
                $r = $dataStart - 1;
                foreach ($rows as $row) {
                    $r++;
                    $sheet->setCellValue("B{$r}", $row['name']);
                    $sheet->setCellValueExplicit("C{$r}", $row['staff_no'], DataType::TYPE_STRING);
                    foreach ($row['amounts'] as $i => $amount) {
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($firstAmountCol + $i).$r, $amount);
                    }
                    $sheet->setCellValue("{$totalLetter}{$r}", $row['total']);
                    $sheet->getRowDimension($r)->setRowHeight(20);
                }
                $lastData = $r;

                if (! $rows) {
                    $r++;
                    $sheet->mergeCells("{$first}{$r}:{$last}{$r}");
                    $sheet->setCellValue("{$first}{$r}", 'No approved claims for this month.');
                    $sheet->getRowDimension($r)->setRowHeight(20);
                    $lastData = $r;
                }

                // ---- look ----
                ExportHelper::styleTable($sheet, "{$first}{$dataStart}:{$last}{$lastData}");
                $sheet->getStyle("{$first}{$dataStart}:{$last}{$lastData}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("B{$dataStart}:B{$lastData}")->getAlignment()->setIndent(1);
                $sheet->getStyle("C{$dataStart}:C{$lastData}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $amountRange = "{$firstAmount}{$dataStart}:{$last}{$lastData}";
                $sheet->getStyle($amountRange)->getNumberFormat()->setFormatCode(self::AMOUNT_FORMAT);
                $sheet->getStyle($amountRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setIndent(1);

                // Light stripes on every second row (not on the TOTAL column).
                if ($rows) {
                    for ($i = $dataStart + 1; $i <= $lastData; $i += 2) {
                        ExportHelper::stripe($sheet, "{$first}{$i}:{$last}{$i}");
                    }
                }

                // TOTAL column (each person's own total): bold, plain.
                $sheet->getStyle("{$totalLetter}{$dataStart}:{$totalLetter}{$lastData}")->getFont()->setBold(true);

                // Strong navy frame around the whole table.
                ExportHelper::outerFrame($sheet, "{$first}{$bandRow}:{$last}{$lastData}");

                // ---- widths, view, print ----
                $sheet->getColumnDimension('A')->setWidth(2);
                $sheet->getColumnDimension('B')->setWidth(26);
                $sheet->getColumnDimension('C')->setWidth(12);
                for ($col = $firstAmountCol; $col < $lastCol; $col++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setWidth(17);
                }
                $sheet->getColumnDimension($totalLetter)->setWidth(13);

                $sheet->getTabColor()->setRGB(ExportHelper::NAVY);
                ExportHelper::printFooter($sheet);

                $page = $sheet->getPageSetup();
                $page->setOrientation('landscape');
                $page->setFitToWidth(1);
                $page->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($bandRow, $headerRow);
            },
        ];
    }
}
