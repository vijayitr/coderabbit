<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Client;
use App\Models\TaskLog;
use App\Models\Workflow;
use App\Models\TimeEntry;
use App\Models\AssignTask;
use App\Models\ActivityLog;
use App\Models\AssignClient;
use Illuminate\Http\Request;
use App\Models\GlobleQcField;
use App\Models\UserAllocation;
use App\Models\AssignActivity;
use App\Models\ActivityScript;
use App\Models\AssignmentNote;
use App\Models\WorkflowQcField;
use App\Models\UserChecklistItem;
use App\Models\WorkflowFieldValue;
use App\Models\ProcessQcFieldValue;
use App\Models\WorkflowProcessName;
use App\Models\ProcessNameChecklist;
use Illuminate\Support\Facades\Auth;

// Manager Role
use App\Models\ManagerEntryTime;
use App\Models\ManageDepartment;
use App\Models\ManageDepartmentTask;

// call log
use App\Models\TaskCallLog;


class TimeEntryController extends Controller
{

    public $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth()->guard('web')->user();
            return $next($request);
        });
    }

    /**
     * Show the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        
        if (is_null($this->user) || !$this->user->can('time_entry.view')) {
            abort(403, 'Sorry !! You are Unauthorized to view any Task Data !');
        }

        $departments = collect(); 
        $ManagerEntryTime = collect(); 
        $AssignActivities = collect(); 
        $workflows = collect();

        if ($this->user->can('time_entry.manager_tasks')) {
            $departments = ManageDepartment::with('tasks')->get();
            $ManagerEntryTime = ManagerEntryTime::with(['task','department'])->where('end_time',null)->where('user_id', $this->user->id)->orderBy('created_at', 'desc')->get();
        }

        if ($this->user->can('time_entry.agent_tasks')) {
            $AssignActivitiesQuery = AssignActivity::with('activity.workflow.client');

            if (!$this->user->can('time_entry.view_all_tasks')) {
                $AssignActivitiesQuery->where('user_id', $this->user->id);
            }
            
            $AssignActivities = $AssignActivitiesQuery->get()->where('status', '!=', 'completed');
            $workflows = Workflow::all();
        }

        // filter Data
        $filter_data = $this->getTimeEntryFilters();

        return view('TimeEntry.timeEntry', compact('workflows','AssignActivities','departments', 'ManagerEntryTime', 'filter_data'));
    }

    public function getTimeEntryFilters() {
        $user = auth()->user();
        $userRoleIds = $user->roles->pluck('id')->toArray();
        $isSuperAdmin = in_array(1, $userRoleIds);

        $query = AssignActivity::with([
            'activity.workflow.client.assignedTo',
            'activity.workflowFieldValues.workflowField' => function ($query) {
                $query->orderBy('order', 'asc');
            },
            'activity.workflowFieldValues.workflowField.options' => function ($query) {
                $query->orderBy('option_order', 'asc');
            },
            'TimeEntry','user'
        ]);
        if ($this->user->can('time_entry.quality_check')) {
            $assignedUserIds = UserAllocation::where('parent_id', $user->id)->pluck('user_id');
            $query = $query->where('status', 'completed')->whereIn('user_id', $assignedUserIds)
            ->whereHas('activity.qcFields')
            ->where(function ($q) {
                $q->whereDoesntHave('qc')
                  ->orWhereHas('qc', function ($qc) {
                      $qc->where('status', '!=', 'completed');
                  });
            });
        } else {
            $query = $query->where('status', 'pending');  
        }

        if (!$this->user->can('time_entry.view_all_tasks') && !$this->user->can('time_entry.quality_check')) {
            $query = $query->where('user_id', $this->user->id);
        }

        $assignActivitiesQuery = $query->get();
        $returnData = [
            'users' => [],
            'process_names' => [],
            'workflows' => [],
            'clients' => [],
        ];

        $clientUsers = [];
        foreach ($assignActivitiesQuery as $value) {
            // Add user (unique by user ID)
            $user = $value->user;
            if ($user && !isset($returnData['users'][$user->id])) {
                $returnData['users'][$user->id] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            }

            // Add process (unique by activity ID)
            $activity = $value->activity;
            if ($activity && !isset($returnData['process_names'][$activity->id])) {
                $returnData['process_names'][$activity->id] = [
                    'id' => $activity->id,
                    'process_name' => $activity->process_name,
                    'workflow_id' => $activity->workflow_id,
                ];
            }

            // Add workflow (unique by workflow ID)
            $workflow = $activity->workflow ?? null;
            if ($workflow && !isset($returnData['workflows'][$workflow->id])) {
                $returnData['workflows'][$workflow->id] = [
                    'id' => $workflow->id,
                    'workflow_name' => $workflow->workflow_name,
                    'client_id' => $workflow->client_id,
                ];
            }

            // Add client (unique by client ID)
            $client = $workflow->client ?? null;
            $clientUsers[$client->id][] = $user->id;
            if ($client) {
                $returnData['clients'][$client->id] = [
                    'id' => $client->id,
                    'client_name' => $client->client_name,
                    'assigned_to' => json_encode($clientUsers[$client->id]),
                ];
            }
        }

        // Reindex arrays (remove keys)
        $returnData['users'] = array_values($returnData['users']);
        $returnData['process_names'] = array_values($returnData['process_names']);
        $returnData['workflows'] = array_values($returnData['workflows']);
        $returnData['clients'] = array_values($returnData['clients']);
        return $returnData;

    }

    public function getAssignedTasks(Request $request) {
        // Get Route Code
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $startDate = $request->input('start_date', '');
        $endDate = $request->input('end_date', '');
        $savedTaskId = $request->input('savedTaskId', '');

        $query = AssignTask::with('task')
        ->where([
            'assigned_to' => auth()->id(),
            'archive' => '0'
        ])->orderBy('priority', 'asc')->orderByDesc('id');

        $totalCount = $query->count();
        $allTasks = $query->get();
        $position = $query->pluck('task_id')->search($savedTaskId);
        if ($position) {
            $page = floor($position / $perPage) + 1;
            $offset = ($page - 1) * $perPage;
        }
        $tasks = $query->offset($offset)
            ->limit($perPage)
            ->get();
        $totalPages = ceil($totalCount / $perPage);
        $data['total'] = $totalCount;
       $data['records'] = $tasks->mapWithKeys(function ($task) {
         $priority = $task->priority !== null && $task->priority !== '' ? $task->priority : 4;
            return [
                $task->task_id => array_merge(
                    ['priority' => $priority],
                    json_decode($task->task->data, true) ?? []
                )
            ];
        })->all();
       
        $data['filter_records'] = [];
        foreach ($tasks as $key => $task) {
            $data['filter_records'][] = $task->task_id;
        }

        $data['columns'] = array_unique(
            array_merge(
                ['priority'],
                ...array_map('array_keys', $data['records'])
            )
        );

        if (empty($data['records'])) {
            $data['columns'] = [];
        }

        $data['pagination'] = $this->generatePagination($page, $totalPages);
        $data['pagination_info'] = $this->generatePaginationInfo($page, $perPage, $totalCount);
        return response()->json([
            'draw' => $request->input('draw'),
            'status' => true,
            'data' => $data
        ]);
    }

    function generatePagination($currentPage, $totalPages, $limit = 5) {
        if ($totalPages <= 1) return ''; // No pagination needed for single-page results

        $pagination = '<ul class="pagination">';

        // Previous button
        if ($currentPage > 1) {
            $pagination .= '<li class="paginate_button page-item previous"><a href="?page=' . ($currentPage - 1) . '" data-page="' . ($currentPage - 1) . '" class="page-link"><i class="fa-solid fa-arrow-left"></i> Previous</a></li>';
        }

        // Calculate start and end pages
        $start = max(1, $currentPage - floor($limit / 2));
        $end = min($totalPages, $start + $limit - 1);

        // Adjust start if at the end
        if ($end - $start < $limit - 1) {
            $start = max(1, $end - $limit + 1);
        }
        // pred($end);
        if ($end >= $limit+2) {
            $pagination .= '<li class="paginate_button page-item">
                            <a href="?page=1" data-page="1" class="page-link">1</a>
                        </li>
                        <li class="paginate_button page-item">
                            <a href="#" class="page-link">...</a>
                        </li>';
        }

        // Page number buttons
        for ($i = $start; $i <= $end; $i++) {
            $active = ($i == $currentPage) ? 'active' : '';
            $pagination .= '<li class="paginate_button page-item ' . $active . '">
                                <a href="?page=' . $i . '" data-page="' . $i . '" class="page-link">' . $i . '</a>
                            </li>';
        }

        // pred($end);
        if ($currentPage+$limit < $totalPages) {
            $pagination .= '<li class="paginate_button page-item">
                                <a href="#" class="page-link">...</a>
                            </li>
                            <li class="paginate_button page-item">
                                <a href="?page=' . $totalPages . '" data-page="' . $totalPages . '" class="page-link">' . $totalPages . '</a>
                            </li>';
        }

        // Next button
        if ($currentPage < $totalPages) {
            $pagination .= '<li class="paginate_button page-item next"><a href="?page=' . ($currentPage + 1) . '" data-page="' . ($currentPage + 1) . '" class="page-link">Next <i class="fa-solid fa-arrow-right"></i></a></li>';
        }

        $pagination .= '</ul>';

        return $pagination;
    }

    function generatePaginationInfo($currentPage, $perPage, $totalRecords) {
        $start = ($currentPage - 1) * $perPage + 1;
        $end = min($currentPage * $perPage, $totalRecords);

        return "Showing $start to $end of $totalRecords entries";
    }

    public function archiveTask(Request $request) {
        try {
            $id = $request->id;
            $updated = AssignTask::where(['id' => $id, 'assigned_to' => auth()->id()])
                                 ->update(['archive' => 1]);
            if ($updated) {
                return response()->json([
                    'status' => true,
                    'message' => 'Task archived successfully.',
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => 'Task not found or already archived.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function getClientsAndUsers()
    {
        try {
            $user = auth()->user();
            $userRoleIds = $user->roles->pluck('id')->toArray();
            $isSuperAdmin = in_array(1, $userRoleIds);

            $clientsQuery = Client::select('id', 'client_name')->where('status', '1');
            $usersQuery = User::select('id', 'name', 'email')->where('id', '!=', 1);

            if (!$isSuperAdmin) {
                $assignedClientIds = AssignClient::where('user_id', $user->id)->pluck('client_id');
                $assignedUserIds = UserAllocation::where('parent_id', $user->id)->pluck('user_id');

                $clientsQuery->whereIn('id', $assignedClientIds);
                $usersQuery->whereIn('id', $assignedUserIds);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'clients' => $clientsQuery->get(),
                    'users'   => $usersQuery->get(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('time_entry.create')) {
            abort(403, 'Sorry !! You are Unauthorized to create any Task Data !');
        }
        $partial = $request->has('partial');
        $is_qcagent = $this->user->can('time_entry.quality_check');

        $message = '';
        $userRoles = auth()->user()->roles->pluck('id')->toArray();
        if ($is_qcagent) {
            $this->storeQc($request->all());

            $message = 'QC Data Submitted Successfully';
            // if (!in_array(1, $userRoles)) {
            //     return back()->with('success', 'QC Data Submitted Successfully');
            // }
        }
        // Manage Task Entry
        if (!$request->assignment_id && !$is_agen) {
            $this->storeManageTask($request);
            return back()->with('success', 'Data saved successfully.');
        }

        // Agent Task Entry
        if (!$is_qcagent) {
            $validated = $request->validate([
                'client_id' => 'required|integer',
                'process_id' => 'required|integer',
                'assignment_id' => 'required|integer',
                'category_id' => 'nullable|integer',
                'fields' => 'required|array',
                'fields.*' => 'nullable|string',
                'status' => 'nullable|string',
            ]);
        } else {
            $validated = $request->all();
            $validated['fields'] = [];
        }


        $processId = $validated['process_id'];
        $workflowProcess = WorkflowProcessName::query()
            ->where('id', $processId)
            ->with(['workflow.client'])
            ->first();

        $this->createReportEntry(['process_id' => $processId, 'new' => false, 'assignment_id' => $validated['assignment_id'], 'status' =>$validated['status'], 'partial' => $partial ]);

        if ($workflowProcess && $workflowProcess->workflow && $workflowProcess->workflow->client && $this->user->id != 1) {
            $client = $workflowProcess->workflow->client;
            // if ($client->assigned_to != null && $client->assigned_to != $this->user->id) {
            //     return back()->with('error', 'You do not have permission to access this client.');
            // }
            if ($client->assigned_to == null) {
                // $client->update([
                //     'assigned_to' => $this->user->id,
                // ]);
            }
        }
        $updatedRecord = WorkflowProcessName::with('workflow.client')->find($processId);
        if ($updatedRecord) {
            $client = $updatedRecord->workflow->client ?? null;
            // $updatedRecord->status = strtolower($validated['status']);
            // $updatedRecord->save();
            // pred($validated);
            $status = strtolower($validated['status']);
            if ($status == 'completed') {
                $this->EndBreakCommon($updatedRecord->id);
                if (!$partial) {
                    $this->endTask($this->user->id, $updatedRecord->id, $validated['assignment_id']);
                }
            }
        }
        $changes = [];
        $fieldIds = [];
        $message = 'Data saved successfully';
        foreach ($validated['fields'] as $fieldId => $value) {
            $fieldIds[] = $fieldId;
            $changes[$fieldId] = array(
                'field_name' => '',
                'old' => '',
                'new' => ''
            );
        }

        $workflowFields = WorkflowFieldValue::with(['workflowField.workflow.client', 'workflowProcessName.assignActivities'])->where([
            'process_id' => $processId,
            'assignment_id' => $validated['assignment_id']
        ])->whereIn('field_id',$fieldIds)->get();
        $user_id = auth()->id();
        if (!empty($workflowFields)) {
            foreach ($workflowFields as $key => $value) {
                $changes['details'] = array(
                    'client' => array(
                        'id' => $value->workflowField->workflow->client->id,
                        'name' => $value->workflowField->workflow->client->client_name,
                    ),
                    'workflow' => array(
                        'id' => $value->workflowField->workflow->id,
                        'name' => $value->workflowField->workflow->workflow_name,
                    ),
                    'activity' => array(
                        'id' => $value->workflowProcessName->id,
                        'name' => $value->workflowProcessName->process_name,
                    ),
                    'field_name' => $value->workflowField->field_name,
                    'old' => $value->value,
                );
                $changes[$value->field_id] = array(
                    'field_name' => $value->workflowField->field_name,
                    'old' => $value->value,
                );
            }
        }

        $assignment = AssignActivity::where('id',$validated['assignment_id'])->first();
        $user_id = $assignment->user_id;
        if (!$is_qcagent) {
            foreach ($validated['fields'] as $fieldId => $value) {
                $workflowFields = WorkflowFieldValue::updateOrCreate(
                    [
                        'field_id' => $fieldId,
                        'process_id' => $processId,
                        'assignment_id' => $validated['assignment_id']
                    ],
                    [
                        'value' => $value,
                    ]
                );
                $changes[$fieldId]['new'] = $value;
                if (!$workflowFields->wasRecentlyCreated && $changes[$fieldId]['old'] == $value) {
                    unset($changes[$fieldId]);
                }
                $message = $workflowFields->wasRecentlyCreated ? $message : 'Data updated successfully.';
            }
        }
        if(!empty($changes) && count($changes) > 1) {
            // ActivityLog::create([
            //     'user_id'  => auth()->id(),
            //     'activity' => $message,
            //     'model'    => 'Entry Time',
            //     'model_id' => $user_id,
            //     'changes'  => $changes,
            // ]);
        }
        if (!$partial) {
            TimeEntry::where(['user_id' => $this->user->id, 'assignment_id' => $validated['assignment_id']])
                    ->whereNull('end_time')
                    ->where('type' , 'task')
                    ->update([
                        'end_time' => now(),
                    ]);
        }
        $status = strtolower($validated['status']);
        if (!$is_qcagent && $status == 'completed') {
            $activity = AssignActivity::find($validated['assignment_id']);
            if ($activity) {
                $newActivity = $activity->replicate();
                $newActivity->fill([
                    'auto_assign' => true,
                    'status'      => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $newActivity->save();
            }
        }
        if (!$partial) {
            return back()->with('success', $message);
        }
    }

    public function storeQc($data) {
        $status = isset($data['qc_status']) ? $data['qc_status'] : 'pending';
        if (isset($data['globalqc'])) {
            foreach ($data['globalqc'] as $fieldId => $value) {
                $workflowFields = ProcessQcFieldValue::updateOrCreate(
                    [
                        'globle_qc_field_id' => $fieldId,
                        'process_id' => $data['process_id'],
                        'assignment_id' => $data['assignment_id']
                    ],
                    [
                        'value' => $value,
                        'status' => $status
                    ]
                );
            }
        }
        if (isset($data['qc'])) {
            foreach ($data['qc'] as $fieldId => $value) {
                $workflowFields = ProcessQcFieldValue::updateOrCreate(
                    [
                        'workflow_qc_field_id' => $fieldId,
                        'process_id' => $data['process_id'],
                        'assignment_id' => $data['assignment_id']
                    ],
                    [
                        'value' => $value,
                        'status' => $status
                    ]
                );
            }
        }
    }

    public function storeManageTask($request) {
        // Validate the request data
        $request->validate([
            'fields' => 'required|array',
            'fields.*.department' => 'required|exists:manage_departments,id',
            'fields.*.field_value' => 'nullable|exists:manage_department_tasks,id',
            'fields.*.description' => 'nullable|string|max:255',
        ]);

        // Loop through each entry in the fields array
        $insertData = [];
        foreach ($request->fields as $field) {
            $insertData[] = [
                'user_id' => auth()->id(),
                'manage_field_id' => $field['department'],
                'manage_field_option_id' => $field['field_value'] ?? null,
                'details' => $field['description'],
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        if (!empty($insertData)) {
            ManagerEntryTime::insert($insertData);
        }
    }

    public function getTimeEntryTasks(Request $request)
    {
        try {
            if (is_null($this->user) || !$this->user->can('time_entry.view')) {
                 return response()->json([
                    'status' => 'success',
                    'html' =>'',
                ], 200);
            }

            $user = auth()->user();
            $userRoleIds = $user->roles->pluck('id')->toArray();
            $isSuperAdmin = in_array(1, $userRoleIds);

            $query = AssignActivity::with([
                'activity.workflow.client',
                'activity.workflowFieldValues.workflowField' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'activity.workflowFieldValues.workflowField.options' => function ($query) {
                    $query->orderBy('option_order', 'asc');
                },
                'TimeEntry'
            ]);
            if ($this->user->can('time_entry.quality_check')) {
                $assignedUserIds = UserAllocation::where('parent_id', $user->id)->pluck('user_id');
                $query = $query->where('status', 'completed')->whereIn('user_id', $assignedUserIds)
                ->whereHas('activity.qcFields')
                ->where(function ($q) {
                    $q->whereDoesntHave('qc')
                      ->orWhereHas('qc', function ($qc) {
                          $qc->where('status', '!=', 'completed');
                      });
                });
            } else {
                // $query = $query->where('status', 'pending');  
            }

            if (!$this->user->can('time_entry.view_all_tasks') && !$this->user->can('time_entry.quality_check')) {
                $query = $query->where('user_id', $this->user->id);
            }


            // ===== APPLY FILTERS BASED ON REQUEST =====
            if ($request->filled('user')) {
                $query->where('user_id', $request->user);
            }

            if ($request->filled('process_name')) {
                $query->where('activity_id', $request->process_name);
            }

            if ($request->filled('workflow')) {
                $query->whereHas('activity.workflow', function ($q) use ($request) {
                    $q->where('id', $request->workflow);
                });
            }

            if ($request->filled('client')) {
                $query->whereHas('activity.workflow.client', function ($q) use ($request) {
                    $q->where('id', $request->client);
                });
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('updated_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            } elseif ($request->filled('start_date')) {
                $query->whereDate('updated_at', '>=', $request->start_date);
            } elseif ($request->filled('end_date')) {
                $query->whereDate('updated_at', '<=', $request->end_date);
            }


            $assignActivitiesQuery = $query->get();


            foreach ($assignActivitiesQuery as $assignActivity) {
                $globalQc = $assignActivity->activity->qc_enabled;
                $processId = $assignActivity->activity_id;
                $primaryValues = $assignActivity->activity->workflowFieldValues
                    ->filter(fn($workflowFieldValue) => 
                        $workflowFieldValue->workflowField->is_primary == 1 && 
                        $workflowFieldValue->assignment_id == $assignActivity->id
                    )
                    ->pluck('value');
                $assignActivity->primary_value = $primaryValues[0] ?? '-';

                $fieldsHtml = view('TimeEntry.workflow_fields', ['AssignActivity' => $assignActivity])->render();

                if (!is_null($this->user) && ($this->user->can('time_entry.quality_check') || $isSuperAdmin)) {
                    // Qc fields
                    $qc_fields = WorkflowQcField::with(['options' => function ($query) {
                        $query->orderBy('order', 'asc');
                    }])
                    ->where('process_id',$processId)
                    ->orderBy('order', 'asc')
                    ->get();
                    // Global Qc Fields
                    $globalQCFields = collect();
                    if ($globalQc) {
                        $globalQCFields = GlobleQcField::with('options')->get();
                    }
                    $allQcFields = $globalQCFields->merge($qc_fields);
                    if ($allQcFields->count()) {
                        $qc_data = ProcessQcFieldValue::where(
                            [
                                'process_id' => $processId,
                                'assignment_id' => $assignActivity->id,
                            ]
                        )->get();
                        $fieldsHtml .= '<div class="col-md-12"> <h5>Qc Fields</h5></div>';
                        $fieldsHtml .= view('TimeEntry.qc_fields', ['qc_fields' => $allQcFields, 'qc_data' => $qc_data])->render();
                    }
                }
                $assignActivity->fieldsHtmlBase64 = base64_encode($fieldsHtml);
            }
            $html = view('TimeEntry.timeEntryTableRow', ['AssignActivities' => $assignActivitiesQuery])->render();
            return response()->json([
                'status' => 'success',
                'html' => $html,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCallList(Request $request)
    {
        try {

            $assignment_id = $request->assignment_id;
            $callLogs = TaskCallLog::where('assignment_id', $assignment_id)
            ->when(auth()->id() !== 1, function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()->paginate(5);

            // foreach ($callLogs as $key => $call) {
            //     pre(json_decode(json_decode($call->call_details, true), true));
            // }
            // die;
            $html = view('components.time-entry.tab-pane.call-list', ['callLogs' => $callLogs])->render();

            return response()->json([
                'status' => 'success',
                'html' => $html,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function getCheckList(Request $request)
    {
        try {

            $process_id = $request->process_id;
            $assignment_id = $request->assignment_id;
            $checkListIds = UserChecklistItem::where('assignment_id' ,$assignment_id)->pluck('item_id')->toArray();
            $checklist = ProcessNameChecklist::where('process_id', $process_id)->get();
            $html = view('TimeEntry.checklist', ['checklist' => $checklist, 'checkListIds' =>$checkListIds])->render();

            return response()->json([
                'status' => 'success',
                'html' => $html,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getScript(Request $request)
    {
        try {

            $process_id = $request->process_id;
            $assignment_id = $request->assignment_id;
            $script = ActivityScript::where('activity_id', $request->process_id)->value('script') ?? '';

            return response()->json([
                'status' => 'success',
                'html' => $script,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getNotes(Request $request) {
        try {

            $notes = AssignmentNote::where('assignment_id', $request->assignment_id)->orderBy('created_at', 'desc')->get();
            $html = view('TimeEntry.notes', ['notes' => $notes])->render();

            return response()->json([
                'status' => 'success',
                'html' => $html,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateChecklist(Request $request)
    {
        $request->validate([
            'checklist_id' => 'required|integer',
            'assignment_id' => 'required|integer',
            'action' => 'required|string'
        ]);

        if ($request->action === 'add') {
            UserChecklistItem::firstOrCreate([
                'user_id' => auth()->id(),
                'assignment_id' => $request->assignment_id,
                'item_id' => $request->checklist_id
            ]);
        } else {
            UserChecklistItem::where([
                'user_id' => auth()->id(),
                'assignment_id' => $request->assignment_id,
                'item_id' => $request->checklist_id
            ])->delete();
        }

        return response()->json(['success' => true, 'message' => 'Checklist updated successfully!']);
    }

    public function saveOrUpdateNote(Request $request)
    {
        // Validate the request
        $request->validate([
            'note' => 'required|string',
            'assignment_id' => 'required|integer|exists:assign_activities,id',
        ]);

        if ($request->has('id')) {
            // Update existing note
            $note = AssignmentNote::find($request->id);
            
            if ($note) {
                $note->note = $request->note;
                $note->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Note updated successfully!',
                    'note' => $note,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Note not found!',
                ], 404);
            }
        } else {
            // Create new note
            $note = new AssignmentNote();
            $note->assignment_id = $request->assignment_id;
            $note->note = $request->note;
            $note->user_id = auth()->id();
            $note->save();

            return response()->json([
                'success' => true,
                'message' => 'Note added successfully!',
                'note' => $note,
            ]);
        }
    }


    public function completeTask (Request $request) {
        if ($request->userType == 'manager') {
            return $this->completeManagerTask($request);
        }

        try {
            $processId =$request->get('process_id');
            // Update and fetch the updated record
            $updatedRecord = WorkflowProcessName::with(['workflow.client', 'workflow.fields'])->find($processId);
            if ($updatedRecord) {
                $activity = AssignActivity::find($request->assignment_id);

                if ($activity) {
                    $newActivity = $activity->replicate();
                    $newActivity->fill([
                        'auto_assign' => true,
                        'status' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $newActivity->save();

                    $activity->update(['status' => strtolower($request->get('status'))]);

                    // 
                    // $changes['details'] = array(
                    //     'client' => array(
                    //         'id' => $updatedRecord->workflow->client->id
                    //         'name' => $updatedRecord->workflow->client->client_name,
                    //     ),
                    //     'workflow' => array(
                    //         'id' => $updatedRecord->workflow->id,
                    //         'name' => $updatedRecord->workflow->workflow_name,
                    //     ),
                    //     'activity' => array(
                    //         'id' => $updatedRecord->id,
                    //         'name' => $updatedRecord->process_name,
                    //     )
                    // );
                    // $changes[status] = array(
                    //     'field_name' => 'status',
                    //     'old' => $activity->status,
                    //     'new' => strtolower($request->get('status'))
                    // );
                    // ActivityLog::create([
                    //     'user_id'  => auth()->id(),
                    //     'activity' => 'Agent Task Completed.',
                    //     'model'    => 'Entry Time',
                    //     'model_id' => $activity->user_id,
                    //     'changes'  => $changes,
                    // ]);
                }

                // $updatedRecord->status = strtolower($request->get('status'));
                // $updatedRecord->save();
                $this->endTask($this->user->id, $processId, $request->assignment_id);
            }
            session()->flash('success','Data updated successfully.');
            return response()->json([
                'success' => true,
                'message' => 'Data updated successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the task.',
                'error' => $e->getMessage(),
            ], 500);
        }
        
    }

    public function completeManagerTask($request) {
        // Validate the incoming request
        $request->validate([
            'id' => 'required|integer|exists:manager_entry_times,id',
            'status' => 'required|string|in:completed',
        ]);

        try {
            // Find the task by ID
            $task = ManagerEntryTime::findOrFail($request->id);

            // Update status and set end_time
            $task->update([
                'status' => $request->status,
                'end_time' => now(), // Store completion time
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Task marked as completed successfully!',
                'data' => $task
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error completing task: ' . $e->getMessage()
            ], 500);
        }
    }

    public function startTask (Request $request) {
        // pred($request->all());

        if ($request->userType === 'manager') {
            return $this->startManagerTask($request);
        }

        try {
            $processId = $request->get('process_id');

            if (!$this->createReportEntry($request->all())) {
                return response()->json([
                    'success' => false,
                    'message' => 'This activity not assigned to you.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Task started successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while starting the task.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function startManagerTask($request) {
        try {
            // Validate request data
            $request->validate([
                'userType' => 'required|in:manager',
                'department_id' => 'required|exists:manage_departments,id',
                'task_id' => 'required|exists:manage_department_tasks,id',
                'details' => 'nullable|string',
            ]);

            $InsertData = [
                'user_id' => auth()->id(),
                'manage_field_id' => $request->department_id,
                'manage_field_option_id' => $request->task_id,
                'details' => $request->details,
                'start_time' => now(),
            ];

            // Prepare optional JSON data
            $extraData = array_filter([
                'clients' => $request->clients,
                'user'    => $request->users,
                'full_time_employee'    => $request->full_time_employee,
                'todays_team'    => $request->todays_team,
            ], function ($value) {
                return !is_null($value); // Only filter out nulls
            });


            // Add extra_data to the insert array if present
            if (!empty($extraData)) {
                $InsertData['extra_data'] = json_encode($extraData);
            }

            // Insert data into database
            $entry = ManagerEntryTime::create($InsertData);

            return response()->json([
                'message' => 'Task started successfully',
                'data' => $entry,
                'status' => true
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while starting the task.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createReportEntry($data) {
        $is_qcagent = $this->user->can('time_entry.quality_check');
        $processId = $data['process_id'];
        $partial = isset($data['partial']) ? $data['partial'] : false;;
        $new = $data['new'] ?? true;
        $updatedRecord = WorkflowProcessName::with(['workflow.client', 'workflow.fields'])->find($processId);
        if (!$updatedRecord) {
            return;
        }

        $client = $updatedRecord->workflow->client ?? null;
        // $update_data = ['status' => 'pending'];

        if ($new) {
            $AssignActivities = AssignActivity::where(['id' => $data['assignment_id'], 'user_id' => $this->user->id, 'activity_id' => $processId, 'status' => null])->first();
            if ($AssignActivities) {
                // $update_data['assigned_by'] = $this->user->id;
                $fieldsArray = $updatedRecord->workflow->fields->map(fn($field) => [
                    'field_id' => $field->id,
                    'process_id' => $processId,
                    'assignment_id' => $data['assignment_id']
                ])->toArray();
                if (!empty($fieldsArray) && !$is_qcagent) {
                    WorkflowFieldValue::insert($fieldsArray);
                    $AssignActivities->update(['status' => 'pending']);
                }
            } else {
                return false;
            }
        } else {
            if (!empty($data['status']) && !$is_qcagent) {
                AssignActivity::where([
                    'id' => $data['assignment_id'],
                    'user_id' => $this->user->id,
                    'activity_id' => $processId,
                ])->whereNotNull('status')
                ->update(['status' => $data['status']]);
            }
        }
        // $updatedRecord->update($update_data);

        if (!$partial) {
            $existingTask = TimeEntry::where([
                'process_id' => $processId,
                'assignment_id' => $data['assignment_id'],
                'user_id' => $this->user->id,
                'end_time' => null,
            ])->first();

            $taskData = [
                'process_id' => $processId,
                'assignment_id' => $data['assignment_id'],
                'user_id' => $this->user->id,
                'client_id' => $client?->id,
                'activity' => $updatedRecord->process_name,
                'category' => 'Production Activities',
                'sub_category' => 'Direct Production',
            ];

            if ($existingTask) {
                // Update existing task
                if (!$partial) {
                    $existingTask->update(array_merge($taskData, [
                        'end_time' => now(),
                    ]));
                }
            } else {
                // Create a new task
                $taskData = array_merge($taskData, [
                    'type' => 'task',
                    'comments' => 'Task',
                    'start_time' => now(),
                    'end_time' => null,
                ]);
                $existingTask = TimeEntry::create($taskData);
            }

            // Create a new task entry if the date has changed
            if (date('d-M-Y') !== date('d-M-Y', strtotime($existingTask->created_at))) {
                $taskData['start_time'] = now();
                $taskData['end_time'] = null;
                TimeEntry::create($taskData);
            }

            // Log the start task action
            TaskLog::create([
                'user_id' => $this->user->id,
                'time_entry_id' => $existingTask->id,
                'action' => 'start_task',
                'status' => 'active',
            ]);
        }
        return true;
    }


    public function resumeTasks(Request $request) {
        try {
            $processId = $request->id;
            $user_id = $this->user->id;
            TimeEntry::where('user_id', $user_id)
                    ->whereNull('end_time')
                    ->whereIn('type', ['task','break'])
                    ->update([
                        'end_time' => now(),
                    ]);
            $updatedRecord = WorkflowProcessName::with(['workflow.client', 'workflow.fields'])->find($processId);

            if ($updatedRecord) {
                $client = $updatedRecord->workflow->client ?? null;
                $task = TimeEntry::create([
                        'process_id' => $processId,
                        'user_id' => $user_id,
                        'client_id' => $client?->id,
                        'activity' => $updatedRecord->process_name,
                        'assignment_id' => $request->assignment_id,
                        'type' => 'task',
                        'start_time' => now(),
                        'category' => 'Production Activities',
                        'sub_category' => 'Direct Production ',
                        'end_time' => null,
                    ]);
                TaskLog::create([
                    'user_id' => $user_id,
                    'time_entry_id' => $task->id,
                    'action' => 'start_task',
                    'status' => 'active'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Data updated successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the task.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function endTask($user_id, $process_id, $assignment_id)
    {
        $this->EndBreakCommon($process_id);
        $task = TimeEntry::where(['process_id' => $process_id , 'assignment_id' => $assignment_id])
                ->where('type', 'task')
                ->where('user_id', auth()->id())
                ->whereNull('end_time')
                ->first();
        if ($task) {
            $task->end_time = now();
            $task->status = 'completed';
            $task->save();

            // Log the end task action with the correct user_id
            TaskLog::create([
                'user_id' => $user_id,
                'time_entry_id' => $task->id,
                'action' => 'end_task',
                'status' => 'active',
            ]);
        }
        return response()->json($task);
    }

    public function startBreak(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'process_id' => 'required|integer', // Ensure process_id exists in processes table
            'pause_reason' => 'nullable|string|max:255',
            'breakOption' => 'nullable|string|max:255',
        ]);

        if (!$request->pause_reason && !$request->breakOption) {
            return back()->with('error', 'Please Fill Your break reason.');
        }

        // Create a new time entry for the break
        $break = TimeEntry::create([
            'user_id' => $this->user->id,
            'process_id' => $request->process_id,
            'client_id' => $request->client_id ?? null,
            'assignment_id' => $request->assignment_id ?? null,
            'type' => 'break',
            'comments' => 'Break',
            'start_time' => now(),
            'comments' => $request->pause_reason,
            'break_option' => $request->breakOption,
        ]);

        // Log the start break action
        TaskLog::create([
            'user_id' => auth()->id(),
            'time_entry_id' => $break->id,
            'action' => 'start_break',
            'status' => 'active',
        ]);
        return back();
    }

    public function endBreak(Request $request)
    {
        // Validate that `process_id` is provided
        $request->validate([
            'process_id' => 'required|integer',
        ]);
        return $this->EndBreakCommon($request->process_id);
    }

    public function EndBreakCommon($process_id) {
        // Find the active break for the given process_id
        $break = TimeEntry::where('process_id', $process_id)
                ->where('type', 'break')
                ->where('user_id', auth()->id())
                ->whereNull('end_time')
                ->first();
        if ($break) {
            // End the break
            $break->end_time = now();
            $break->save();

            // Log the end break action
            TaskLog::create([
                'user_id' => auth()->id(),
                'time_entry_id' => $break->id,
                'action' => 'end_break',
                'status' => 'active',
            ]);
        }

        // Return the updated break as JSON
        return response()->json($break);
    }

    public function userIdle(Request $request) {

        try {
            $reason = $request->reason;
            $break_type = $request->break_type;
            $user_id = $this->user->id;
            $timeEntry = TimeEntry::where('type', 'idle')->whereNull('end_time')->where('user_id', $user_id)->first();
            if ($timeEntry && ($reason || $break_type)) {
                $timeEntry->end_time = now();
                $timeEntry->status = 'completed';
                $timeEntry->comments = $reason;
                $timeEntry->break_option = $break_type;
                $timeEntry->save();
            } elseif (!$timeEntry) {
                $task = TimeEntry::create([
                        'user_id' => $user_id,
                        'activity' => 'Idle',
                        'type' => 'idle',
                        'comments' => 'Idle Time',
                        'start_time' => now(),
                        'category' => 'Idle',
                        'sub_category' => 'Idle Activities',
                    ]);
                TaskLog::create([
                    'user_id' => $user_id,
                    'time_entry_id' => $task->id,
                    'action' => 'start_task',
                    'status' => 'active'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Success.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
