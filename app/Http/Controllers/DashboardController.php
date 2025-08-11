<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Permission;
use App\Models\TimeEntry;
use App\Helpers\TimeHelper;
use App\Models\ImportedTask;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\WorkflowField;
use App\Events\MessageSent;
use App\Models\AssignActivity;
use App\Models\RoleHasPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ReportController;
 
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;
use App\Models\Client;
use App\Models\Workflow;
use App\Models\UserAllocation;
use App\Models\ManagerEntryTime;
use App\Models\WorkflowFieldOption;
use App\Models\WorkflowProcessName;
use App\Models\WorkflowFieldValue;
use App\Exports\CsvExport;


// use Spatie\Permission\Models\Role;
// use Spatie\Permission\Models\Permission;
class DashboardController extends Controller
{
    /**
     * Show the dashboard.
     *
     * @return \Illuminate\View\View
     */

    public $user;
    public $userId;
    public $reportController;

    public function __construct(ReportController $reportController)
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth()->guard('web')->user();
            $this->userId = $this->user->id;
            return $next($request);
        });
        $this->reportController = $reportController;
    }

    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }
        $userIds = $this->UserIds();
        
        $users = User::whereNotIn('id', [1])
            ->when(!empty($userIds), fn($query) => $query->whereIn('id', $userIds))
            ->get();

        $clients = Client::when(!empty($userIds), fn($query) => $query->whereIn('assigned_to', $userIds))
            ->get();

        $clientIds = $clients->pluck('id');

        $activities = WorkflowProcessName::with('workflow')
            ->when(!empty($userIds), fn($query) => 
                $query->whereHas('workflow', fn($q) => $q->whereIn('client_id', $userIds))
            )
            ->get();
        $userRoles = auth()->user()->roles->pluck('id')->toArray();
        return view('dashboard.index', compact('users', 'clients', 'activities', 'userRoles'));
    }

    public function UserIds() 
    {
        $user_id = $this->user->id;

        if ($this->user->can('dashboard.view_all_users_data')) {
            return [];
        }

        if ($this->user->can('dashboard.view_team_dashboard')) {
            return array_merge(
                UserAllocation::where('parent_id', $user_id)->pluck('user_id')->toArray(),
                [$user_id]
            );
        }

        return [$user_id];
    }

    public function topStatus($userIds = [], $startDate = '', $endDate = '', $client_filter, $activity_filter) {
        if (is_null($this->user) || !$this->user->can('dashboard.show_top_status')) {
            return [] ;
        }
        $status_count = [];
        $status = WorkflowField::with([
                'fieldValues' => function($query) use ($startDate, $endDate, $activity_filter) {
                    // Date filtering
                    if (!empty($startDate) && !empty($endDate)) {
                        $endDate = date('Y-m-d 23:59:59', strtotime($endDate));
                        $startDate = date('Y-m-d 00:00:00', strtotime($startDate));

                        if ($startDate != $endDate) {
                            $query->whereBetween('created_at', [$startDate, $endDate]);
                        } else {
                            $query->whereDate('created_at', $startDate);
                        }
                    }
                    if ($activity_filter) {
                        $query->where('process_id', $activity_filter);
                    }
                },
                'workflow.client' => function($query) use ($userIds, $client_filter) {
                    // User ID filtering
                    if (!empty($userIds)) {
                        $query->whereIn('assigned_to', $userIds);
                    }
                    if ($client_filter) {
                        $query->where('id', $client_filter);
                    }
                },
                'fieldValues.option' // No change needed here
            ])
            ->where('field_name', 'status')->get();

        foreach ($status as $workflowField) {
            $top_stauts = true;
            if (!empty($userIds) && (!isset($workflowField->workflow->client->assigned_to) || isset($workflowField->workflow->client->assigned_to) && !in_array($workflowField->workflow->client->assigned_to, $userIds))) {
                $top_stauts = false;
            }
            if ($top_stauts) {
                foreach ($workflowField->fieldValues as $fieldValue) {
                    $option_value = $fieldValue->option ? $fieldValue->option->option_value : $fieldValue->value;
                    if ($option_value) {
                        $status_count[$option_value] = isset($status_count[$option_value]) ? $status_count[$option_value] + 1 : 1;
                    }
                }
            }
        }
        arsort($status_count);
        $topStatus = array_slice($status_count, 0, 10, true);
        return $topStatus;
    }

    public function getChartData(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('dashboard.view')) {
            return response()->json([
                'attendanceData' => [
                    "onTime" =>  0,
                    "late" =>  0,
                    "noRecord" =>  1
                ],
                'IdleTime' => [
                    "idle_data_arr" => [],
                    "labels" => [],
                    "data" => []
                ],
                'top_stauts' => [],
                'usersProductivity' => [],
                'usersCount' => 0,
                'clientsCount' => 0,
                'taskCount' => 0,
                'workflowsCount' => 0,
                'completedTasks' => 0,
                'pendingTasks' => 0,
                'managerCompletedTasks' => 0,
                'managerCendingTasks' => 0,
            ]);
        }

        $userIds = $this->UserIds();
        $managerRoleIds = RoleHasPermission::whereIn('permission_id', function ($query) {
                $query->select('id')->from('permissions')->where('name', 'report.manager_reports');
            })
            ->whereNotIn('role_id', [1])
            ->pluck('role_id')
            ->toArray();

        if (!$this->user->can('dashboard.view_all_users_data')) {
            $userIds[] = $this->user->id;
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        if ($request->user_filter) {
            $userIds = [$request->user_filter];
        }
        if (is_null($this->user) || !$this->user->can('dashboard.show_productivity')) {
            $usersProductivity = [];
        } else {
            $usersProductivity = $this->reportController->getUsersProductivity($userIds, $start_date, $end_date, $request->client_filter, $request->activity_filter, $managerRoleIds);
        }
        $top_stauts = $this->topStatus($userIds, $start_date, $end_date, $request->client_filter, $request->activity_filter);
        $attendanceData = TimeHelper::attendanceData($userIds, $start_date, $end_date);
        // pred($userIds);
        $IdleTime  = TimeHelper::calculateIdleTime($userIds, $start_date, $end_date);
        $dashCount = $this->getDashboardCounts($userIds, $request);

        // Manager Productivity Data
        if ($this->user->can('report.manager_reports')) {
            $managersProductivity = $this->getManagersProductivity($start_date, $end_date);
        }
        $response  = [
            'attendanceData' => $attendanceData,
            'IdleTime' => $IdleTime,
            'top_stauts' => $top_stauts,
            'usersProductivity' => $usersProductivity,
            'managersProductivity' => $managersProductivity ?? []
        ];

        $response = array_merge($response, $dashCount);
        return response()->json($response);
    }

    public function getManagersProductivity($startDate, $endDate) {
        $query = ManagerEntryTime::with(['user', 'department', 'task']);

        // Apply date filter if provided
        if (!empty($startDate) && !empty($endDate)) {
            $endDate = date('Y-m-d 23:59:59', strtotime($endDate));
            $startDate = date('Y-m-d 00:00:00', strtotime($startDate));

            if ($startDate != $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } else {
                $query->whereDate('created_at', $startDate);
            }
        }

        // Check user roles for restrictions
        $userRoles = auth()->user()->roles->pluck('id')->toArray();
        if (!in_array(1, $userRoles)) {
            $query->where('user_id', auth()->user()->id);
        }

        // Get the data grouped by user_id and count the total and completed tasks
        $tasksGroupedByUser = $query
            ->selectRaw('user_id, 
                        count(*) as total_tasks, 
                        sum(case when end_time is not null then 1 else 0 end) as completed_tasks')
            ->groupBy('user_id')
            ->get();


        // Calculate completion rate and output the data
        $manager_productivity = [];
        foreach ($tasksGroupedByUser as $data) {
            $completionRate = $data->total_tasks > 0 ? ($data->completed_tasks / $data->total_tasks) * 100 : 0;
            $manager_productivity[] = array(
                "user_name" => $data->user->name,
                "total_tasks" => $data->total_tasks,
                "completed_tasks" => $data->completed_tasks,
                "completionRate" => round($completionRate, 2),
            );
        }

        return $manager_productivity;
    }

    public  function getDashboardCounts($userIds, $request){
        $usersCount = User::whereNotIn('id', [1])->count();
        if (!empty($userIds)) {
            $usersCount = UserAllocation::whereIn('parent_id', $userIds)->count();
            $usersCount = $usersCount+1;
        }

        $clientsIds = Client::when(!empty($userIds), function ($query) use ($userIds) {
                    return $query->whereIn('assigned_to', $userIds);
                })->pluck('id');
        $clientsCount = $clientsIds->count();
        $workflowsCount = 0;
        $taskCount = 0;
        $completedTasks = 0;
        $pendingTasks = 0;
        if ($clientsCount) {
            $clientsIds = $clientsIds->toArray();
            $workflows = Workflow::with(['fields','processNames'])
                ->when(!empty($clientsIds), function ($query) use ($clientsIds) {
                    return $query->whereIn('client_id', $clientsIds);
                })->get();
            if ($request->client_filter) {
                 $workflows =  $workflows->where('client_id', $request->client_filter);
            }
            $workflowsCount = $workflows->count();
            if ($workflowsCount) {
                $fields = $workflows->flatMap(function ($workflow) {
                    return $workflow->fields->pluck('id');
                })->unique()->toArray();


                foreach ($workflows as $workflow) {
                    if (!empty($workflow->processNames)) {
                        $completedTasks += $workflow->processNames->where('status', 'completed')->count();
                        $pendingTasks += $workflow->processNames->whereNotNull('status')->where('status', '!=', 'completed')->count();
                    }
                }

                $taskCount = WorkflowFieldValue::whereIn('field_id', $fields)->distinct('process_id')->count('process_id');
            }
        }

        $query = ManagerEntryTime::with(['user', 'department', 'task']);
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        if (!empty($startDate) && !empty($endDate)) {
            $endDate = date('Y-m-d 23:59:59', strtotime($endDate));
            $startDate = date('Y-m-d 00:00:00', strtotime($startDate));

            if ($startDate != $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } else {
                $query->whereDate('created_at', $startDate);
            }
        }
        $userRoles = auth()->user()->roles->pluck('id')->toArray();
        if (!in_array(1, $userRoles)) {
            $query->where('user_id', auth()->user()->id);
        }
        $managerPendingTasks = (clone $query)->whereNull('end_time')->count();
        $managerCompletedTasks = (clone $query)->whereNotNull('end_time')->count();
        // Get Manager users
        $managerCounts = 0;
        $role_ids = RoleHasPermission::whereIn('permission_id', function ($query) {
                $query->select('id')->from('permissions')->where('name', 'report.manager_reports');
            })
            ->whereNotIn('role_id', [1])
            ->pluck('role_id')
            ->toArray();

        if (!empty($role_ids)) {
            $managerCounts = User::whereHas('roles', fn($query) => $query->whereIn('id', $role_ids))->count();
        }

        // ->get();
     

        return array(
            'usersCount' => $usersCount,
            'clientsCount' => $clientsCount,
            'taskCount' => $taskCount,
            'workflowsCount' => $workflowsCount,
            'completedTasks' => $completedTasks,
            'pendingTasks' => $pendingTasks,
            'managerCompletedTasks' => $managerCompletedTasks,
            'managerPendingTasks' => $managerPendingTasks,
            'managersCount' => $managerCounts,
        );
    }

    public function importTestingDataForm()
    {
        $clients = Client::pluck('id', 'client_name');
        return view('dashboard.import', compact('clients'));
    }

    public function importTestingDatadata(Request $request)
    {
        try {
            // Validate uploaded file
            $request->validate([
                'file' => 'required|mimes:xlsx,xls', // Only allow Excel files
            ]);

            // Get file and user ID
            $file = $request->file('file');
            $userId = auth()->id();

            // Load Excel file into an array
            $data = Excel::toArray(new class implements ToArray {
                public function array(array $rows) { return $rows; }
            }, $file);

            if (empty($data) || empty($data[0])) {
                return back()->with('error', 'Empty file or invalid format.');
            }

            // Preload database data
            $clients = Client::pluck('id', 'client_name');
            $workflows = Workflow::pluck('id', 'workflow_name');
            $workflowFields = WorkflowField::pluck('id', 'field_name');
            $workflowProcesses = WorkflowProcessName::pluck('id', 'process_name');

            // Extract header and records
            $header = array_map(fn($h) => strtolower(str_replace(' ', '_', $h)), $data[0][0]);
            $records = array_slice($data[0], 1);

            foreach ($records as $record) {
                $temp_arr = [];
                $fields = [];

                foreach ($record as $index => $value) {
                    $field_name = $header[$index] ?? null;
                    if (!$field_name) continue;

                    if (preg_match('/^field_name_(\d+)$/', $field_name, $matches)) {
                        $num = $matches[1];
                        $value_key = array_search("field_value_$num", $header);
                        if ($value_key !== false && !empty($record[$index])) {
                            $fields[$record[$index]] = $record[$value_key];
                        }
                    } else {
                        $temp_arr[$field_name] = ($field_name == 'entry_type' || $field_name == 'status') ? strtolower(str_replace(' ', '_', $value)) : $value;
                    }
                }

                // Assign client and workflow IDs
                if (empty($temp_arr['client_name']) || empty($temp_arr['workflow_name'])) continue;
                $temp_arr['client_id'] = $clients[$temp_arr['client_name']] ?? null;
                $temp_arr['workflow_id'] = $workflows[$temp_arr['workflow_name']] ?? null;
                if (!$temp_arr['client_id'] || !$temp_arr['workflow_id']) continue;

                // Get or create workflow process
                $workflowProcess = WorkflowProcessName::where([
                    'process_name' => $temp_arr['activity_name'],
                    'workflow_id' => $temp_arr['workflow_id']
                ])->first();

                if (!$workflowProcess) continue;
                $temp_arr['process_id'] = $workflowProcess->id;

                // Update status if null
                if (is_null($workflowProcess->status)) {
                    $workflowProcess->update(['status' => $temp_arr['status'] ?? 'pending']);
                }

                // Generate Start and End Date
                $start_time = strtotime(date('Y') . '-01-01') + rand(0, time() - strtotime(date('Y') . '-01-01'));
                $end_time = $start_time + (rand(3, 10) * 60);

                // Prepare task data
                $taskData = [
                    'process_id'   => $temp_arr['process_id'],
                    'user_id'      => $temp_arr['agent_id'] ?? $userId,
                    'client_id'    => $temp_arr['client_id'],
                    'activity'     => $workflowProcess->process_name,
                    'category'     => $temp_arr['category'] ?? 'Production Activities',
                    'sub_category' => 'Direct Production',
                    'type'         => $temp_arr['entry_type'],
                    'comments'     => $temp_arr['comment'] ?? '',
                    'start_time'   => date('Y-m-d H:i:s', $start_time),
                    'end_time'     => date('Y-m-d H:i:s', $end_time),
                    'status'       => $temp_arr['status'] ?? 'pending'
                ];

                pred($taskData);
                TimeEntry::create($taskData);
                $this->AddBreak($taskData);
                $this->AddBreak($taskData, 'idle');

                // Insert workflow fields
                foreach ($fields as $fieldName => $value) {
                    if (!isset($workflowFields[$fieldName])) continue;
                    WorkflowFieldValue::updateOrCreate(
                        ['field_id' => $workflowFields[$fieldName], 'process_id' => $temp_arr['process_id']],
                        ['value' => $value]
                    );
                }
            }
            return back()->with('success', 'Data Imported Successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error processing file: ' . $e->getMessage());
        }
    }

    public function importTestingData(Request $request)
    {
            // Validate the uploaded file
            $request->validate([
                'file' => 'required|mimes:xlsx,xls', // Only allow Excel files
            ]);

            // Get the uploaded file
            $file = $request->file('file');
            
            // Get the user ID
            $userId = auth()->id();
            

            // Load the Excel file data into an array
            $data = Excel::toArray(new class implements ToArray {
                public function array(array $rows)
                {
                    return $rows;
                }
            }, $file);

            if (!empty($data) && !empty($data[0])) {
                $header = [];
                $records = [];
                $clients = Client::get();
                $Workflow = Workflow::get();
                $WorkflowField = WorkflowField::get();
                $WorkflowFieldValue = WorkflowFieldValue::get();
                $WorkflowFieldOption = WorkflowFieldOption::get();
                $WorkflowProcessName = WorkflowProcessName::get();
                foreach ($data[0] as $key => $record) {
                    if ($key == 0) {
                        $header = $record;
                        continue;
                    }
                     // Generate Start and End Date
                        $start_date = strtotime(date('Y') . '-01-01');  // January 1st of this year
                        $end_date = time();  // Current date
                        $random_date = rand($start_date, $end_date);
                        $random_date_formatted = date('Y-m-d H:i:s', $random_date);
                        $start_time = strtotime($random_date_formatted);
                        $minutes_diff = rand(3, 10);
                        $end_time = $start_time + ($minutes_diff * 60);
                        $start_time_formatted = date('Y-m-d H:i:s', $start_time);
                        $end_time_formatted = date('Y-m-d H:i:s', $end_time);
                    
                    $temp_arr = [];
                    foreach ($record as $res_key => $rec) {
                        $field_name = strtolower(str_replace(' ', '_', $header[$res_key]));

                        if (str_starts_with($field_name, "field_name_") || str_starts_with($field_name, "field_value_")) {
                            preg_match('/(\d+)$/', $field_name, $matches);
                            $last_digits = $matches[0] ?? null;
                            
                            if ($last_digits !== null) {
                                $field_key = array_search("Field Name $last_digits", $header);
                                $value_key = array_search("Field Value $last_digits", $header);
                                
                                if ($field_key !== false && $value_key !== false && !empty($record[$field_key])) {
                                    $temp_arr['fields'][$record[$field_key]] = $record[$value_key];
                                }
                            }
                        } else {
                            
                            if ($field_name == 'entry_type' || $field_name == 'status') {
                                $rec = strtolower(str_replace(' ', '_', $rec));
                            }
                            $temp_arr[$field_name] = $rec;
                        }
                    }

                // pred($temp_arr);
                // echo 'helldo';
                        // pre($WorkflowProcessName);
                    if (!empty($temp_arr['client_name'])) {
                        $temp_arr['client_id'] = $clients->where('client_name', $temp_arr['client_name'])->pluck('id')->first();
                        Client::where('id', $temp_arr['client_id'])->update(['assigned_to' => $temp_arr['agent_id']]);
                        $condition = ['workflow_name' => $temp_arr['workflow_name'], 'client_id' => $temp_arr['client_id']];
                        $temp_arr['workflow_id'] = Workflow::where($condition)->pluck('id')->first();
                        $WorkflowProcessName = WorkflowProcessName::where(['process_name' => $temp_arr['activity_name'], 'workflow_id' => $temp_arr['workflow_id']])->first();
                        // echo 'hellos';
                        // pre($temp_arr);
                        if($WorkflowProcessName) {
                            $temp_arr['process_id'] = $WorkflowProcessName->id;

                            foreach ($temp_arr['fields'] as $fkey => $field) {
                                $field_id = WorkflowField::where(['field_name' => $fkey, 'workflow_id' => $temp_arr['workflow_id']])->pluck('id')->first();

                                $temp_arr['insertFields'][$field_id] = $field;
                            }
                            // Insert Data

                            if ($WorkflowProcessName->status == null) {
                                $WorkflowProcessName->status = !empty($temp_arr['status']) ? $temp_arr['status'] : 'pending';
                                $WorkflowProcessName->save();
                            }
                            Client::where('id', $temp_arr['client_id'])->update(['assigned_to' => $temp_arr['agent_id']]);

                            $taskData = [
                                'process_id' => $temp_arr['process_id'],
                                'user_id' => $temp_arr['agent_id'],
                                'client_id' =>  $temp_arr['client_id'],
                                'activity' => $WorkflowProcessName->process_name,
                                'category' => $temp_arr['category'] ?? 'Production Activities',
                                'sub_category' => 'Direct Production',
                                'type' => $temp_arr['entry_type'],
                                'comments' => $temp_arr['comment'],
                                'start_time' => $start_time_formatted,
                                'end_time' => $end_time_formatted,
                                'status' => !empty($temp_arr['status']) ? $temp_arr['status'] : 'pending'
                            ];
                            TimeEntry::create($taskData);
                            $this->AddBreak($taskData);
                            $this->AddBreak($taskData, 'idle');
                    
                            foreach ($temp_arr['insertFields'] as $fieldId => $value) {
                                if($fieldId != '') {
                                    $workflowFields = WorkflowFieldValue::updateOrCreate(
                                        [
                                            'field_id' => $fieldId,
                                            'process_id' => $temp_arr['process_id'],
                                        ],
                                        [
                                            'value' => $value,
                                            'updated_at' => now(),
                                            'created_at' => now()
                                        ]
                                    );
                                }
                            }
                            $records[] = $temp_arr;
                        }
                    }
                }

                // pred($records);
            } else {
                return back()->with('error', 'Empty file or invalid format.');
            }
            return back()->with('success', 'Data Imported Successfully.');
        try {

        } catch (\Exception $e) {
            // return back();
        }

    }

    function AddBreak($taskData, $type = 'break')
    {
        $probability = 50;
        if (rand(1, 100) <= $probability) {
            $excuses = $this->excuses();
            $randomExcuse = $excuses[array_rand($excuses)];
            $start_time = strtotime(date('Y') . '-01-01') + rand(0, time() - strtotime(date('Y') . '-01-01'));
            $end_time = $start_time + (rand(3, 10) * 60);

            $taskData['client_id'] = null;
            $taskData['activity'] = null;
            $taskData['category'] = $type == 'break' ? null : 'idle';
            $taskData['process_id'] = $type == 'break' ? $taskData['process_id'] : null;
            $taskData['sub_category'] = $type == 'break' ? null : 'Idle Activities';
            $taskData['type'] = $type;
            $taskData['status'] = 'completed';
            $taskData['comments'] = $randomExcuse;
            $taskData['start_time'] = date('Y-m-d H:i:s', $start_time);
            $taskData['end_time'] = date('Y-m-d H:i:s', $end_time);
            TimeEntry::create($taskData);
        }
    }

    public function excuses() {
        return [
            "I was about to start, but I got caught up in another urgent task.",
            "I’m waiting on some information before I can proceed.",
            "I ran into an unexpected issue, working on resolving it now.",
            "I had some technical difficulties, but I’ll get back on track soon.",
            "I was in back-to-back meetings, so I couldn’t make much progress.",
            "The file I needed was missing, but I’ll sort it out shortly.",
            "I had to prioritize another task, but I’ll focus on this next.",
            "I started, but I realized I needed more details to continue.",
            "I had some personal matters to attend to, but I’m back on it now.",
            "I was waiting for approval before moving forward.",
            "I needed to double-check some requirements before proceeding.",
            "I had to step away for an urgent request, but I’ll resume shortly.",
            "My system crashed earlier, which caused some delays.",
            "I lost track of time but will catch up soon.",
            "I was reviewing everything to make sure it's done correctly.",
            "I was helping a colleague with something urgent.",
            "I got sidetracked but will refocus now.",
            "I’m making steady progress, just need a little more time.",
            "I needed to confirm some details before proceeding.",
            "Almost done, just putting the finishing touches!"
        ];

    }

    public function GetClientWorkflowDetails($client) {
        $client_data = Client::with('assignedUser')->where('id',$client)->first();
        $user_id = '';
        $header = 'Client Name,Workflow Name,Activity Name,Agent id,Entry Type,Status,Category,Comment,';
        if ($client_data) {
            $client_id = $client_data->id;
            $client_name = $client_data->client_name;
            if (!empty($client_data->assigned_to) && isset($client_data->assigned_to)) {
                $user_id = $client_data->assigned_to;
            }

            $csvData = '';
            $field_counts = 0;

            // Get Workflows
            $workflows = Workflow::with(['processNames', 'fields'])->where('client_id',$client_id)->get();
            if (!empty($workflows) && $workflows) {
                foreach ($workflows as $key => $value) {
                    $workflow_name = $value->workflow_name;
                    $processNames = $value->processNames;
                    $fields = $value->fields;
                    $field_values = '';
                    $field_counts = $field_counts > count($fields) ? $field_counts : count($fields);
                    foreach ($fields as $key => $value) {
                        
                        $field_values .= $value->field_name.',,';
                    }
                    foreach ($processNames as $key => $value) {
                        $statuses = ['Pending', 'In Progress', 'On Hold', 'Completed'];
                        $status = $statuses[array_rand($statuses)];
                        $csvData .= $client_name.','.$workflow_name.','.$value->process_name.','.$user_id.',task,'.$status.',Production Activities,,'.$field_values."\n";
                    }
                }

                for ($i=0; $i < $field_counts ; $i++) { 
                    $index = $i+1;
                   $header .= 'Field Name '.$index.',Field Value '.$index.',';
                }
                $header .= "\n";

                $rows = array_map("str_getcsv", explode("\n", $header.$csvData));

                // Remove empty rows if any
                $rows = array_filter($rows, function ($row) {
                    return !empty(array_filter($row)); // Remove completely empty rows
                });

                // Return Excel file for download
                return Excel::download(new CsvExport($rows), $client_name.'.xlsx');
                echo $client_name.'xlsx Sheet Downloaded';
            } else {
                echo ' Workflows Not Exist';
            }
        } else {
            echo $client.' Client Not Exist';
        }
    }

    public function getTasks(Request $request) {
        // Get Route Code
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $startDate = $request->input('start_date', '');
        $endDate = $request->input('end_date', '');

        // Fetch only needed columns and data for optimization
        $query = ImportedTask::with('assignedTask')
        ->where(function ($q) {
            $q->where('imported_by', $this->user->id)
              ->orWhereHas('assignedTask', function ($subQ) {
                  $subQ->where('assigned_to', $this->user->id);
              });
        })->orderBy('id', 'desc');

        $totalCount = $query->count();
        $allTasks = $query->get();
        $tasks = $query->offset($offset)
            ->limit($perPage)
            ->get();
        $totalPages = ceil($totalCount / $perPage);
        $data['total'] = $totalCount;
        $data['records'] = $tasks->mapWithKeys(fn($task) => [$task->id => json_decode($task->data, true)])->all();
       
        $data['filter_records'] = [];
        foreach ($tasks as $key => $task) {
            $data['filter_records'][] = $task->id;
        }
        $data['columns'] = array_unique(array_merge(...array_map('array_keys', $data['records'])));
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

    public function notifications(){
        try {
            $notifications = Notification::where('user_id', $this->user->id)->where('read',false)->orderBy('id', 'desc')->get()->toArray();
            foreach ($notifications as &$notification) {
                $notification['formatted_date'] = Carbon::parse($notification['created_at'])->format('g:i a d-M-Y');
            }
            return response()->json([
                'status' => true,
                'message' => 'Notification list.',
                'data' => $notifications
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead(Request $request)
    {
        try {
            $userId = $this->user->id;
            $notificationId = $request->input('notification_id');

            if ($notificationId) {
                // Mark single notification as read
                $notification = Notification::where('user_id', $userId)
                    ->where('id', $notificationId)
                    ->first();

                if (!$notification) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Notification not found.'
                    ], 404);
                }

                $notification->read = true;
                $notification->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Notification marked as read.',
                    'data' => $notification
                ]);
            } else {
                // Mark all notifications as read
                $updatedCount = Notification::where('user_id', $userId)
                    ->where('read', false)
                    ->update(['read' => true]);

                return response()->json([
                    'status' => true,
                    'message' => 'All unread notifications marked as read.',
                    'data' => ['updated_count' => $updatedCount]
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}