<?php


namespace App\Http\Controllers\ExcelImport;

use App\Imports\DossierImport;
use Maatwebsite\Excel\Facades\Excel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class DossierImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        Excel::import(new DossierImport, $request->file('file'));

        return back()->with('success', 'Dossiers and Affaires imported successfully!');
    }
}
