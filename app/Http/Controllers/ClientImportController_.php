<?php

namespace App\Http\Controllers;

use App\Imports\ClientsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Client;

class ClientImportController extends Controller
{
    /**
     * Show the form to upload the Excel file.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('Clients.create');
    }

    /**
     * Handle the import of the Excel file.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv', // Ensure it's an Excel or CSV file
        ]);

        Excel::import(new ClientsImport, $request->file('file'));

        return back()->with('success', 'Clients imported successfully.');
    }
}
