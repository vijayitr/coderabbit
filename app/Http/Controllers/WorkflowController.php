<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Client;
use App\Models\Workflow;
use App\Exports\CsvExport;
use Illuminate\Http\Request;
use App\Models\WorkflowField;
use App\Models\GlobleQcField;
use App\Models\ActivityScript;
use App\Imports\WorkflowImport;
use App\Models\WorkflowQcField;
use Illuminate\Http\JsonResponse;
use App\Models\ProcessQcFieldValue;
use App\Models\GlobleQcFieldOption;
use App\Models\WorkflowProcessName;
use App\Models\WorkflowFieldOption;
use App\Models\ProcessNameChecklist;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\WorkflowQcFieldOption;
use Illuminate\Support\Facades\DB;

class WorkflowController extends Controller
{
    public $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth()->guard('web')->user();
            return $next($request);
        });
    }

    public function index(){
        if (is_null($this->user) || !$this->user->can('workflow.view')) {
            abort(403, 'Sorry !! You are Unauthorized to view any workflow !');
        }
        $workflows = Workflow::select('workflow_name','id','client_id')->distinct()->orderBy('workflow_name')->get();
        $clients = Client::select('id', 'client_name')->orderBy('client_name')->get();
        return view('Workflows.index', compact('workflows', 'clients'));
    }

    public function getAllWorkflows($id): JsonResponse
    {
        try {
            $workflows = Workflow::with(['processNames'])
            ->when($this->user->id != 1, function ($query) {
                $query->whereNotNull('status');
            })
            ->where('client_id', $id)
            ->get();

            return response()->json([
                'status' => 'success',
                'data' => $workflows,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getWorkflows(Request $request) {
        // Input parameters for pagination and filtering
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');
        $workflow_name = $request->input('workflow_name');
        $client_id = $request->input('client_id');
        $dateFilter = $request->input('date_filter', 'Show All');
        $sortBy = $request->input('sort_by', 'Date Added');
        $draw = $request->input('draw', 1);

        // Base query for workflows with relationships
        $query = Workflow::with(['user', 'client', 'workflowProcessNames'])->select('workflows.id', 'workflows.workflow_name', 'workflows.created_at', 'workflows.created_by',  'workflows.status', 'workflows.client_id') 
            ->when($client_id != '', fn($q) => $q->where('workflows.client_id', $client_id))
            ->when($workflow_name != '', fn($q) => $q->where('workflows.id', $workflow_name));

        // Search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('workflows.workflow_name', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('users.name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Date filter
        // if ($dateFilter !== 'Show All') {
        //     $query->whereBetween('workflows.created_at', $this->getDateRange($dateFilter));
        // }

        // Sorting logic
        switch ($sortBy) {
            case 'Z to A':
                $query->orderBy('workflows.workflow_name', 'desc');
                break;
            case 'Date Added':
                $query->orderBy('workflows.created_at', 'asc');
                break;
            default:
                $query->orderBy('workflows.created_at', 'desc'); // Default sorting
                break;
        }

        // Total records count before filtering
        $totalRecords = Workflow::count();

        // Filtered records count
        $totalFilteredRecords = $query->count();

        // Paginate the results
        $workflows = $query->skip($start)->take($length)->get();
        // echo '<pre>'; dd($workflows); die;
        // Format the results
        $formattedWorkflows = $workflows->map(function ($workflow) {
            $qc_enabled = $workflow->workflowProcessNames->where('qc_enabled', 1);
            return [
                'formatted_id' => sprintf("%05d", $workflow->id),
                'id' => $workflow->id,
                'workflow_name' => $workflow->workflow_name,
                'client_name' => $workflow->client->client_name,
                'created_at' => Carbon::parse($workflow->created_at)->format('g:i A, d M Y'),
                'status' => $workflow->status ? 'Enabled' : 'Disabled',
                'qc_enabled' => $qc_enabled->isNotEmpty(),
                'created_by' => optional($workflow->user)->name
            ];
        });

        // Return response as JSON
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFilteredRecords,
            'data' => $formattedWorkflows
        ]);
    }

    private function getDateRange($filter)
    {
        switch ($filter) {
            case 'Last Week':
                return [now()->subWeek()->startOfDay(), now()->endOfDay()];
            case 'Last Month':
                return [now()->subMonth()->startOfDay(), now()->endOfDay()];
            case 'Last Year':
                return [now()->subYear()->startOfDay(), now()->endOfDay()];
            default:
                return [now()->startOfDay(), now()->endOfDay()];
        }
    }

    public function store(Request $request) {
        $row = $request->all();
        if (!isset($row['workflow_name'], $row['client_id'])) {
            return;
        }

        $client = Client::find($row['client_id']);
        if (!$client) {
            return;
        }

        // Insert or update Workflow Data with client_id and other details
        $workflow = Workflow::updateOrCreate(
            [
                'workflow_name' => (string)$row['workflow_name'],
                'client_id'     => $client->id,
            ],
            [
                'status'    => $row['status'] ?? 1,
                'created_by' => $this->user->id
            ]
        );

        // Handle Workflow Fields
        if (isset($row['fields'])) {
            foreach ($row['fields'] as $fieldId => $fieldData) {
                if (isset($fieldData['field_name'], $fieldData['field_type'])) {
                    $fieldType = strtolower(trim($fieldData['field_type']));
                    $is_primary = $request->get('primary_field') == $fieldId ? '1' : '0';

                    // Validate field type
                    if (!in_array($fieldType, ['text', 'dropdown', 'date', 'checkbox'])) {
                        continue; // Skip invalid field types
                    }

                    // Create or update the workflow field
                    $field = WorkflowField::updateOrCreate(
                        [
                            'workflow_id' => $workflow->id,
                            'field_name'  => (string)$fieldData['field_name'],
                        ],
                        [
                            'field_type'  => $fieldType,
                            'is_primary' => $is_primary
                        ]
                    );

                    // Handle field options (for dropdown fields)
                    if (isset($fieldData['options'])) {
                        foreach ($fieldData['options'] as $option) {
                            if (!empty(trim($option))) {
                                WorkflowFieldOption::create([
                                    'workflow_field_id' => $field->id,
                                    'option_value'       => (string)$option,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // Handle Process Names
        if (isset($row['process_names'])) {
            foreach ($row['process_names'] as $processName) {
                if (!empty($processName)) {
                    WorkflowProcessName::firstOrCreate([
                        'workflow_id'  => $workflow->id,
                        'process_name' => (string)$processName,
                    ]);
                }
            }
        }

        return redirect()->route('workflows.index')->with('success', 'Data Created Successfully');
    }
    
    public function create()
    {
        if (is_null($this->user) || !$this->user->can('workflow.create')) {
            abort(403, 'Sorry !! You are Unauthorized to create any workflow !');
        }
        $clients = Client::select('id', 'client_name')->where('status', '1')->get();
        $workflow = $this->empty_data();
        return view('Workflows.edit', compact('workflow','clients'));
    }

    public function importForm()
    {
        if (is_null($this->user) || !$this->user->can('workflow.create')) {
            abort(403, 'Sorry !! You are Unauthorized to import any workflow !');
        }
        return view('Workflows.create');
    }

    public function import(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('workflow.create')) {
            abort(403, 'Sorry !! You are Unauthorized to create any workflow !');
        }
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        $userId = auth()->id();

        Excel::import(new WorkflowImport($userId), $file);

        return redirect()->route('workflows.index')->with('success', 'Data Imported Successfully');
    }

    public function edit($id)
    {
        // Fetch the workflow with its related fields, options, and process names
        $clients = Client::select('id', 'client_name')->where('status', '1')->get();
        $workflow = Workflow::with([
            'fields' => function ($query) {
                $query->orderBy('order', 'asc');
            },
            'fields.options' => function ($query) {
                $query->orderBy('option_order', 'asc');
            },
            'processNames'
        ])->findOrFail($id);

        $workflow = $this->empty_data($workflow);
        return view('Workflows.edit', compact('workflow','clients'));
    }

    public function empty_data($workflow = []) {
        // Ensure $workflow is always an object
        $workflow = is_array($workflow) ? (object) $workflow : $workflow;

        // Provide dummy data if fields or processNames are empty
        $workflow->fields = !empty($workflow->fields) && $workflow->fields->isNotEmpty()
            ? $workflow->fields
            : collect([
                (object)[
                    'id' => 'new_123',
                    'field_name' => '',
                    'field_type' => 'text',
                    'options' => collect([
                        (object)[
                            'id' => 'new_123',
                            'option_value' => ''
                        ]
                    ])
                ]
            ]);

        $workflow->processNames = !empty($workflow->processNames) && $workflow->processNames->isNotEmpty()
            ? $workflow->processNames
            : collect([
                (object)[
                    'id' => 'new_123',
                    'process_name' => ''
                ]
            ]);

        // Ensure all fields have options, providing dummy data if necessary
        $workflow->fields = $workflow->fields->map(function ($field) {
            $field->options = !empty($field->options) && $field->options->isNotEmpty()
                ? $field->options
                : collect([
                    (object)[
                        'id' => 'new_123',
                        'option_value' => ''
                    ]
                ]);
            return $field;
        });

        return $workflow;
    }



    public function update(Request $request, $id)
    {
        if (is_null($this->user) || !$this->user->can('workflow.edit')) {
            abort(403, 'Sorry !! You are Unauthorized to update the workflow!');
        }

        $workflow = Workflow::with(['fields', 'fields.options', 'processNames'])->findOrFail($id);

        // Update workflow details
        $workflow->update([
            'workflow_name' => $request->input('workflow_name'),
            'client_id' => $request->input('client_id'),
            'status' => $request->input('status'),
        ]);

        $fields = $request->input('fields', []);
        $fieldIds = $workflow->fields->pluck('id')->toArray();

        $ordering = 1;
        foreach ($fields as $fieldId => $fieldData) {
            $is_primary = $request->get('primary_field') == $fieldId ? '1' : '0';
            if (str_starts_with($fieldId, 'new_')) {
                $this->createNewField($workflow, $fieldData, $is_primary, $ordering);
            } else {
                $this->updateExistingField($workflow, $fieldId, $fieldData, $fieldIds, $is_primary, $ordering);
            }
            $ordering++;
        }

        $workflow->fields()->whereIn('id', $fieldIds)->delete();
        $this->updateProcessNames($workflow, $request->input('process_names', []));

        return redirect()->route('workflows.index')->with('success', 'Workflow updated successfully.');
    }

    private function createNewField($workflow, $fieldData, $is_primary, $ordering)
    {
        $newField = $workflow->fields()->create([
            'field_name' => $fieldData['field_name'],
            'field_type' => $fieldData['field_type'],
            'is_primary' => $is_primary,
            'order' => $ordering
        ]);

        // Add new options
        if (!empty($fieldData['options']) && $fieldData['field_type'] == 'dropdown') {
            $option_ordering = 1;
            foreach ($fieldData['options'] as $optionValue) {
                if (!empty($optionValue)) {
                    $newField->options()->create(['option_value' => $optionValue, 'option_order' => $option_ordering]);
                }
                $option_ordering++;
            }
        }
    }

    private function updateExistingField($workflow, $fieldId, $fieldData, &$fieldIds, $is_primary, $ordering)
    {
        $field = $workflow->fields()->findOrFail($fieldId);

        // Update field details
        $field->update([
            'field_name' => $fieldData['field_name'],
            'field_type' => $fieldData['field_type'],
            'is_primary' => $is_primary,
            'order' => $ordering
        ]);

        // Handle options
        if ( $fieldData['field_type'] == 'dropdown') {
            $this->updateFieldOptions($field, $fieldData['options']);
        }

        // Remove this field ID from the fieldIds array
        $fieldIds = array_diff($fieldIds, [$fieldId]);
    }

    private function updateFieldOptions($field, $options)
    {
        $optionIds = $field->options->pluck('id')->toArray();
        $option_ordering = 1;
        foreach ($options as $optionId => $optionValue) {
            if (str_starts_with($optionId, 'new_')) {
                // Add new option
                if (!empty($optionValue)) {
                    $field->options()->create(['option_value' => $optionValue, 'option_order' => $option_ordering]);
                }
            } else {
                // Remove option from the list if it's not part of the update
                $optionIds = array_diff($optionIds, [$optionId]);

                // Update existing option
                $option = $field->options()->find($optionId);
                if ($option) {
                    if (!empty($optionValue)) {
                        $option->update(['option_value' => $optionValue, 'option_order' => $option_ordering]);
                    } else {
                        $option->delete();
                    }
                }
            }
            $option_ordering++;
        }

        // Delete options that are not in the request
        $field->options()->whereIn('id', $optionIds)->delete();
    }

    private function updateProcessNames($workflow, $processNames)
    {
        $processIds = $workflow->processNames->pluck('id')->toArray();
        $processNames = array_filter($processNames);

        // Add new process names or update existing ones
        foreach ($processNames as $processId => $processName) {
            if (str_starts_with($processId, 'new_')) {
                // Add new process name
                if (!empty($processName)) {
                    $workflow->processNames()->create(['process_name' => $processName]);
                }
            } else {
                // Update or create existing process name
                $processIds = array_diff($processIds, [$processId]);
                if (!empty($processName)) {
                    $existingProcess = $workflow->processNames()->find($processId);
                    if ($existingProcess) {
                        $existingProcess->update(['process_name' => $processName]);
                    }
                }
            }
        }
        $workflow->processNames()->whereIn('id', $processIds)->delete();
    }

    public function destroy($id)
    {
        $workflow = Workflow::findOrFail($id);
        $workflow->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Workflow deleted successfully!'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:workflows,id',
        ]);
        Workflow::destroy($validated['ids']);

        return response()->json([
            'status' => 'success',
            'message' => 'Selected workflows deleted successfully!'
        ]);
    }

    public function getWorkflowsFields(Request $request)
    {
        try {
            $id = $request->get('id');
            $user = auth()->user();
            $userRoleIds = $user->roles->pluck('id')->toArray();
            $isSuperAdmin = in_array(1, $userRoleIds);
            if ($id == '') {
                $fieldsHtml = '';
            } else {
                // Fetch workflow with fields and options
                $workflow = Workflow::with([
                    'fields' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                    'fields.options' => function ($query) {
                        $query->orderBy('option_order', 'asc');
                    },
                ])
                ->whereHas('processNames', function ($query) use ($id) {
                    $query->where('id', $id);
                })
                ->firstOrFail();
                // Generate HTML for the fields
                $fieldsHtml = '';
                $qcEnabled = WorkflowProcessName::where('id', $id)->value('qc_enabled');
                foreach ($workflow->fields as $field) {
                    $required_field = '';
                    $fieldIsCheckbox = $field->field_type == 'checkbox';
                    $checkboxClass = $fieldIsCheckbox ? 'opacity-0' : '';
                    $fieldsHtml .= '<div class="col-md-3 w-field">';
                    $fieldsHtml .= '<label class="form-label '.$checkboxClass.'">' . html_entity_decode($field->field_name);
                    if ($field->is_primary) {
                        $fieldsHtml .= '<i class="bi bi-check2-circle text-success ms-1"></i>';
                        $required_field = 'required';
                    }
                        $fieldsHtml .= '<img class="ms-1 copy_field_content cursor-pointer d-none float-end" src="/images/copy.svg" alt="copy content" title="Copy">';
                    $fieldsHtml .=  '</label>';

                    // Add options if available
                    if ($field->options->isNotEmpty()) {
                        $fieldsHtml .= '<select class="form-control workflow_dropdown" name="fields['.$field->id.']" '.$required_field.'>';
                        $fieldsHtml .= '<option value="">Select</option>';
                        foreach ($field->options as $option) {
                            $fieldsHtml .= '<option value="' . htmlspecialchars($option->id) . '">' . htmlspecialchars($option->option_value) . '</option>';
                        }
                        $fieldsHtml .= '</select>';
                    } else {
                        $datepicker = $field->field_type == 'date' ? 'datepicker' : '';
                        if ($fieldIsCheckbox) {
                             $fieldsHtml .= '<div><input type="checkbox" name="fields['.$field->id.']" class="form-check-input table-item-checkbox mt-0 me-2" value="" '.$required_field.'><label class="form-label mb-0">' . html_entity_decode($field->field_name) . '</label></div>';
                        } else {
                             $fieldsHtml .= '<input type="text" class="form-control '.$datepicker.'" name="fields['.$field->id.']" '.$required_field.'/>';
                        }
                       
                    }

                    $fieldsHtml .= '</div>';
                }
                if (!is_null($this->user) && ($this->user->can('time_entry.quality_check') || $isSuperAdmin)) {
                    // Qc fields
                    $qc_fields = WorkflowQcField::with(['options' => function ($query) {
                        $query->orderBy('order', 'asc');
                    }])
                    ->where('process_id',$id)
                    ->orderBy('order', 'asc')
                    ->get();
                    if ($qc_fields->count()) {
                        $qc_data = ProcessQcFieldValue::where(
                            [
                                'process_id' => $id,
                                'assignment_id' => '',
                            ]
                        )->get();

                        // Global Qc Fields
                        $globalQCFields = collect();
                        if ($qcEnabled) {
                            $globalQCFields = GlobleQcField::with('options')->get();
                        }
                        $allQcFields = $globalQCFields->merge($qc_fields);
                        $fieldsHtml .= '<div class="col-md-12"> <h5>Qc Fields</h5></div>';
                        $fieldsHtml .= view('TimeEntry.qc_fields', ['qc_fields' => $allQcFields, 'qc_data' => $qc_data])->render();
                    }
                }
            }


            return response()->json([
                'status' => 'success',
                'html' => $fieldsHtml,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getChecklistItems(Request $request) {

        try {
            $checklistItems = ProcessNameChecklist::where('process_id', $request->id)->get();
            $html = view('Workflows.process_checklist_items', [
                'items' => $checklistItems,
                'id' => $request->id
            ])->render();

             return response()->json([
                'status' => true,
                'message' => 'success',
                'html' => $html,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'An error occurred while fetching allocated users',
                'message' => $e->getMessage(),
                'html' => '',
            ], 500);
        }
    }

    public function getScript(Request $request) {
        try {
            $script = ActivityScript::where('activity_id', $request->id)->value('script') ?? '';

            return response()->json([
                'status' => true,
                'message' => 'success',
                'html' => $script,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'An error occurred while fetching allocated users',
                'message' => $e->getMessage(),
                'html' => '',
            ], 500);
        }
    }

    public function storeScript(Request $request, $id) {
        try {
            $validated = $request->validate([
                'script' => 'required|string',
            ]);

            ActivityScript::updateOrCreate(
                ['activity_id' => $id],
                ['script' => $request->input('script')]
            );

            return response()->json([
                'status' => true,
                'message' => 'Script submited successfully!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'An error occurred while submiting the script.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeChecklistItems(Request $request, $id) {
        try {
            $validated = $request->validate([
                'process_item' => 'required|array',
            ]);

            $existingItemsIds = ProcessNameChecklist::where('process_id', $id)->pluck('id')->toArray();
            if ($request->has('process_item.new')) {
                $insertData = array_filter($request->input('process_item.new'), function($item) {
                    return !empty($item);
                });

                $insertData = array_map(function($item) use ($id) {
                    return ['process_id' => $id, 'item' => $item];
                }, $insertData);
                
                ProcessNameChecklist::insert($insertData);
            }

            foreach ($request->input('process_item') as $key => $item) {
                if ($key == 'new') continue;

                if (!empty($item)) {
                    ProcessNameChecklist::where('id', $key)->where('process_id', $id)->update(['item' => $item]);
                } else {
                    ProcessNameChecklist::where('id', $key)->where('process_id', $id)->delete();
                }
            }

            $requestItemIds = array_merge(array_keys($request->input('process_item')), array_keys($request->input('process_item.new', [])));
            $itemsToDelete = array_diff($existingItemsIds, $requestItemIds);

            if (!empty($itemsToDelete)) {
                ProcessNameChecklist::whereIn('id', $itemsToDelete)->where('process_id', $id)->delete();
            }

             return response()->json([
                'status' => true,
                'message' => 'Checklist items saved/updated successfully!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'An error occurred while fetching allocated users',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function taskSampleExport($id){
        $workflow = Workflow::with('client')->findOrFail($id);
        $client_name = str_replace(' ', '_', $workflow->client->client_name); // Sanitize filename
        $workflowName = $client_name.str_replace(' ', '_', $workflow->workflow_name); // Sanitize filename
        $fields = WorkflowField::with('workflow')->where('workflow_id', $id)->pluck('field_name')->toArray();
        return Excel::download(new CsvExport([$fields]), $workflowName.'.xlsx');
    }

    function duplicateWorkflow($workflowId)
    {
        // $workflowId, $createdBy
        $createdBy = auth()->id();
        DB::beginTransaction();

        try {
            // Fetch the original workflow
            $originalWorkflow = Workflow::with(['fields.options', 'processNames'])->findOrFail($workflowId);

            // Clone the workflow
            $newWorkflow = $originalWorkflow->replicate();
            $newWorkflow->workflow_name = $originalWorkflow->workflow_name . ' (Copy)';
            $newWorkflow->created_by = $createdBy; // Assign to new creator if needed
            $newWorkflow->push(); // Save the workflow to get the ID

            // Clone fields
            foreach ($originalWorkflow->fields as $field) {
                $newField = $field->replicate();
                $newField->workflow_id = $newWorkflow->id;
                $newField->push();

                // Clone options for each field
                foreach ($field->options as $option) {
                    $newOption = $option->replicate();
                    $newOption->workflow_field_id = $newField->id;
                    $newOption->save();
                }
            }

            // Clone process names
            foreach ($originalWorkflow->processNames as $process) {
                $newProcess = $process->replicate();
                $newProcess->workflow_id = $newWorkflow->id;
                $newProcess->save();
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Workflow duplicated successfully.',
                'new_workflow_id' => $newWorkflow->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to duplicate workflow: ' . $e->getMessage(),
            ]);
        }
    }

    public function getGlobleQCData() {
        $fields = GlobleQcField::with(['options' => function ($query) {
                $query->orderBy('order', 'asc');
            }])
            ->orderBy('order', 'asc')
            ->get();
        return $fields;
    }

    public function getGlobleQCFields() {
        try {
            $fields = $this->getGlobleQCData();
            if ($fields->isEmpty()) {
                $fields = collect([
                    (object)[
                        'id' => 'new_123',
                        'field_name' => '',
                        'field_type' => 'text',
                        'order' => 1,
                        'options' => collect()
                    ]
                ]);
            }
            $options = [(object)[
                        'id' => 'new_123',
                        'option_text' => '',
                        'order' => 1,
                    ]];
            $submit_url = route('workflows.globleQCstore');
            
            $html = view('Workflows.globle_module', [ 
                'fields' => $fields,
                'options' => $options,
                'submit_url' => $submit_url
                ])->render();

             return response()->json([
                'status' => true,
                'message' => 'success',
                'html' => $html,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'An error occurred while fetching allocated users',
                'message' => $e->getMessage(),
                'html' => '',
            ], 500);
        }
    }

    public function getProcessQCFields($id) {
        try {

            $qc_status = WorkflowProcessName::where('id', $id)
                ->where('qc_enabled', 1)
                ->exists();

            $fields = WorkflowQcField::with(['options' => function ($query) {
                $query->orderBy('order', 'asc');
            }])
            ->where('process_id',$id)
            ->orderBy('order', 'asc')
            ->get();
            
            if ($fields->isEmpty()) {
                $fields = collect([
                    (object)[
                        'id' => 'new_123',
                        'workflow_id' => $id,
                        'field_name' => '',
                        'field_type' => 'text',
                        'order' => 1,
                        'options' => collect()
                    ]
                ]);
            }
            $options = [(object)[
                        'id' => 'new_123',
                        'option_text' => '',
                        'order' => 1,
                    ]];
            $submit_url = route('workflows.workflowQCstore',$id);
            $global_qc_field = $this->getGlobleQCData();
            // dd($global_qc_field);
            $html = view('Workflows.globle_module', [ 
                'fields' => $fields,
                'options' => $options,
                'process_id' => $id,
                'title' => 'Process QC Fields',
                'submit_url' => $submit_url,
                'global_qc_field' => $global_qc_field,
                'qc_status' => $qc_status,
                ])->render();

             return response()->json([
                'status' => true,
                'message' => 'success',
                'html' => $html,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'An error occurred while fetching allocated users',
                'message' => $e->getMessage(),
                'html' => '',
            ], 500);
        }
    }

    function globleQCstore(Request $request) {
        // pred($request->all());
        $fields = $request->input('fields', []);
        $ordering = 1;

        // Step 1: Get all existing field IDs from DB
        $existingFieldIds = GlobleQcField::pluck('id')->toArray();

        // Step 2: Get field IDs from request (excluding 'new_')
        $requestedFieldIds = [];
        foreach (array_keys($fields) as $fieldId) {
            if (!str_starts_with($fieldId, 'new_')) {
                $requestedFieldIds[] = (int) $fieldId;
            }
        }

        // Step 3: Find fields that need to be deleted
        $fieldsToDelete = array_diff($existingFieldIds, $requestedFieldIds);

        // Step 4: Delete them (along with related options if needed)
        GlobleQcField::whereIn('id', $fieldsToDelete)->each(function ($field) {
            $field->options()->delete();
            $field->delete();
        });

        foreach ($fields as $fieldId =>  $fieldData) {
            if (str_starts_with($fieldId, 'new_')) {
                // Create new field
                $field = GlobleQcField::create([
                    'field_name' => $fieldData['field_name'],
                    'field_type' => $fieldData['field_type'],
                    'order' => $ordering,
                ]);

                // Add options if any
                if (!empty($fieldData['options'])) {
                    $status_ordering = 1;
                    foreach ($fieldData['options'] as $optionText) {
                        if (trim($optionText) !== '') {
                            $field->options()->create([
                                'option_text' => $optionText,
                                'order' => $status_ordering
                            ]);
                        }
                        $status_ordering++;
                    }
                }
            } else {
                // Update existing field
                $field = GlobleQcField::find($fieldId);
                if ($field) {
                    $field->update([
                        'field_name' => $fieldData['field_name'],
                        'field_type' => $fieldData['field_type'],
                        'order' => $ordering
                    ]);

                    // Remove old options
                    $field->options()->delete();

                    // Add new options
                    if (!empty($fieldData['options'])) {
                        $status_ordering = 1;
                        foreach ($fieldData['options'] as $optionText) {
                            if (trim($optionText) !== '') {
                                $field->options()->create([
                                    'option_text' => $optionText,
                                    'order' => $status_ordering
                                ]);
                            }
                            $status_ordering++;
                        }
                    }
                }
            }
            $ordering++;
        }

        return redirect()->back()->with('success', 'Global QC Fields saved/updated successfully.');
    }

    function workflowQCstore(Request $request, $id) {
        // pred($id);
        // pred($request->all());
        $fields = $request->input('fields', []);
        $ordering = 1;

        // Step 1: Get all existing field IDs from DB
        $existingFieldIds = WorkflowQcField::where('process_id',$id)->pluck('id')->toArray();

        // Step 2: Get field IDs from request (excluding 'new_')
        $requestedFieldIds = [];
        foreach (array_keys($fields) as $fieldId) {
            if (!str_starts_with($fieldId, 'new_')) {
                $requestedFieldIds[] = (int) $fieldId;
            }
        }

        // Step 3: Find fields that need to be deleted
        $fieldsToDelete = array_diff($existingFieldIds, $requestedFieldIds);

        // Step 4: Delete them (along with related options if needed)
        WorkflowQcField::whereIn('id', $fieldsToDelete)->each(function ($field) {
            $field->options()->delete();
            $field->delete();
        });

        foreach ($fields as $fieldId =>  $fieldData) {
            if (str_starts_with($fieldId, 'new_')) {
                // Create new field
                $field = WorkflowQcField::create([
                    'process_id' => $id,
                    'field_name' => $fieldData['field_name'],
                    'field_type' => $fieldData['field_type'],
                    'order' => $ordering,
                ]);

                // Add options if any
                if (!empty($fieldData['options'])) {
                    $status_ordering = 1;
                    foreach ($fieldData['options'] as $optionText) {
                        if (trim($optionText) !== '') {
                            $field->options()->create([
                                'option_text' => $optionText,
                                'order' => $status_ordering
                            ]);
                        }
                        $status_ordering++;
                    }
                }
            } else {
                // Update existing field
                $field = WorkflowQcField::find($fieldId);
                if ($field) {
                    $field->update([
                        'process_id' => $id,
                        'field_name' => $fieldData['field_name'],
                        'field_type' => $fieldData['field_type'],
                        'order' => $ordering
                    ]);

                    // Remove old options
                    $field->options()->delete();

                    // Add new options
                    if (!empty($fieldData['options'])) {
                        $status_ordering = 1;
                        foreach ($fieldData['options'] as $optionText) {
                            if (trim($optionText) !== '') {
                                $field->options()->create([
                                    'option_text' => $optionText,
                                    'order' => $status_ordering
                                ]);
                            }
                            $status_ordering++;
                        }
                    }
                }
            }
            $ordering++;
        }

        return redirect()->back()->with('success', 'Process QC Fields saved/updated successfully.');
    }

    function globalQcStatus($id) {
        try {
            $newStatus = WorkflowProcessName::where('workflow_id', $id)
                ->where('qc_enabled', 1)
                ->exists() ? 0 : 1;
            WorkflowProcessName::where('workflow_id', $id)->update(['qc_enabled' => $newStatus]);
            return response()->json([
                'success' => true,
                'new_status' => $newStatus,
                'message' => 'Globlal QC status updated successfully.',
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating Globlal QC status.',
            ], 500);
        }
    }

    function processQcStatus($id) {
        try {
            $newStatus = WorkflowProcessName::where('id', $id)
                ->where('qc_enabled', 1)
                ->exists() ? 0 : 1;
            WorkflowProcessName::where('id', $id)->update(['qc_enabled' => $newStatus]);
            return response()->json([
                'success' => true,
                'new_status' => $newStatus,
                'message' => 'Globlal QC status updated successfully.',
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating Globlal QC status.',
            ], 500);
        }
    }
}