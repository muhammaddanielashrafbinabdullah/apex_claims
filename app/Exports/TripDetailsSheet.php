<?php

namespace App\Exports;

use App\Models\Claim;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/** Sheet 2: trip details of approved claims that have them, for the chosen month. */
class TripDetailsSheet implements FromArray, WithEvents, WithTitle
{
    public function __construct(private int $month, private int $year) {}

    public function title(): string
    {
        return 'Travel Description';
    }

    public function array(): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $claims = Claim::query()
                    ->where('status', 'approved')
                    ->forPeriod($this->month, $this->year)
                    ->where(fn ($q) => $q->whereNotNull('trip_from')->orWhereNotNull('trip_to')->orWhereNotNull('trip_date')->orWhereNotNull('trip_job'))
                    ->orderBy('full_name')
                    ->orderBy('trip_date')
                    ->orderBy('submitted_at')
                    ->get()
                    ->filter(fn (Claim $c) => trim((string) $c->trip_from.(string) $c->trip_to.(string) $c->trip_job) !== '' || $c->trip_date);

                ExportHelper::title($sheet, 'Travel Description ('.ExportHelper::periodLabel($this->month, $this->year).')');

                // Two header rows (4-5): "Destination" spans From | To. Table starts at column B.
                // B Full Name | C Staff No | D Date | E From | F To | G Description
                $sheet->setCellValue('B4', 'Full Name');
                $sheet->setCellValue('C4', 'Staff No');
                $sheet->setCellValue('D4', 'Date');
                $sheet->setCellValue('E4', 'Destination');
                $sheet->setCellValue('E5', 'From');
                $sheet->setCellValue('F5', 'To');
                $sheet->setCellValue('G4', 'Description');
                foreach (['B', 'C', 'D', 'G'] as $col) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                }
                $sheet->mergeCells('E4:F4');
                ExportHelper::styleHeaderRange($sheet, 'B4:G5');
                $sheet->getRowDimension(4)->setRowHeight(22);
                $sheet->getRowDimension(5)->setRowHeight(22);

                $r = 5;
                foreach ($claims->values() as $c) {
                    $r++;
                    $sheet->setCellValue("B{$r}", $c->full_name);
                    $sheet->setCellValueExplicit("C{$r}", (string) $c->staff_no, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("D{$r}", $c->trip_date ? $c->trip_date->format('d/m/Y') : '-', DataType::TYPE_STRING);
                    $sheet->setCellValue("E{$r}", $c->trip_from ?: '-');
                    $sheet->setCellValue("F{$r}", $c->trip_to ?: '-');
                    $sheet->setCellValue("G{$r}", $c->trip_job ?: '-');
                }
                $lastData = $r;

                if ($claims->isEmpty()) {
                    $r++;
                    $sheet->mergeCells("B{$r}:G{$r}");
                    $sheet->setCellValue("B{$r}", 'No trip details for this month.');
                    $lastData = $r;
                }

                ExportHelper::styleTable($sheet, "B6:G{$lastData}");
                $sheet->getStyle("B6:G{$lastData}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                $sheet->getStyle("C6:F{$lastData}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                foreach (['A' => 2, 'B' => 28, 'C' => 10, 'D' => 12, 'E' => 24, 'F' => 24, 'G' => 50] as $col => $w) {
                    $sheet->getColumnDimension($col)->setWidth($w);
                }

                $sheet->getSheetView()->setZoomScale(90);

                $page = $sheet->getPageSetup();
                $page->setOrientation('landscape');
                $page->setFitToWidth(1);
                $page->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(4, 5);
            },
        ];
    }
}
