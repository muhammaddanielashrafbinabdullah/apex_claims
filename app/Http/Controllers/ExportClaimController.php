<?php

namespace App\Http\Controllers;

use App\Exports\ClaimsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportClaimController extends Controller
{
    public function export(Request $request)
    {
        $data = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        [$month, $year] = \App\Exports\ExportHelper::resolvePeriod(
            isset($data['month']) ? (int) $data['month'] : null,
            isset($data['year']) ? (int) $data['year'] : null
        );

        $name = sprintf('apex-claims-%d-%02d.xlsx', $year, $month);

        return Excel::download(new ClaimsExport($month, $year), $name);
    }
}
