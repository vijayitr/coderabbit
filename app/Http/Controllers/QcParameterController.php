<?php

namespace App\Http\Controllers;

use App\Models\QcParameter;
use Illuminate\Http\Request;

class QcParameterController extends Controller
{
    public function index()
    {
        $parameters = QcParameter::orderBy('order')->get();
        return view('qc_parameters.index', compact('parameters'));
    }

    public function create()
    {
        return view('qc_parameters.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:1',
            'order' => 'nullable|integer',
            'status' => 'required|boolean',
        ]);

        QcParameter::create($request->all());

        return redirect()->route('qc-parameters.index')->with('success', 'QC Parameter created successfully.');
    }

    public function edit(QcParameter $qc_parameter)
    {
        return view('qc_parameters.edit', compact('qc_parameter'));
    }

    public function update(Request $request, QcParameter $qc_parameter)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:1',
            'order' => 'nullable|integer',
            'status' => 'required|boolean',
        ]);

        $qc_parameter->update($request->all());

        return redirect()->route('qc-parameters.index')->with('success', 'QC Parameter updated successfully.');
    }

    public function destroy(QcParameter $qc_parameter)
    {
        $qc_parameter->delete();

        return redirect()->route('qc-parameters.index')->with('success', 'QC Parameter deleted successfully.');
    }
}
