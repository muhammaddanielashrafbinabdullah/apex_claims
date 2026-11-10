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

        $month = $data['month'] ?? null;
        $year = $data['year'] ?? null;

        $name = 'apex-claims'
            .($year ? "-{$year}" : '')
            .($month ? sprintf('-%02d', $month) : '')
            .'.xlsx';

        return Excel::download(new ClaimsExport($month ? (int) $month : null, $year ? (int) $year : null), $name);
    }
}
