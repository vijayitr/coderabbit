<?php

namespace App\Http\Controllers;

use App\Models\WorkflowFieldValue;
use Illuminate\Http\Request;

class WorkflowFieldValueController extends Controller
{
    /**
     * Display a listing of the WorkflowFieldValues.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fieldValues = WorkflowFieldValue::all();
        return response()->json($fieldValues);
    }

    /**
     * Show the form for creating a new WorkflowFieldValue.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Return a view or form to create a new field value
    }

    /**
     * Store a newly created WorkflowFieldValue in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'field_id' => 'required|exists:workflow_fields,id',
            'value' => 'nullable|string',
        ]);

        $fieldValue = WorkflowFieldValue::create($validated);

        return response()->json($fieldValue, 201); // 201 Created
    }

    /**
     * Display the specified WorkflowFieldValue.
     *
     * @param  \App\Models\WorkflowFieldValue  $workflowFieldValue
     * @return \Illuminate\Http\Response
     */
    public function show(WorkflowFieldValue $workflowFieldValue)
    {
        return response()->json($workflowFieldValue);
    }

    /**
     * Show the form for editing the specified WorkflowFieldValue.
     *
     * @param  \App\Models\WorkflowFieldValue  $workflowFieldValue
     * @return \Illuminate\Http\Response
     */
    public function edit(WorkflowFieldValue $workflowFieldValue)
    {
        // Return a view or form to edit the field value
    }

    /**
     * Update the specified WorkflowFieldValue in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WorkflowFieldValue  $workflowFieldValue
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WorkflowFieldValue $workflowFieldValue)
    {
        $validated = $request->validate([
            'field_id' => 'required|exists:workflow_fields,id',
            'value' => 'nullable|string',
        ]);

        $workflowFieldValue->update($validated);

        return response()->json($workflowFieldValue);
    }

    /**
     * Remove the specified WorkflowFieldValue from storage.
     *
     * @param  \App\Models\WorkflowFieldValue  $workflowFieldValue
     * @return \Illuminate\Http\Response
     */
    public function destroy(WorkflowFieldValue $workflowFieldValue)
    {
        $workflowFieldValue->delete();

        return response()->json(['message' => 'WorkflowFieldValue deleted successfully']);
    }
}
