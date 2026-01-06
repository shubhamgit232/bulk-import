<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ProcessProductCsv;

class ProductImportController extends Controller
{
    public function index()
    {
        return view('import.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv' => 'required|mimes:csv,txt|max:10240'
        ]);

        $path = $request->file('csv')->store('imports');

        ProcessProductCsv::dispatch($path);

        return back()->with('success', 'CSV import started successfully');
    }
}
