<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/** Two sheets for ONE month: the chosen filter, otherwise the current month. */
class ClaimsExport implements Export, WithMultipleSheets
{
    use Exportable;

    private int $month;
    private int $year;

    public function __construct(?int $month = null, ?int $year = null)
    {
        [$this->month, $this->year] = ExportHelper::resolvePeriod($month, $year);
    }

    public function sheets(): array
    {
        return [
            new ClaimsSummarySheet($this->month, $this->year),
            new TripDetailsSheet($this->month, $this->year),
        ];
    }
}
