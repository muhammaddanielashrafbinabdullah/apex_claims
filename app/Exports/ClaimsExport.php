<?php

namespace App\Exports;

use App\Models\Claim;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ClaimsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    public function __construct(private ?int $month = null, private ?int $year = null) {}

    public function query(): Builder
    {
        return Claim::query()
            ->with('items')
            ->forPeriod($this->month, $this->year)
            ->orderBy('submitted_at');
    }

    public function headings(): array
    {
        return [
            'Claim ID', 'Staff Name', 'Staff No', 'Department', 'Bank & Account No',
            'Categories', 'Month/Year', 'Date Submitted', 'Total (RM)', 'Status', 'Rejection Reason',
        ];
    }

    /** @param  Claim  $claim */
    public function map($claim): array
    {
        return [
            $claim->claim_number,
            $claim->full_name,
            $claim->staff_no,
            $claim->department,
            $claim->bank_account,
            $claim->categories_list,
            sprintf('%02d/%d', $claim->claim_month, $claim->claim_year),
            $claim->submitted_at->timezone(config('claim_rates.timezone'))->format('d/m/Y'),
            (float) $claim->total_amount,
            ucfirst($claim->status),
            $claim->rejection_reason,
        ];
    }

    public function columnFormats(): array
    {
        return ['I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1];
    }
}
